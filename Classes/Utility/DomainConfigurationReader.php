<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Utility;

use Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class DomainConfigurationReader
{
    public const IDENTIFIER = 'pixelboxx';

    protected string $identifier = self::IDENTIFIER;
    private ResourceStorage $storage;
    private StorageRepository $storageRepository;

    public function __construct(
        StorageRepository $storageRepository
    )
    {
        $this->storageRepository = $storageRepository;
        $this->initializeStorage();
    }

    public static function getDomain(): string
    {
        $domainConfigurationReader =  GeneralUtility::makeInstance(DomainConfigurationReader::class);
        return $domainConfigurationReader->getAssetPickerDomain();
    }


    private function initializeStorage(): void
    {
        $storageId = (int)(explode('|', $this->bparams)[5] ?? 0);
        $this->storage = $this->findStorageById($storageId);
    }

    private function findStorageById(int $storageId): ResourceStorage
    {
        $storage = $this->storageRepository->findByUid($storageId);
        if ($storage === null || $storage->getDriverType() !== PixelboxxDriver::DRIVER_NAME) {
            throw new \Exception('Invalid pixelboxx storage id given.');
        }
        return $storage;
    }

    private function getAssetPickerDomain(): string
    {
        $domain = $this->storage->getConfiguration()['pixelboxxDomain'] ?? '';
        if (!is_string($domain) || $domain === '') {
            throw new \Exception('Pixelboxx-Domain does not seem to be configured for %d', $this->storage->getUid());
        }
        return $domain;
    }
}
