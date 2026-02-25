<?php

namespace App\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;

/**
 * Size-based rotating file handler with optional gzip compression.
 *
 * - Rotates when current log file exceeds max size
 * - Archives old logs with timestamp suffix
 * - Optionally gzips archives to save disk
 * - Keeps only the most recent maxFiles archives
 *
 * Note: Laravel already supports daily rotation, but this handler is aimed at
 * limiting storage by file size (useful in production environments).
 */
class SizeRotatingFileHandler extends StreamHandler
{
    protected int $maxFiles;
    protected int $maxFileSizeBytes;
    protected bool $compress;

    public function __construct(
        string $stream,
        int|string|Level $level = Logger::DEBUG,
        bool $bubble = true,
        ?int $filePermission = null,
        bool $useLocking = false,
        int $maxFiles = 14,
        int $maxFileSizeBytes = 52428800, // 50 MB
        bool $compress = true
    ) {
        parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);
        $this->maxFiles = max(1, $maxFiles);
        $this->maxFileSizeBytes = max(1024 * 1024, $maxFileSizeBytes); // min 1 MB
        $this->compress = $compress;
    }

    protected function write(LogRecord $record): void
    {
        $this->rotateIfNeeded();
        parent::write($record);
    }

    protected function rotateIfNeeded(): void
    {
        $path = $this->url;
        if (!$path) {
            return;
        }

        if (!is_string($path)) {
            return;
        }

        if (!file_exists($path)) {
            return;
        }

        clearstatcache(true, $path);
        $size = @filesize($path);
        if ($size === false || $size < $this->maxFileSizeBytes) {
            return;
        }

        // Close current stream before rotating
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;

        $timestamp = date('Ymd-His');
        $dir = dirname($path);
        $base = basename($path);

        $ext = pathinfo($base, PATHINFO_EXTENSION);
        $name = $ext ? substr($base, 0, -strlen($ext) - 1) : $base;

        $rotated = $dir . DIRECTORY_SEPARATOR . $name . '-' . $timestamp . ($ext ? '.' . $ext : '');

        // Best effort rotate
        @rename($path, $rotated);

        if ($this->compress && file_exists($rotated)) {
            $gzPath = $rotated . '.gz';
            $this->gzipFile($rotated, $gzPath);
            @unlink($rotated);
        }

        $this->cleanupArchives($dir, $name, $ext);
    }

    protected function gzipFile(string $source, string $destination): void
    {
        $in = @fopen($source, 'rb');
        if (!$in) {
            return;
        }
        $out = @gzopen($destination, 'wb9');
        if (!$out) {
            fclose($in);
            return;
        }

        while (!feof($in)) {
            $chunk = fread($in, 1024 * 1024); // 1MB
            if ($chunk === false) {
                break;
            }
            gzwrite($out, $chunk);
        }

        fclose($in);
        gzclose($out);
    }

    protected function cleanupArchives(string $dir, string $name, string $ext): void
    {
        $pattern = $dir . DIRECTORY_SEPARATOR . $name . '-*' . ($ext ? '.' . $ext : '') . '*';
        $files = glob($pattern) ?: [];

        // Keep only archives (exclude current laravel.log if pattern ever matches)
        $files = array_values(array_filter($files, function ($file) use ($dir, $name, $ext) {
            $current = $dir . DIRECTORY_SEPARATOR . $name . ($ext ? '.' . $ext : '');
            return realpath($file) !== realpath($current);
        }));

        usort($files, function ($a, $b) {
            return (@filemtime($b) ?: 0) <=> (@filemtime($a) ?: 0);
        });

        $toDelete = array_slice($files, $this->maxFiles);
        foreach ($toDelete as $file) {
            @unlink($file);
        }
    }
}


