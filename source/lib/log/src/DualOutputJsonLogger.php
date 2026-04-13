<?php

/**
 * Inane: Log
 *
 * It has levels and keeps a log, but you knew that already.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * @author   Philip Michael Raab<philip@cathedral.co.za>
 * @package  inanepain\log
 * @category log
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

use function clearstatcache;
use function fclose;
use function file_exists;
use function filesize;
use function flock;
use function fopen;
use function fwrite;
use function get_class;
use function gmdate;
use function is_dir;
use function is_object;
use function is_scalar;
use function json_encode;
use function method_exists;
use function mkdir;
use function rename;
use function strtoupper;
use function unlink;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const LOCK_EX;
use const LOCK_UN;
use const PHP_EOL;

class DualOutputJsonLogger implements LoggerInterface {
    use LoggerTrait;

    public function __construct(
        string $dir = __DIR__ . '/logs',
        string $baseName = 'app',
        int    $maxSizeBytes = 5_000_000,
        int    $maxFiles = 5,
        string $dateFormat = 'Y-m-d',
        bool   $logToStdout = true,
    ) {
        $this->dir = $dir;
        $this->baseName = $baseName;
        $this->maxSizeBytes = $maxSizeBytes;
        $this->maxFiles = $maxFiles;
        $this->dateFormat = $dateFormat;
        $this->logToStdout = $logToStdout;

        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
    }

    private string $dir;
    private string $baseName;
    private int $maxSizeBytes;
    private int $maxFiles;
    private string $dateFormat;
    private bool $logToStdout;

    public function log($level, $message, array $context = []): void {
        $file = $this->getLogFile();

        $this->rotateIfNeeded($file);

        $entry = $this->buildEntry($level, $message, $context);

        $json = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            $json = json_encode([
                'ts'    => gmdate('c'),
                'level' => 'ERROR',
                'msg'   => 'Failed to encode log entry',
            ]);
        }

        $line = $json . PHP_EOL;

        // 1. Write to file
        $this->writeToFile($file, $line);

        // 2. Write to stdout (Docker)
        if ($this->logToStdout) {
            $this->writeToStdout($line);
        }
    }

    private function buildEntry($level, $message, array $context): array {
        $entry = [
            'ts'    => gmdate('c'),
            'level' => strtoupper((string)$level),
            'msg'   => $this->interpolate((string)$message, $context),
        ];

        foreach($context as $k => $v) {
            if ($k === 'exception' && $v instanceof \Throwable) {
                $entry['exception'] = [
                    'class' => get_class($v),
                    'msg'   => $v->getMessage(),
                    'file'  => $v->getFile(),
                    'line'  => $v->getLine(),
                ];
                continue;
            }

            $entry[$k] = $v;
        }

        return $entry;
    }

    private function writeToFile(string $file, string $line): void {
        $fp = fopen($file, 'ab');
        if ($fp) {
            flock($fp, LOCK_EX);
            fwrite($fp, $line);
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    private function writeToStdout(string $line): void {
        // STDOUT for Docker logs
        fwrite(STDOUT, $line);
    }

    private function getLogFile(): string {
        $date = gmdate($this->dateFormat);

        return "{$this->dir}/{$this->baseName}-{$date}.log";
    }

    private function rotateIfNeeded(string $file): void {
        if (!file_exists($file)) {
            return;
        }

        clearstatcache(true, $file);

        if (filesize($file) < $this->maxSizeBytes) {
            return;
        }

        for($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $old = $file . '.' . $i;
            $new = $file . '.' . ($i + 1);

            if (file_exists($old)) {
                if ($i === $this->maxFiles - 1) {
                    unlink($old);
                } else {
                    rename($old, $new);
                }
            }
        }

        rename($file, $file . '.1');
    }

    private function interpolate(string $message, array $context): string {
        $replace = [];

        foreach($context as $key => $val) {
            if (is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = (string)$val;
            }
        }

        return strtr($message, $replace);
    }
}
