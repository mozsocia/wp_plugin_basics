```php
<?php

$oldone = "2022-12-21";

$currentDate = date("Y-m-d");

// echo $currentDate;

$startTimestamp = strtotime($oldone);
$endTimestamp = strtotime($currentDate);

$diff = $endTimestamp - $startTimestamp;

$days = $diff / 86400;

echo $days . PHP_EOL;

$startDate = new DateTime($oldone);
$endDate = new DateTime($currentDate);

$diff = $startDate->diff($endDate);

echo $diff->days;
