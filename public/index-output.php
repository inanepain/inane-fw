<?php

declare(strict_types = 1);

$data = ['state' => 'initial', 'name' => 'philip', 'code' => 'php'];
$object = (object)$data;

echo "DATA:\n";
var_dump($data);

echo "OBJECT:\n";
var_dump($object);

echo "\nJSON:\n";
$data['state'] = 'json';
$jo = new \Inane\Stdlib\Output\JsonStringOutput($data);
var_dump($jo->output());

echo "\nSERIALISED:\n";
$data['state'] = 'serialise';
$so = new \Inane\Stdlib\Output\SerializedOutput($data);
var_dump($so->output());

$opt = new \Inane\Stdlib\Options([
    'one' => $data,
    'two' => [
        'two' => 2,
        'three' => [
            'four' => 'five',
        ],
    ],
]);

echo "\nARRAY:\n";
$data['state'] = 'array';
//$ao = new \Inane\Stdlib\Output\ArrayOutput($jo->output());
$ao = new \Inane\Stdlib\Output\ArrayOutput($so->output());
//$ao = new \Inane\Stdlib\Output\ArrayOutput($opt);
var_dump($ao->output());

echo "\nXML:\n";
$data['state'] = 'xml';
$xo = new \Inane\Stdlib\Output\XmlOutput($data);
var_dump($xo->output());

echo "\nXMLString:\n";
$data['state'] = 'xml-string';
$xso = new \Inane\Stdlib\Output\XmlStringOutput($data);
var_dump($xso->output());
