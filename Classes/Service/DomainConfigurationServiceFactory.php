<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Service;

use Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class DomainConfigurationServiceFactory
{
    private StorageRepository $storageRepository;

    public function injectStorageRepository(StorageRepository $storageRepository) : void
    {
        $this->storageRepository = $storageRepository;
    }

    public function __invoke(): ?DomainConfigurationService
    {
        return new DomainConfigurationService($this->storageRepository);
    }

}
