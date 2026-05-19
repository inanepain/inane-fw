<?php

declare(strict_types = 1);


$activities = require_once 'include/ActivityListSexual.php';

$ap = new ActivityPicker($activities);

$ap->setNumberOfPicks(15, true);

while (!$ap->end) {
    echo $ap->pick() . PHP_EOL;
}
