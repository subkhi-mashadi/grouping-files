<?php

namespace App\Services;

use InvalidArgumentException;
use setasign\Fpdi\Fpdi;

class PdfMergeService
{
    /**
     * Parse "1-2, 5-6, 9" into a list of page numbers (in given order).
     * Empty string = all pages.
     *
     * @return int[]
     */
    public function parseRanges(?string $input, int $pageCount): array
    {
        $input = trim((string) $input);

        if ($input === '') {
            return range(1, $pageCount);
        }

        $pages = [];

        foreach (explode(',', $input) as $part) {
            $part = trim($part);

            if (! preg_match('/^(\d+)(?:\s*-\s*(\d+))?$/', $part, $m)) {
                throw new InvalidArgumentException("Format halaman tidak valid: \"{$part}\"");
            }

            $start = (int) $m[1];
            $end = isset($m[2]) ? (int) $m[2] : $start;

            if ($start < 1 || $end < $start) {
                throw new InvalidArgumentException("Rentang tidak valid: \"{$part}\"");
            }

            if ($end > $pageCount) {
                throw new InvalidArgumentException("Halaman {$end} melebihi jumlah halaman file ({$pageCount}).");
            }

            foreach (range($start, $end) as $p) {
                $pages[] = $p;
            }
        }

        return $pages;
    }

    /**
     * @param  array<int, array{path: string, name: string, ranges: ?string}>  $files
     * @return string  PDF binary
     */
    public function merge(array $files): string
    {
        $pdf = new Fpdi();

        foreach ($files as $file) {
            try {
                $count = $pdf->setSourceFile($file['path']);
                $pages = $this->parseRanges($file['ranges'] ?? '', $count);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("{$file['name']}: ".$e->getMessage());
            } catch (\Throwable $e) {
                throw new InvalidArgumentException("{$file['name']}: tidak bisa dibaca (".$e->getMessage().')');
            }

            foreach ($pages as $page) {
                $tpl = $pdf->importPage($page);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }
        }

        return $pdf->Output('S');
    }
}
