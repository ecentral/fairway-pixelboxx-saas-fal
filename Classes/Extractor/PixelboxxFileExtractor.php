<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Extractor;

use Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver;
use Fairway\PixelboxxSaasFal\Metadata\MetadataManager;
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Index\ExtractorInterface;

final class PixelboxxFileExtractor implements ExtractorInterface
{
    private MetadataManager $manager;

    public function __construct(MetadataManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return int[]
     */
    public function getFileTypeRestrictions(): array
    {
        return [AbstractFile::FILETYPE_IMAGE];
    }

    /**
     * @return string[]
     */
    public function getDriverRestrictions(): array
    {
        return [PixelboxxDriver::DRIVER_NAME];
    }

    public function getPriority(): int
    {
        return 23;
    }

    public function getExecutionPriority(): int
    {
        return 23;
    }

    public function canProcess(File $file): bool
    {
        return $file->getType() == AbstractFile::FILETYPE_IMAGE
            && $file->getStorage()->getDriverType() === PixelboxxDriver::DRIVER_NAME;
    }

    /**
     * @param File $file
     * @param array<string, mixed> $previousExtractedData
     * @return array<string, mixed>
     */
    public function extractMetaData(File $file, array $previousExtractedData = []): array
    {
        $metadata = $this->manager->syncFromRemote($file, $previousExtractedData);
        $this->manager->syncTranslationsFromRemote($file);
        return $metadata;
    }
}
