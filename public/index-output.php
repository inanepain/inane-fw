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

final class Thing {
    /**
     * Handles the output data and processes it appropriately.
     *
     * @param mixed $outputHandler The handler responsible for managing and processing the output.
     *
     * @return void
     *
     * @throws \RuntimeException If the output handler encounters a processing error.
     */
    private \Inane\Stdlib\Output\OutputInterface $outputHandler;

    /**
     * Initializes the class with the given input data.
     *
     * @param mixed $inputData The input data to be used for initialization.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the provided input data is invalid.
     */
    public function __construct(protected mixed $inputData) {}

    /**
     * Sets the output handler to be used.
     *
     * @param \Inane\Stdlib\Output\OutputInterface $outputHandler The output handler to set.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the provided output handler is invalid.
     */
    public function setOutputHandler(\Inane\Stdlib\Output\OutputInterface $outputHandler): void {
        $this->outputHandler = $outputHandler;
    }

    /**
     * Processes the input data and returns the output.
     *
     * @return mixed The result of processing the input data.
     *
     * @throws \Exception If an error occurs during output processing.
     */
    public function output(): mixed {
        return $this->outputHandler->output($this->inputData);
    }
}


echo "\nREUSABLE:\n";
$thing = new Thing($opt);
$thing->setOutputHandler(new \Inane\Stdlib\Output\XmlStringOutput());
echo "\nXMLString:\n";
var_dump($thing->output());
$thing->setOutputHandler(new \Inane\Stdlib\Output\XmlOutput());
echo "\nXML:\n";
var_dump($thing->output());
$thing->setOutputHandler(new \Inane\Stdlib\Output\ArrayOutput());
echo "\nARRAY:\n";
var_dump($thing->output());
$thing->setOutputHandler($jo);
echo "\nJSON:\n";
var_dump($thing->output());
