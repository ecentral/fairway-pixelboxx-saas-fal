<?php

declare(strict_types=1);

namespace Fairway\PixelboxxSaasFal\Listener;
use Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver;
use TYPO3\CMS\Core\Resource\Event\BeforeFileProcessingEvent;
use TYPO3\CMS\Core\Resource\ProcessedFile;

final class BeforeFileProcessingEventListener
{
    public function __invoke(BeforeFileProcessingEvent $event)
    {
        if (!($event->getDriver() instanceof PixelboxxDriver)) {
            return;
        }

        $processedFile = $event->getProcessedFile();
//        if (in_array($event->getTaskType(), [
//            ProcessedFile::CONTEXT_IMAGECROPSCALEMASK,
//        ], true)) {
//            $processedFile->setUsesOriginalFile();
//            $event->setProcessedFile($processedFile);
//            return;
//        }

        $configuration = $event->getConfiguration();
//        $properties['processing_url'] = $event->getDriver()->getPublicUrl($processedFile->getIdentifier());
//        if (!isset($configuration['width'], $configuration['height'])) {
//            [$width, $height] = getimagesize($properties['processing_url']);
//            $configuration['width'] = $width;
//            $configuration['height'] = $height;
//        }
        $processedFile->setUsesOriginalFile();
        $properties['width'] = $configuration['width'];
        $properties['height'] = $configuration['height'];
        $processedFile->updateProperties($properties);
        $event->setProcessedFile($processedFile);
    }
}
