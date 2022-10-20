<?php

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'import_from_asset_builder' => [
        'path' => '/pixelboxx-saas-fal/import-file',
        'access' => 'public',
        'target' => \Fairway\PixelboxxSaasFal\Controller\Backend\AssetBrowserController::class . '::importFile'
    ],
];
