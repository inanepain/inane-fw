<?php

declare(strict_types = 1);

use Inane\Stdlib\Merge\Merge;
use Inane\Stdlib\Merge\MergeMethod;

$target1 = new \Inane\Stdlib\Options([
    'rangeSize' => 9,
    'step' => 5,
    'debug' => [
        'activityIndex' => false,
    ],
]);

$target2 = new \Inane\Stdlib\Options([
    'rangeSize' => 9,
    'step' => 5,
    'debug' => [
        'activityIndex' => false,
    ],
]);

$target3 = new \Inane\Stdlib\Options([
    'rangeSize' => 9,
    'step' => 5,
    'debug' => [
        'activityIndex' => false,
    ],
]);

$source = new \Inane\Stdlib\Options([
    'rangeSize' => 3,
    'step' => 2,
    'debug' => [
        'activityIndex' => true,
    ],
    'King' => 'Kong',
]);

//$merge = new MergeOptions();

echo "Add\n";
// $merge->mergeMethod = MergeMethod::AddOnly;
// $result = $merge->mergeOptions($target, $source);
$result = Merge::mergeOptionsWithMethod(MergeMethod::AddOnly, $target1, $source);
print_r($result);

echo "Update\n";
// $merge->mergeMethod = MergeMethod::UpdateOnly;
// $result = $merge->mergeOptions($target, $source);
$result = Merge::mergeOptionsWithMethod(MergeMethod::UpdateOnly, $target2, $source);
print_r($result);

echo "AddUpdate\n";
// $merge->mergeMethod = MergeMethod::AddAndUpdate;
// $result = $merge->mergeOptions($target, $source);
$result = Merge::mergeOptionsWithMethod(MergeMethod::AddAndUpdate, $target3, $source);
print_r($result);
