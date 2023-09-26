<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Form\Container;

use Fairway\PixelboxxSaasFal\Service\DomainConfigurationService;
use Fairway\PixelboxxSaasFal\Service\DomainConfigurationServiceFactory;
use TYPO3\CMS\Backend\Form\Container\FilesControlContainer as FilesControlContainerCore;
use TYPO3\CMS\Core\Resource\Filter\FileExtensionFilter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class FileControlContainer extends FilesControlContainerCore
{
    /**
     * Generate buttons to select, reference and upload files.
     */
    protected function getFileSelectors(array $inlineConfiguration, FileExtensionFilter $fileExtensionFilter): array
    {
        $fileSelectors = parent::getFileSelectors($inlineConfiguration, $fileExtensionFilter);

        /** @var  DomainConfigurationService $service */
        $service = GeneralUtility::makeInstance(DomainConfigurationServiceFactory::class)();
        $storageIds = $service->getAssetPickerStorageIds();

        foreach ($storageIds as $storageId) {
            if ($storageId > 0) {
                $newbuttonData = $this->renderPixelboxxAssetPickerButton($inlineConfiguration, $storageId, count($storageIds) > 1);
                $fileSelectors[] = $newbuttonData;
            }
        }
        return $fileSelectors;
    }

    /**
     * @param array<string, mixed> $inlineConfiguration
     * @param int $storageId
     * @return string
     */
    private function renderPixelboxxAssetPickerButton(array $inlineConfiguration, int $storageId, bool $renderStorageId = false): string
    {
        $buttonStyle = '';
        if (isset($inlineConfiguration['inline']['inlineNewRelationButtonStyle'])) {
            $buttonStyle = ' style="' . $inlineConfiguration['inline']['inlineNewRelationButtonStyle'] . '"';
        }

        $foreignTable = $inlineConfiguration['foreign_table'];
        $allowed = $inlineConfiguration['allowed'];
        $currentStructureDomObjectIdPrefix = $this->inlineStackProcessor->getCurrentStructureDomObjectIdPrefix(
            $this->data['inlineFirstPid']
        );
        $objectPrefix = $currentStructureDomObjectIdPrefix . '-' . $foreignTable;

        $title = htmlspecialchars($this->getLanguageService()->sL('LLL:EXT:pixelboxx_saas_fal/Resources/Private/Language/locallang_be.xlf:pixelboxx_asset_browser.add_button_title'));
        if ($renderStorageId) { // multiple storage configurations present, result in rendering ids behind buttton
            $title .= ' [' . $storageId . ']';
        }
        $browserParams = '|||' . $allowed . '|' . $objectPrefix . '|' . $storageId;
        $icon = '';
        return <<<HTML
<button type="button" class="btn btn-default t3js-element-browser" data-mode="pixelboxx" data-params="$browserParams" $buttonStyle title="$title">
$icon $title
</button>
HTML;
    }

}
