<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Metadata;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\MapperBuilder;
use Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver;
use Fairway\PixelboxxSaasFal\Mapping\MetadataMapping;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Type\File\ImageInfo;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class MetadataManager
{
    use LoggerAwareTrait;

    // todo: reactivate again
    // private MetadataRepository $metadataRepository;

    public function __construct(
        // MetadataRepository $metadataRepository,
        LoggerInterface $logger
    ) {
        // $this->metadataRepository = $metadataRepository;
        $this->logger = $logger;
    }

    /**
     * @param AbstractFile $file
     * @param array<string, string|int|bool> $previousExtractedData
     * @return array<string, mixed>
     */
    public function syncFromRemote(FileInterface $file, array $previousExtractedData = []): array
    {
        $data = $this->processMetadata($file, [
            'xsize' => 'width',
            'ysize' => 'height',
            'whocreatedit' => 'creator',
            'iptccorexmp__creator' => 'creator_tool',
            // todo: copyright mapping
        ]);
        $metadata = array_merge($data, $previousExtractedData);
        $metadata = array_replace(
            $metadata,
            [
                'width' => (int)($data['width'] ?? 0),
                'height' => (int)($data['height'] ?? 0),
            ]
        );
        if ($file instanceof AbstractFile && ($metadata['width'] === 0 || $metadata['height'] === 0)) {
            [$width, $height] = $this->getImageDimensionsOfRemoteFile($file);
            if (!$metadata['width']) {
                $metadata['width'] = $width;
            }
            if (!$metadata['height']) {
                $metadata['height'] = $height;
            }
        }
        return $metadata;
    }

    public function syncTranslationsFromRemote(AbstractFile $file): void
    {
        $config = $this->parseConfiguration($file);
        if ($config === null) {
            return;
        }
        $repository = GeneralUtility::makeInstance(MetadataRepository::class);
        $metadataParentUid = $repository->findByFileUidAndLanguageUid($file->getUid(), 0)['uid'] ?? null;
        foreach ($config->localizationMapping as $localizationMapping) {
            if ($localizationMapping->languageId === 0) {
                if ($metadataParentUid === null) {
                    $metadataParentUid = $repository->createMetaDataRecord($file->getUid(), $this->processMetadata($file))['uid'];
                }
                continue;
            }
            $metadata = $this->processMetadata($file, [], $localizationMapping->languageCode);
            $result = $repository->findByFileUidAndLanguageUid($file->getUid(), $localizationMapping->languageId);
            if (count($result) > 0) {
                $repository->updateByFileUidAndLanguageUid(
                    $file->getUid(),
                    $localizationMapping->languageId,
                    array_replace($result, $metadata)
                );
            } elseif ($metadataParentUid !== null) {
                $repository->createMetaDataRecord($file->getUid(), array_merge(
                    [
                        'sys_language_uid' => $localizationMapping->languageId,
                        'l10n_parent' => $metadataParentUid,
                    ],
                    $metadata
                ));
            }
        }
    }

    /**
     * @param AbstractFile $file
     * @param array<string, string> $defaultMappedProperties
     * @param string|null $languageCode
     * @return array<string, mixed>
     * @throws \Fairway\FairwayFilesystemApi\Exceptions\NotSupportedException
     */
    private function processMetadata(AbstractFile $file, array $defaultMappedProperties = [], string $languageCode = null): array
    {
        $storage = $file->getStorage();
        $storageReflection = new \ReflectionClass($storage);
        if ($storage->getDriverType() !== PixelboxxDriver::DRIVER_NAME || !$storageReflection->hasProperty('driver')) {
            return [];
        }
        $config = $this->parseConfiguration($file);
        /** @var PixelboxxDriver $driver */
        $driver = $storageReflection->getProperty('driver')->getValue($storage);
        $metadata = $driver->getMetadata($file->getIdentifier());
        $mappedMetadata = [];
        foreach ($metadata as $item) {
            if ($config instanceof MetadataMapping) {
                foreach ($config->propertyMapping as $property) {
                    if ($item->getPropertyId() === $property->property) {
                        $value = $item->getValue();
                        if (
                            $languageCode !== null
                            && in_array($languageCode, $property->translateToLanguages, true)
                        ) {
                            $value = $item->getLocalizedValue($languageCode);
                            if ($value === null) {
                                continue;
                            }
                        }
                        $mappedMetadata[$property->identifier] = $value;
                    }
                }
            }
            if (isset($defaultMappedProperties[$item->getPropertyId()])) {
                $mappedMetadata[$defaultMappedProperties[$item->getPropertyId()]] = $item->getValue();
            }
        }
        return $mappedMetadata;
    }

    private function parseConfiguration(AbstractFile $file): ?MetadataMapping
    {
        $configuration = $file->getStorage()->getConfiguration();
        if (!array_key_exists('metadataMapping', $configuration)) {
            return null;
        }
        try {
            return (new MapperBuilder())
                ->mapper()
                ->map(
                    MetadataMapping::class,
                    new JsonSource($configuration['metadataMapping'])
                );
        } catch (MappingError $error) {
            $node = $error->node();
            $messages = new MessagesFlattener($node);
            $errorMessages = $messages->errors();

            $messageList = array_map((fn (NodeMessage $message) => (string)$message), iterator_to_array($errorMessages));
            /** @var LoggerInterface $logger */
            $logger = $this->logger;
            $logger->error(sprintf("Could not parse Content\n%s", implode(',', $messageList)));
            return null;
        }
    }

    /**
     * @param AbstractFile $file
     * @return int[]
     */
    public function getImageDimensionsOfRemoteFile(AbstractFile $file): array
    {
        $imageInfo = GeneralUtility::makeInstance(
            ImageInfo::class,
            $file->getForLocalProcessing()
        );
        return [$imageInfo->getWidth(), $imageInfo->getHeight()];
    }
}
