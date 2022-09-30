<?php

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['registeredDrivers'][\Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver::DRIVER_NAME] = [
    'class' => \Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver::class,
    'shortName' => \Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver::DRIVER_NAME,
    'flexFormDS' => 'FILE:EXT:pixelboxx_saas_fal/Configuration/FlexForm/PixelboxxDriver.xml',
    'label' => 'Pixelboxx DAM',
];
