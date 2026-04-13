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
use Psr\Log\LogLevel;

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

class JsonFileLogger implements LoggerInterface {
    public function __construct(
        string $dir = __DIR__ . '/logs',
        string $baseName = 'app',
        int    $maxSizeBytes = 5_000_000,
        int    $maxFiles = 5,
        string $dateFormat = 'Y-m-d',
    ) {
        $this->dir = $dir;
        $this->baseName = $baseName;
        $this->maxSizeBytes = $maxSizeBytes;
        $this->maxFiles = $maxFiles;
        $this->dateFormat = $dateFormat;

        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
    }
    private string $dir;
    private string $baseName;
    private int $maxSizeBytes;
    private int $maxFiles;
    private string $dateFormat;

    public function emergency($message, array $context = []): void {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = []): void {
        $file = $this->getLogFile();

        $this->rotateIfNeeded($file);

        $entry = [
            'ts'    => gmdate('c'),
            'level' => strtoupper((string)$level),
            'msg'   => $this->interpolate((string)$message, $context),
        ];

        // Attach remaining context as structured fields
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

        $json = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            $json = json_encode([
                'ts'    => gmdate('c'),
                'level' => 'ERROR',
                'msg'   => 'Failed to encode log entry',
            ]);
        }

        $json .= PHP_EOL;

        $fp = fopen($file, 'ab');
        if ($fp) {
            flock($fp, LOCK_EX);
            fwrite($fp, $json);
            flock($fp, LOCK_UN);
            fclose($fp);
        }
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

        // shift old logs
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
        // PSR-3-style placeholder replacement: "User {id}"
        $replace = [];
        foreach($context as $key => $val) {
            if (is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = (string)$val;
            }
        }

        return strtr($message, $replace);
    }
}
