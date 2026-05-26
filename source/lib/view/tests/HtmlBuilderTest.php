<?php

declare(strict_types = 1);

namespace Test\View\Helper;

use Inane\View\Helper\HtmlBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Stringable;

/**
 * HtmlBuilderTest
 *
 * @version 0.1.0
 */
class HtmlBuilderTest extends TestCase {
	/**
	 * @var array|string[] values to use for assertion tests
	 */
	protected static array $checkValues = [
		'testRender' => '<!DOCTYPE HTML><html lang="en"><head><title>Test Case</title></head><body><style></style></body></html>',
	];

	/**
	 * Calls a protected or private method on an object using reflection
	 *
	 * @param object|string         $objectOrClass
	 * @param string|Stringable     $methodName
	 * @param array                 $arguments
	 *
	 * @return mixed
	 *
	 * @throws \ReflectionException
	 */
	public static function callMethod(object|string $objectOrClass, string|Stringable $methodName, array $arguments): mixed {
		$class = new ReflectionClass($objectOrClass);

        return $class->getMethod($methodName)
            ->invokeArgs($objectOrClass, $arguments);
	}

	/**
	 * Test creating new instance
	 *
	 * @return void
	 */
	public function testCreate(): void {
		$this->assertInstanceOf(HtmlBuilder::class, HtmlBuilder::create());
	}

	/**
	 * Test the render process
	 *
	 * @return void
	 */
	public function testRender(): void {
		$html = HtmlBuilder::create()->headStart()
			->titleStart()->contents('Test Case')->titleEnd()
			->headEnd()
			->bodyStart()
			->bodyEnd()
			->render();

		$this->assertEquals(static::$checkValues[__FUNCTION__], $html);
	}
}
