<?php

namespace App\Services\Reports;

use RuntimeException;
use ZipArchive;

class TopDiagnosesExport
{
    public const HEADERS = ['Rank', 'Diagnosis', 'Unique patients', 'Total histories', 'Patient ID', 'Patient name', 'Gender', 'Patient histories', 'Referred hospitals'];

    public function rows(array $report): array
    {
        $rows = [];
        foreach ($report as $index => $diagnosis) {
            foreach ($diagnosis['patients'] as $patient) {
                $rows[] = [
                    $index + 1, $diagnosis['diagnosis_name'], $diagnosis['patient_count'], $diagnosis['history_count'],
                    $patient['patient_id'], $patient['name'], $patient['gender'] ?? '', $patient['history_count'],
                    implode('; ', array_column($patient['referred_hospitals'], 'hospital_name')),
                ];
            }
        }

        return $rows;
    }

    public function csvCell($value): string
    {
        $value = (string) $value;

        // Keep spreadsheet applications from interpreting patient-entered text as formulas.
        return preg_match('/^[\s\x{FEFF}]*[=+@-]|^[\t\r\n]/u', $value) ? "'".$value : $value;
    }

    public function word(array $rows, string $period): string
    {
        $xml = fn ($value) => htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $paragraph = fn ($text) => '<w:p><w:r><w:t xml:space="preserve">'.$xml($text).'</w:t></w:r></w:p>';
        $body = $paragraph('Top 10 Diagnoses').$paragraph($period)
            .$paragraph('Ranked by unique patients. Diagnosis totals repeat on each patient row; do not sum them. Hospitals are referrals for the same diagnosis within the selected period.')
            .'<w:tbl><w:tblPr><w:tblBorders><w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/><w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/><w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/></w:tblBorders></w:tblPr>';
        foreach (array_merge([self::HEADERS], $rows) as $index => $row) {
            $body .= '<w:tr>'.($index === 0 ? '<w:trPr><w:tblHeader/></w:trPr>' : '');
            foreach ($row as $cell) {
                $body .= '<w:tc>'.$paragraph($cell).'</w:tc>';
            }
            $body .= '</w:tr>';
        }
        $body .= '</w:tbl><w:sectPr><w:pgSz w:w="16838" w:h="11906" w:orient="landscape"/><w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720"/></w:sectPr>';
        $path = tempnam(sys_get_temp_dir(), 'diagnoses-');
        if ($path === false) {
            throw new RuntimeException('Unable to create Word export.');
        }
        try {
            $zip = new ZipArchive;
            if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Unable to open Word export.');
            }
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
            $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$body.'</w:body></w:document>');
            $zip->close();

            return file_get_contents($path);
        } finally {
            unlink($path);
        }
    }
}
