<?php

declare(strict_types=1);

namespace Lea;

use Lea\Adore\Fancy;
use Lea\Adore\Girlfriend;
use Lea\Adore\Heath;
use Lea\Adore\PaisleyPark;

define(constant_name: 'ROOT', value: dirname(path: __DIR__) . "/Heath");
define(constant_name: 'REPO', value: realpath(path: ".") . "/arx/");
require_once ROOT . "/vendor/autoload.php";

Girlfriend::comeToMe()->emotionalPump();

$heath = new Heath();
foreach ($heath->ebookFiles as $fileName) {
    echo $heath->heathName . Fancy::BG_BLUE . " $fileName " . Fancy::RESET . PHP_EOL;
    Girlfriend::comeToMe()->myNameIsLea();
    $work = new PaisleyPark(fileName: $fileName);
    $work->pControl();
    $heath->makeIndex($work)->writeIndex();
}
