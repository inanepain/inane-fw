<?php

declare(strict_types=1);

use Inane\View\Helper\{
    HtmlBuilder,
    jQuery};

//Usage example of jQuery and HtmlCode classes
// include 'jQuery.php';
// include 'HtmlCode.php';

include 'vendor/autoload.php';

$jq = new jQuery("document");
$htm = new HtmlBuilder('head');

$documentReady = function () {
	$jq1 = new jQuery("'#testDiv'");
	$jq1->find('p');
	$jq1->css('color', 'blue');
	$clickHandle = function () {
		$jq2 = new jQuery("this");
		return $jq2->css('color', 'red')->output();
	};
	$jq1->on('click', $clickHandle);
	return $jq1->output();
};
$scriptOutput = $jq->ready($documentReady)->output();

echo $htm->headStart()
	->titleStart()->contents('Web Page Title')->titleEnd()
	->scriptStart(['src' => 'https://code.jquery.com/jquery-3.4.1.min.js'])->scriptEnd()
	->headEnd()
	->bodyStart()
	->divStart([
		'class' => 'testDivClass',
		'id' => 'testDiv',
		'styleSelector' => '.testDivClass',
		'style' => [
			'width' => '500px',
			'padding' => '20px',
			'border' => '1px solid #000',
			'margin' => '50px auto'
		]
	])
	->pStart()
	->contents('Some paragraph content here')
	->pEnd()
	->divEnd()
	->scriptStart()->contents($scriptOutput)->scriptEnd()
	->bodyEnd();
