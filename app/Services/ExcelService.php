<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelService
{
    /**
     * Export collection or array of rows to streamed XLSX download with styling.
     */
    public function export(string $filename, array $headers, array $rows, string $sheetTitle = 'Data'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($sheetTitle, 0, 31));

        // 1. Write Headers
        $colIndex = 1;
        foreach ($headers as $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$colLetter}1", $header);
            $colIndex++;
        }
        $lastColLetter = Coordinate::stringFromColumnIndex(count($headers));

        // Style Header
        $headerRange = "A1:{$lastColLetter}1";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'], // Brand Primary Navy Blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // 2. Write Data Rows
        $rowIndex = 2;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach ($row as $val) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                // Treat numeric strings starting with 0 or long numbers as explicit text so Excel doesn't truncate or use scientific notation
                if (is_string($val) && (str_starts_with($val, '0') || (strlen($val) >= 10 && ctype_digit($val)))) {
                    $sheet->setCellValueExplicit("{$colLetter}{$rowIndex}", $val, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue("{$colLetter}{$rowIndex}", $val);
                }
                $colIndex++;
            }

            // Alternating zebra row fill
            if ($rowIndex % 2 === 0) {
                $rowRange = "A{$rowIndex}:{$lastColLetter}{$rowIndex}";
                $sheet->getStyle($rowRange)->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8FAFC'],
                ]);
            }
            $sheet->getRowDimension($rowIndex)->setRowHeight(20);
            $rowIndex++;
        }

        $lastRowIndex = max(1, $rowIndex - 1);
        $fullRange = "A1:{$lastColLetter}{$lastRowIndex}";

        // Borders
        $sheet->getStyle($fullRange)->getBorders()->getAllBorders()->applyFromArray([
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'CBD5E1'],
        ]);

        // Auto-fit column widths
        for ($i = 1; $i <= count($headers); $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Return StreamedResponse
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Download template XLSX with instructions and sample rows.
     */
    public function downloadTemplate(string $filename, array $headers, array $sampleRows, string $sheetTitle = 'Template'): StreamedResponse
    {
        return $this->export($filename, $headers, $sampleRows, $sheetTitle);
    }

    /**
     * Read rows from uploaded XLSX file.
     * Returns an array of associative arrays keyed by the lowercase trimmed header.
     */
    public function readRows(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, false);

        if (empty($rawRows)) {
            return [];
        }

        // Row 0 is header
        $headerRow = array_shift($rawRows);
        $headerMap = [];
        foreach ($headerRow as $idx => $colName) {
            if ($colName !== null && trim((string)$colName) !== '') {
                $cleanKey = strtolower(trim((string)$colName));
                $headerMap[$idx] = $cleanKey;
            }
        }

        $records = [];
        foreach ($rawRows as $row) {
            // Check if entire row is empty
            $nonEmpty = array_filter($row, fn($val) => $val !== null && trim((string)$val) !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            $record = [];
            foreach ($headerMap as $idx => $key) {
                $record[$key] = isset($row[$idx]) ? trim((string)$row[$idx]) : '';
            }
            $records[] = $record;
        }

        return $records;
    }
}
