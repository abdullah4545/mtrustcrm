<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>
        Customer Database and Daily Visit Report
    </title>

    <style>
        @page {
            size: A4 landscape;
            margin: 12px 14px 18px 14px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.2px;
            color: #000;
        }

        .report-heading {
            text-align: center;
            margin-bottom: 8px;
        }

        .company-name {
            color: #0000cc;
            font-family: serif;
            font-weight: bold;
            font-size: 24px;
            margin: 0 0 2px;
        }

        .report-title {
            color: #0000cc;
            font-family: serif;
            font-weight: bold;
            font-size: 17px;
            margin: 0 0 5px;
        }

        .employee-line {
            font-family: serif;
            font-size: 14px;
            font-weight: bold;
            margin: 1px 0;
        }

        .date-line {
            font-family: serif;
            font-size: 15px;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-row-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            border: 0.7px solid #000;
            padding: 4px 3px;
            vertical-align: middle;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        th {
            font-family: serif;
            font-weight: bold;
            text-align: left;
            background: #fff;
            font-size: 6.6px;
        }

        td {
            min-height: 30px;
        }

        .number-cell {
            text-align: right;
            white-space: nowrap;
        }

        .serial-cell {
            text-align: center;
            width: 23px;
        }

        .total-row td {
            font-family: serif;
            font-weight: bold;
            font-size: 8px;
        }

        .empty-row {
            text-align: center;
            padding: 30px;
            font-size: 10px;
        }

        /*
         * আপনার default 15-column PDF-এর কাছাকাছি widths.
         */
        .column-serial {
            width: 2.2%;
        }

        .column-date {
            width: 6.5%;
        }

        .column-organization_name {
            width: 9.5%;
        }

        .column-department {
            width: 6.8%;
        }

        .column-contact_person {
            width: 9.8%;
        }

        .column-details {
            width: 11%;
        }

        .column-from_location {
            width: 9.5%;
        }

        .column-to_location {
            width: 6.8%;
        }

        .column-distance {
            width: 3.5%;
        }

        .column-vehicle {
            width: 7.8%;
        }

        .column-work_details {
            width: 9.8%;
        }

        .column-ta {
            width: 5.8%;
        }

        .column-da {
            width: 5.8%;
        }

        .column-total {
            width: 4.2%;
        }

        .column-remarks {
            width: 7%;
        }

        .status-pending {
            color: #b26a00;
        }

        .status-approved {
            color: #137333;
        }

        .status-rejected {
            color: #b91c1c;
        }
    </style>
</head>

<body>

@php
    $employeeName = $employee->name ?? 'All Users';

    $employeeArea =
        $employee->area ??
        $employee->address ??
        '';

    $employeePhone =
        $employee->phone ??
        $employee->mobile ??
        '';

    $nameDisplay = $employeeName;

    if ($employeeArea) {
        $nameDisplay .= ', ' . $employeeArea;
    }

    $dateDisplay =
        ($fromDate ?? 'Beginning') .
        ' To ' .
        ($toDate ?? 'Today');

    $numericColumns = [
        'ta',
        'da',
        'total',
    ];
@endphp

<div class="report-heading">

    <div class="company-name">
        {{$business->business_name}}
    </div>

    <div class="report-title">
        Customer Database and Daily Visit Report
    </div>

    <div class="employee-line">
        Name :{{ $nameDisplay }}
    </div>

    <div class="employee-line">
        Phone :{{ $employeePhone }}
    </div>

    <div class="date-line">
        Date : {{ $dateDisplay }}
    </div>

</div>

<table>
    <thead>
        <tr>
            @foreach($columns as $column)
                <th class="column-{{ $column }}">
                    {{ $columnLabels[$column] ?? $column }}
                </th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @forelse($activities as $index => $activity)
            <tr>
                @foreach($columns as $column)

                    @php
                        $value = match($column) {
                            'serial' => $index + 1,

                            'date' => optional($activity->date)
                                ->format('D, M d, Y'),

                            'distance' =>
                                (float) $activity->distance ==
                                floor((float) $activity->distance)
                                    ? (string) (int) $activity->distance
                                    : number_format(
                                        (float) $activity->distance,
                                        2
                                    ),

                            'ta',
                            'da',
                            'total' => number_format(
                                (float) $activity->{$column},
                                2
                            ),

                            'created_by' =>
                                optional($activity->creator)->name ??
                                'N/A',

                            'status' => ucfirst(
                                (string) $activity->status
                            ),

                            default =>
                                $activity->{$column} ?? '',
                        };

                        $cellClass = [];

                        if ($column === 'serial') {
                            $cellClass[] = 'serial-cell';
                        }

                        if (
                            in_array(
                                $column,
                                $numericColumns,
                                true
                            )
                        ) {
                            $cellClass[] = 'number-cell';
                        }

                        if ($column === 'status') {
                            $cellClass[] =
                                'status-' .
                                strtolower($activity->status);
                        }
                    @endphp

                    <td class="{{ implode(' ', $cellClass) }}">
                        {{ $value }}
                    </td>

                @endforeach
            </tr>
        @empty
            <tr>
                <td
                    colspan="{{ max(count($columns), 1) }}"
                    class="empty-row"
                >
                    No activity data found.
                </td>
            </tr>
        @endforelse
    </tbody>

    <tfoot>
        <tr class="total-row">

            @foreach($columns as $index => $column)
                <td
                    class="{{ in_array($column, $numericColumns, true) ? 'number-cell' : '' }}"
                >
                    @if($index === 0)
                        Total Amount
                    @elseif($column === 'ta')
                        {{ number_format((float) $totalTa, 2) }}Tk
                    @elseif($column === 'da')
                        {{ number_format((float) $totalDa, 2) }}Tk
                    @elseif($column === 'total')
                        {{ number_format((float) $grandTotal, 2) }}Tk
                    @endif
                </td>
            @endforeach

        </tr>
    </tfoot>
</table>

</body>
</html>