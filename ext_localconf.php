<?php

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['registeredDrivers'][\Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver::DRIVER_NAME] = [
    'class' => \Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver::class,
    'shortName' => \Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver::DRIVER_NAME,
    'flexFormDS' => 'FILE:EXT:pixelboxx_saas_fal/Configuration/FlexForm/PixelboxxDriver.xml',
    'label' => 'Pixelboxx DAM',
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1628070217] = [
    'nodeName' => 'inline',
    'priority' => 100,
    'class' => \Fairway\PixelboxxSaasFal\Form\Container\InlineControlContainer::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ElementBrowsers']['pixelboxx'] = \Fairway\PixelboxxSaasFal\Browser\PixelboxxAssetBrowser::class;
