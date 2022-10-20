<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Form\Container;

use TYPO3\CMS\Backend\Form\Container\InlineControlContainer as Typo3InlineControlContainer;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

final class InlineControlContainer extends Typo3InlineControlContainer
{
    /**
     * @param array<string, mixed> $inlineConfiguration
     * @return string
     */
    protected function renderPossibleRecordsSelectorTypeGroupDB(array $inlineConfiguration): string
    {
        $buttons = parent::renderPossibleRecordsSelectorTypeGroupDB($inlineConfiguration);
        if (($storageId = $this->getTargetStorageId()) > 0) {
            $buttons = $this->appendButton(
                $buttons,
                $this->renderPixelboxxAssetPickerButton($inlineConfiguration, $storageId)
            );
        }
        return $buttons;
    }

    /**
     * @param array<string, mixed> $inlineConfiguration
     * @param int $storageId
     * @return string
     */
    private function renderPixelboxxAssetPickerButton(array $inlineConfiguration, int $storageId): string
    {
        $buttonStyle = '';
        if (isset($inlineConfiguration['inline']['inlineNewRelationButtonStyle'])) {
            $buttonStyle = ' style="' . $inlineConfiguration['inline']['inlineNewRelationButtonStyle'] . '"';
        }
        $groupFieldConfiguration = $inlineConfiguration['selectorOrUniqueConfiguration']['config'];
        $foreign_table = $inlineConfiguration['foreign_table'];
        $allowed = $groupFieldConfiguration['allowed'];
        $currentStructureDomObjectIdPrefix = $this->inlineStackProcessor->getCurrentStructureDomObjectIdPrefix(
            $this->data['inlineFirstPid']
        );
        $objectPrefix = $currentStructureDomObjectIdPrefix . '-' . $foreign_table;
        if (is_array($groupFieldConfiguration['appearance'])) {
            if (isset($groupFieldConfiguration['appearance']['elementBrowserAllowed'])) {
                $allowed = $groupFieldConfiguration['appearance']['elementBrowserAllowed'];
            }
        }
        $title = 'Add Pixelboxx file';
        $browserParams = '|||' . $allowed . '|' . $objectPrefix . '|' . $storageId;
        $icon = '';
        return <<<HTML
<button type="button" class="btn btn-default t3js-element-browser" data-mode="pixelboxx" data-params="$browserParams" $buttonStyle title="$title">
$icon $title
</button>
HTML;
    }

    private function getTargetStorageId(): int
    {
        return 2;
    }

    private function appendButton(string $origHtml, string $buttonHtml): string
    {
        $inlineControlsPosition = strpos($origHtml, 't3js-inline-controls') ?: 0;
        $inlineControlsClosingTagPosition = strpos($origHtml, '</div>', $inlineControlsPosition) ?: 0;
        return substr_replace($origHtml, $buttonHtml, $inlineControlsClosingTagPosition, 0);
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
