<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class PdfCompressService
{
    /** level => Ghostscript preset */
    private const LEVELS = [
        'low' => '/printer',    // ~300 dpi, kualitas tinggi
        'medium' => '/ebook',   // ~150 dpi, seimbang
        'high' => '/screen',    // ~72 dpi, ukuran terkecil
    ];

    /**
     * @return array{binary: string, original: int, compressed: int}
     */
    public function compress(string $path, string $level): array
    {
        if (! isset(self::LEVELS[$level])) {
            throw new InvalidArgumentException('Level kompresi tidak dikenal.');
        }

        $gs = $this->findGhostscript();
        $out = tempnam(sys_get_temp_dir(), 'cmp_');

        try {
            $process = new Process([
                $gs,
                '-sDEVICE=pdfwrite',
                '-dCompatibilityLevel=1.4',
                '-dPDFSETTINGS='.self::LEVELS[$level],
                '-dNOPAUSE', '-dQUIET', '-dBATCH', '-dSAFER',
                '-sOutputFile='.$out,
                $path,
            ]);
            $process->setTimeout(120);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($out) || filesize($out) === 0) {
                throw new InvalidArgumentException('File tidak bisa dikompres. Pastikan PDF valid dan tidak diproteksi password.');
            }

            $original = filesize($path);
            $compressed = filesize($out);

            // Kalau hasil malah lebih besar, kembalikan file asli.
            $binary = $compressed < $original ? file_get_contents($out) : file_get_contents($path);

            return [
                'binary' => $binary,
                'original' => $original,
                'compressed' => min($compressed, $original),
            ];
        } finally {
            @unlink($out);
        }
    }

    private function findGhostscript(): string
    {
        $found = config('services.ghostscript.path')
            ?: (new ExecutableFinder())->find('gs', null, ['/opt/homebrew/bin', '/usr/local/bin', '/usr/bin']);

        if (! $found) {
            throw new RuntimeException('Ghostscript belum terpasang di server.');
        }

        return $found;
    }
}
