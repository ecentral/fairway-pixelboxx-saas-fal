<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Processing;

use Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver;
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Resource\Processing\ProcessorInterface;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class PixelboxxFileProcessor implements ProcessorInterface
{
    public function canProcessTask(TaskInterface $task): bool
    {
        return $task->getName() === 'Preview'
            && $task->getSourceFile()->getStorage()->getDriverType() === PixelboxxDriver::DRIVER_NAME;
    }

    public function processTask(TaskInterface $task): void
    {
        $processedResult = $this->getHelper()->process($task);
        $task->setExecuted(false);
        if (!empty($processedResult['filePath']) && file_exists($processedResult['filePath'])) {
            $task->setExecuted(true);
            // providing a default width/height set if it could not be determined
            [$width, $height] = $this->getGraphicalFunctionsObject()->getImageDimensions($processedResult['filePath']) ?? [64, 64];
            $task->getTargetFile()->setName($task->getTargetFileName());
            $task->getTargetFile()->updateProperties(
                [
                    'width' => $width,
                    'height' => $height,
                    'size' => filesize($processedResult['filePath']),
                    'checksum' => $task->getConfigurationChecksum()
                ]
            );
            $task->getTargetFile()->updateWithLocalFile($processedResult['filePath']);
        }
    }

    private function getHelper(): ProcessingHelper
    {
        return GeneralUtility::makeInstance(ProcessingHelper::class);
    }

    private function getGraphicalFunctionsObject(): GraphicalFunctions
    {
        return GeneralUtility::makeInstance(GraphicalFunctions::class);
    }
}
