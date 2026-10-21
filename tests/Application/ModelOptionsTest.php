<?php
declare(strict_types=1);

namespace Knot\Tests\Application;

use Inane\Http\HttpStatus;
use Inane\View\Model\HtmlModel;
use PHPUnit\Framework\TestCase;

/**
 * Covers HTTP model option round-tripping.
 */
final class ModelOptionsTest extends TestCase {
    /**
     * Retains integer status and enum options through export and import.
     *
     * @throws \Throwable
     */
    public function testStatusOptions(): void {
        $model = new HtmlModel(options: ['status' => 404, 'headers' => ['X-Test' => 'value']]);
        self::assertSame(404, $model->status);
        $copy = new HtmlModel(options: $model->getOptions());
        self::assertSame(HttpStatus::NotFound, $copy->httpStatus);
        self::assertSame(404, $copy->getOption('status'));
        self::assertSame(['X-Test' => 'value'], $copy->headers);
    }

    /**
     * Rejects invalid HTTP status codes.
     *
     * @throws \Throwable
     */
    public function testInvalidStatus(): void {
        $this->expectException(\ValueError::class);
        new HtmlModel(options: ['status' => 999]);
    }
}