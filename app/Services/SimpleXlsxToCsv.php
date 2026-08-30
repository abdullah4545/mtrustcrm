<?php

namespace App\Services;

use PharData;
use RuntimeException;
use Throwable;

class SimpleXlsxToCsv
{
    public function convert(string $xlsxPath, string $csvPath): array
    {
        try {
            $phar = new PharData($xlsxPath);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to open XLSX file.');
        }

        $sheetEntry = 'xl/worksheets/sheet1.xml';
        if (!isset($phar[$sheetEntry])) {
            throw new RuntimeException('No worksheet found in XLSX file.');
        }

        $sharedStrings = [];
        if (isset($phar['xl/sharedStrings.xml'])) {
            $sharedStrings = $this->parseSharedStrings($phar['xl/sharedStrings.xml']->getContent());
        }

        $source = 'phar://'.$xlsxPath.'/'.$sheetEntry;
        $in = @fopen($source, 'rb');
        $out = @fopen($csvPath, 'wb');

        if (!$in || !$out) {
            if (is_resource($in)) fclose($in);
            if (is_resource($out)) fclose($out);
            throw new RuntimeException('Unable to read XLSX worksheet.');
        }

        $headers = [];
        $rowCount = 0;
        $buffer = '';

        try {
            while (!feof($in)) {
                $chunk = fread($in, 1024 * 1024);
                if ($chunk === false) {
                    throw new RuntimeException('Error while reading XLSX worksheet.');
                }
                $buffer .= $chunk;

                while (true) {
                    $rowStart = strpos($buffer, '<row');
                    if ($rowStart === false) {
                        if (strlen($buffer) > 16) {
                            $buffer = substr($buffer, -16);
                        }
                        break;
                    }

                    $rowEnd = strpos($buffer, '</row>', $rowStart);
                    if ($rowEnd === false) {
                        if ($rowStart > 0) {
                            $buffer = substr($buffer, $rowStart);
                        }
                        break;
                    }

                    $rowEnd += 6;
                    $rowXml = substr($buffer, $rowStart, $rowEnd - $rowStart);
                    $buffer = substr($buffer, $rowEnd);

                    $values = $this->parseRow($rowXml, $sharedStrings);
                    if (!$values || $this->isBlankRow($values)) {
                        continue;
                    }

                    if ($rowCount === 0) {
                        $headers = array_map(fn ($v) => trim((string) $v), $values);
                    }

                    fputcsv($out, $values);
                    $rowCount++;
                }
            }
        } finally {
            fclose($in);
            fclose($out);
        }

        if ($rowCount < 2) {
            @unlink($csvPath);
            throw new RuntimeException('The spreadsheet does not contain importable data rows.');
        }

        return [
            'headers' => $headers,
            'total_rows' => $rowCount - 1,
        ];
    }

    private function parseSharedStrings(string $xml): array
    {
        $strings = [];
        if (!preg_match_all('#<si\b[^>]*>(.*?)</si>#s', $xml, $items)) {
            return $strings;
        }

        foreach ($items[1] as $item) {
            $strings[] = $this->extractAllText($item);
        }

        return $strings;
    }

    private function parseRow(string $rowXml, array $sharedStrings): array
    {
        $row = [];
        if (!preg_match_all('#<c\b([^>]*)>(.*?)</c>#s', $rowXml, $cells, PREG_SET_ORDER)) {
            return [];
        }

        foreach ($cells as $cell) {
            $attrs = $cell[1];
            $body = $cell[2];
            $ref = $this->attribute($attrs, 'r');
            $type = $this->attribute($attrs, 't');
            $index = $this->columnIndex($ref);
            $row[$index] = $this->cellValue($body, $type, $sharedStrings);
        }

        if (!$row) {
            return [];
        }

        $max = max(array_keys($row));
        $values = [];
        for ($i = 0; $i <= $max; $i++) {
            $values[] = $row[$i] ?? '';
        }

        return $values;
    }

    private function cellValue(string $body, string $type, array $sharedStrings): string
    {
        if ($type === 'inlineStr') {
            return $this->extractAllText($body);
        }

        if (preg_match('#<v[^>]*>(.*?)</v>#s', $body, $m)) {
            $raw = $this->decode($m[1]);
            if ($type === 's') {
                return (string) ($sharedStrings[(int) $raw] ?? '');
            }
            return trim($raw);
        }

        return '';
    }

    private function extractAllText(string $xml): string
    {
        preg_match_all('#<t(?:\s[^>]*)?>(.*?)</t>#s', $xml, $matches);
        if (empty($matches[1])) {
            return '';
        }
        return trim(implode('', array_map(fn ($v) => $this->decode($v), $matches[1])));
    }

    private function decode(string $value): string
    {
        return html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function attribute(string $attrs, string $name): string
    {
        if (preg_match('/\b'.preg_quote($name, '/').'="([^"]*)"/', $attrs, $m)) {
            return $m[1];
        }
        if (preg_match("/\\b".preg_quote($name, '/')."='([^']*)'/", $attrs, $m)) {
            return $m[1];
        }
        return '';
    }

    private function columnIndex(string $ref): int
    {
        if (!preg_match('/^([A-Z]+)/i', $ref, $m)) {
            return 0;
        }

        $index = 0;
        foreach (str_split(strtoupper($m[1])) as $char) {
            $index = ($index * 26) + (ord($char) - 64);
        }
        return max(0, $index - 1);
    }

    private function isBlankRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }
        return true;
    }
}
