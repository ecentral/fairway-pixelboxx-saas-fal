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
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceScheme;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class DomainConfigurationService
{

    private StorageRepository $storageRepository;

    public const IDENTIFIER = 'pixelboxx';
    protected string $identifier = self::IDENTIFIER;

    public function __construct(
        StorageRepository $storageRepository
    )
    {
        $this->storageRepository = $storageRepository;
    }

    public function getAssetPickerStorageIds(): array{
        $ids = [];
        $storages = $this->storageRepository->findByStorageType(PixelboxxDriver::DRIVER_NAME);
        foreach ($storages as $storage) {
            $storageId = $storage->getUid();
            $domain = $storage->getConfiguration()['pixelboxxDomain'] ?? '';
            if (!is_string($domain) || $domain === '') {
                // skip unfinished configuration;
            }
            $ids [] = $storageId;
        }
        return $ids;
    }

    public function getAssetPickerDomains(): array
    {
        $domains = [];
        $storages = $this->storageRepository->findByStorageType(PixelboxxDriver::DRIVER_NAME);
        foreach ($storages as $storage) {

            $storageId = $storage->getUid();
            $domain = $storage->getConfiguration()['pixelboxxDomain'] ?? '';
            if (!is_string($domain) || $domain === '') {
                throw new \Exception('Pixelboxx-Domain does not seem to be configured for %d', $storageId);
            }
            $domains [] = $domain;
        }
        return $domains;
    }

    public function getMutationCollection(): ?MutationCollection
    {
        $mutations = [];
        try {
            $domains = $this->getAssetPickerDomains();
            foreach ($domains as $domain) {
                $mutations [] = new Mutation(MutationMode::Extend, Directive::FrameSrc, SourceScheme::https, new UriValue($domain));
            }
            return $this->buildMutationCollectionFromArray($mutations);

        } catch (\Exception $e) {
        }
        return null;
    }

    private function buildMutationCollectionFromArray(array $array): MutationCollection
    {
        $mutations = array_map([$this, 'id'], $array);
        return new MutationCollection(...$mutations);
    }

    private function id(Mutation $m): Mutation
    {
        return $m;
    }
}
