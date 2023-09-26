<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Listener;

use Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver;
use TYPO3\CMS\Core\Resource\Event\BeforeFileProcessingEvent;

final class BeforeFileProcessingEventListener
{
    public function __invoke(BeforeFileProcessingEvent $event): void
    {
        if (!($event->getDriver() instanceof PixelboxxDriver)) {
            return;
        }

        $processedFile = $event->getProcessedFile();
        $configuration = $event->getConfiguration();
        $processedFile->setUsesOriginalFile();
        if (isset($configuration['width'])){
            if (isset($configuration['maxWidth']) && $configuration['maxWidth'] > $configuration['width']) {
                $properties['width'] = $configuration['maxWidth'];
            } else {
                $properties['width'] = $configuration['width'];
            }
        } else {
            $properties['width'] = null;
        }

        if (isset($configuration['height'])){            
            if (isset($configuration['maxHeight']) && $configuration['maxHeight'] > $configuration['height']) {
                $properties['height'] = $configuration['maxHeight'];
            } else {

                $properties['height'] = $configuration['height'];
            }
        } else {            
            $properties['width'] = null;
        }
        
        $processedFile->updateProperties($properties);
        $event->setProcessedFile($processedFile);
    }
}
