<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Processing;

use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\Processing\LocalPreviewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ProcessingHelper extends LocalPreviewHelper
{
    /**
     * @param AbstractFile $file
     * @param array<string, int|string> $configuration
     * @param string $targetFilePath
     * @return string[]
     */
    protected function generatePreviewFromFile(AbstractFile $file, array $configuration, string $targetFilePath): array
    {
        try {
            $tempFile = $file->getStorage()->getFileForLocalProcessing($file);
        } catch (\Exception $exception) {
            $graphicalFunctions = GeneralUtility::makeInstance(GraphicalFunctions::class);
            $graphicalFunctions->getTemporaryImageWithText(
                $targetFilePath,
                'No image',
                'file found!',
                $file->getName()
            );
            return [
                'filePath' => $targetFilePath,
            ];
        }

        $processedImagePath = $this->generatePreviewFromLocalFile($tempFile, $configuration, $targetFilePath);
        GeneralUtility::unlink_tempfile($tempFile);
        return $processedImagePath;
    }
}
