<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Browser;

use Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Recordlist\Browser\AbstractElementBrowser;
use TYPO3\CMS\Recordlist\Browser\ElementBrowserInterface;
use TYPO3\CMS\Recordlist\Tree\View\LinkParameterProviderInterface;

final class PixelboxxAssetBrowser extends AbstractElementBrowser implements ElementBrowserInterface, LinkParameterProviderInterface
{
    private ResourceStorage $storage;
    private StorageRepository $storageRepository;

    public function __construct(
        IconFactory $iconFactory,
        PageRenderer $pageRenderer,
        UriBuilder $uriBuilder,
        ModuleTemplateFactory $moduleTemplateFactory,
        StorageRepository $storageRepository
    ) {
        $this->storageRepository = $storageRepository;
        parent::__construct($iconFactory, $pageRenderer, $uriBuilder, $moduleTemplateFactory);
    }

    protected function initialize()
    {
        parent::initialize();
        $this->initializeView();
        $this->initializeStorage();

        $this->pageRenderer->addCssFile(
            'EXT:canto_saas_fal/Resources/Public/Css/CantoAssetBrowser.css'
        );
        $this->pageRenderer->loadRequireJsModule(
            'TYPO3/CMS/PixelboxxSaasFal/PixelboxxAssetPicker'
        );
    }

    protected function getBodyTagAttributes(): array
    {
        return [
            'data-mode' => 'pixelboxx',
            'data-storage-uid' => (string)$this->storage->getUid(),
            'data-asset-picker-domain' => $this->getAssetPickerDomain(),
        ];
    }

    public function getAssetPickerDomain(): string
    {
        $domain = $this->storage->getConfiguration()['pixelboxxDomain'] ?? '';
        if (!is_string($domain) || $domain === '') {
            throw new \Exception('Pixelboxx-Domain does not seem to be configured for %d', $this->storage->getUid());
        }
        return $domain;
    }

    public function render(): string
    {
        $this->setBodyTagParameters();
        $this->moduleTemplate->setTitle(
            $this->getLanguageService()->sL(
                'LLL:EXT:canto_saas_fal/Resources/Private/Language/locallang_be.xlf:pixelboxx_asset_browser.title'
            )
        );

        $this->moduleTemplate->getView()->setTemplate('Search');
        $this->moduleTemplate->getView()->assignMultiple([
            'storage' => $this->storage,
            'assetPickerDomain' => $this->getAssetPickerDomain(),
        ]);
        return $this->moduleTemplate->renderContent();
    }

    /**
     * @param mixed[] $data
     * @return array<int, mixed[]|false>
     */
    public function processSessionData($data): array
    {
        return [$data, false];
    }

    public function getScriptUrl(): string
    {
        return $this->thisScript;
    }

    public function getUrlParameters(array $values): array
    {
        return [
            'mode' => 'pixelboxx',
            'bparams' => $this->bparams
        ];
    }

    public function isCurrentlySelectedItem(array $values): bool
    {
        return false;
    }

    private function initializeView(): void
    {
        $view = $this->moduleTemplate->getView();
        $view->setLayoutRootPaths([
            100 => 'EXT:pixelboxx_saas_fal/Resources/Private/Layouts/'
        ]);
        $view->setPartialRootPaths([
            100 => 'EXT:pixelboxx_saas_fal/Resources/Private/Partials/',
        ]);
        $view->setTemplateRootPaths([
            100 => 'EXT:pixelboxx_saas_fal/Resources/Private/Templates/PixelboxxAssetBrowser/'
        ]);
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
}
