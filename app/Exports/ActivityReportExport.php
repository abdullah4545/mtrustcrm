<?php

namespace App\Exports;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivityReportExport implements
    FromArray,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    private Collection $activities;

    private ?object $employee;

    public function __construct(
        private readonly array $filters,
        private readonly array $columns,
        private readonly array $columnLabels
    ) {
        $this->activities = $this->query()
            ->with('creator')
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $this->employee = !empty($filters['created_by'])
            ? DB::table('users')
                ->where('id', $filters['created_by'])
                ->first()
            : null;
    }

    public function array(): array
    {
        $columnCount = max(count($this->columns), 1);

        $rows = [
            ['DANPITE TECH'],
            ['Customer Database and Daily Visit Report'],
            [
                'Name :' . (
                    $this->employee->name ??
                    'All Users'
                )
            ],
            [
                'Phone :' . (
                    $this->employee->phone ??
                    ''
                )
            ],
            [
                'Date :' .
                $this->formattedDateRange()
            ],
            [],
            array_map(
                fn ($column) =>
                    $this->columnLabels[$column] ?? $column,
                $this->columns
            ),
        ];

        foreach (
            $this->activities->values() as $index => $activity
        ) {
            $dataRow = [];

            foreach ($this->columns as $column) {
                $dataRow[] = $this->columnValue(
                    $activity,
                    $column,
                    $index + 1
                );
            }

            $rows[] = $dataRow;
        }

        $totalRow = array_fill(0, $columnCount, '');

        if (in_array('serial', $this->columns, true)) {
            $totalRow[
                array_search('serial', $this->columns, true)
            ] = 'Total Amount';
        } else {
            $totalRow[0] = 'Total Amount';
        }

        $this->putTotal(
            $totalRow,
            'ta',
            $this->activities->sum('ta')
        );

        $this->putTotal(
            $totalRow,
            'da',
            $this->activities->sum('da')
        );

        $this->putTotal(
            $totalRow,
            'total',
            $this->activities->sum('total')
        );

        $rows[] = $totalRow;

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 18,
                ],
            ],

            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
            ],

            7 => [
                'font' => [
                    'bold' => true,
                ],

                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'D9EAF7',
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                foreach ([1, 2, 3, 4, 5] as $row) {
                    $sheet->mergeCells(
                        "A{$row}:{$lastColumn}{$row}"
                    );
                }

                $sheet->getStyle(
                    "A1:{$lastColumn}5"
                )->getAlignment()->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );

                $sheet->getStyle(
                    "A7:{$lastColumn}{$lastRow}"
                )->getBorders()->getAllBorders()->setBorderStyle(
                    Border::BORDER_THIN
                );

                $sheet->getStyle(
                    "A7:{$lastColumn}{$lastRow}"
                )->getAlignment()->setVertical(
                    Alignment::VERTICAL_CENTER
                );

                $sheet->getStyle(
                    "A7:{$lastColumn}{$lastRow}"
                )->getAlignment()->setWrapText(true);

                $sheet->freezePane('A8');

                $sheet->setAutoFilter(
                    "A7:{$lastColumn}7"
                );

                $sheet->getPageSetup()
                    ->setOrientation(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                    );

                $sheet->getPageSetup()
                    ->setFitToWidth(1);

                $sheet->getPageSetup()
                    ->setFitToHeight(0);

                $sheet->getStyle(
                    "A{$lastRow}:{$lastColumn}{$lastRow}"
                )->getFont()->setBold(true);
            },
        ];
    }

    private function query(): Builder
    {
        return Activity::query()
            ->when(
                !empty($this->filters['from_date']),
                fn (Builder $query) =>
                    $query->whereDate(
                        'date',
                        '>=',
                        $this->filters['from_date']
                    )
            )
            ->when(
                !empty($this->filters['to_date']),
                fn (Builder $query) =>
                    $query->whereDate(
                        'date',
                        '<=',
                        $this->filters['to_date']
                    )
            )
            ->when(
                !empty($this->filters['created_by']),
                fn (Builder $query) =>
                    $query->where(
                        'created_by',
                        $this->filters['created_by']
                    )
            )
            ->when(
                !empty($this->filters['status']),
                fn (Builder $query) =>
                    $query->where(
                        'status',
                        $this->filters['status']
                    )
            )
            ->when(
                !empty($this->filters['organization_id']),
                fn (Builder $query) =>
                    $query->where(
                        'organization_id',
                        $this->filters['organization_id']
                    )
            )
            ->when(
                !empty($this->filters['branch_id']),
                fn (Builder $query) =>
                    $query->where(
                        'branch_id',
                        $this->filters['branch_id']
                    )
            );
    }

    private function columnValue(
        Activity $activity,
        string $column,
        int $serial
    ): mixed {
        return match ($column) {
            'serial' => $serial,

            'date' => optional($activity->date)
                ->format('D, M d, Y'),

            'ta',
            'da',
            'total' => (float) $activity->{$column},

            'distance' => (float) $activity->distance,

            'created_by' => optional(
                $activity->creator
            )->name ?? 'N/A',

            'status' => ucfirst(
                (string) $activity->status
            ),

            default => $activity->{$column} ?? '',
        };
    }

    private function formattedDateRange(): string
    {
        $from = !empty($this->filters['from_date'])
            ? Carbon::parse(
                $this->filters['from_date']
            )->format('d-m-Y')
            : 'Beginning';

        $to = !empty($this->filters['to_date'])
            ? Carbon::parse(
                $this->filters['to_date']
            )->format('d-m-Y')
            : 'Today';

        return " {$from} To {$to}";
    }

    private function putTotal(
        array &$row,
        string $column,
        $amount
    ): void {
        $index = array_search(
            $column,
            $this->columns,
            true
        );

        if ($index !== false) {
            $row[$index] = (float) $amount;
        }
    }
}