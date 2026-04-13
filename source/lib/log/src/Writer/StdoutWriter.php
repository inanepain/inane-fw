<?php

declare(strict_types=1);

namespace Inane\Log\Writer;

use Inane\Log\AbstractWriter;

use Inane\Stdlib\Json;
use function fwrite;
use function gmdate;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const PHP_EOL;
use const STDOUT;

/**
 * Stdout Writer
 *
 * @package Inane\Log\Writer
 */
class StdoutWriter extends AbstractWriter {
    /**
     * Write a log entry
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function write(mixed $level, string $message, array $context = []): void {
        $entry = $this->buildEntry($level, $message, $context);

        $json = Json::encode($entry, ['numeric' => true, 'flags' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE]);

        if ($json === false) {
            $json = Json::encode([
                'ts'    => gmdate('c'),
                'level' => 'ERROR',
                'msg'   => 'Failed to encode log entry',
            ], ['numeric' => true, 'flags' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE]);
        }

        fwrite(STDOUT, $json . PHP_EOL);
    }
}
