<?php

return [
    'import_from_asset_builder' => [
        'path' => '/pixelboxx-saas-fal/import-file',
        'access' => 'public',
        'target' => \Fairway\PixelboxxSaasFal\Controller\Backend\AssetBrowserController::class . '::importFile'
    ],
];
