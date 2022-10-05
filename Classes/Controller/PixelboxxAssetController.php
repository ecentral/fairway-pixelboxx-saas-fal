<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Resource\StorageRepository;

final class PixelboxxAssetController
{
    protected StorageRepository $storageRepository;

    public function __construct(StorageRepository $storageRepository)
    {
        $this->storageRepository = $storageRepository;
    }
}
