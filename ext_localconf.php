<?php

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['registeredDrivers'][\Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver::DRIVER_NAME] = [
    'class' => \Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver::class,
    'shortName' => \Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver::DRIVER_NAME,
    'flexFormDS' => 'FILE:EXT:pixelboxx_saas_fal/Configuration/FlexForm/PixelboxxDriver.xml',
    'label' => 'Pixelboxx DAM',
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processors']['PixelboxxFileProcessor'] = [
    'className' => \Fairway\PixelboxxSaasFal\Processing\PixelboxxFileProcessor::class,
    'before' => [
        'SvgImageProcessor'
    ]
];

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::class)
    ->registerExtractionService(\Fairway\PixelboxxSaasFal\Extractor\PixelboxxFileExtractor::class);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1628070217] = [
    'nodeName' => 'inline',
    'priority' => 100,
    'class' => \Fairway\PixelboxxSaasFal\Form\Container\InlineControlContainer::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ElementBrowsers']['pixelboxx'] = \Fairway\PixelboxxSaasFal\Browser\PixelboxxAssetBrowser::class;
