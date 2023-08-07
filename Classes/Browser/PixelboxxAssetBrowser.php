<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Browser;

use Fairway\PixelboxxSaasApi\Client;
use Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Recordlist\Browser\AbstractElementBrowser;
use TYPO3\CMS\Recordlist\Browser\ElementBrowserInterface;
use TYPO3\CMS\Recordlist\Tree\View\LinkParameterProviderInterface;

final class PixelboxxAssetBrowser extends \TYPO3\CMS\Backend\ElementBrowser\AbstractElementBrowser
    implements  \TYPO3\CMS\Backend\ElementBrowser\ElementBrowserInterface, \TYPO3\CMS\Backend\Tree\View\LinkParameterProviderInterface
{
    private ResourceStorage $storage;
    private StorageRepository $storageRepository;

    public const IDENTIFIER = 'pixelboxx';
    protected string $identifier = self::IDENTIFIER;

    public function __construct(
        IconFactory $iconFactory,
        PageRenderer $pageRenderer,
        UriBuilder $uriBuilder,
        ExtensionConfiguration $extensionConfiguration,
        BackendViewFactory $backendViewFactory,
     //   ModuleTemplateFactory $moduleTemplateFactory,  // pre v12
        StorageRepository $storageRepository
    ) {
        $this->storageRepository = $storageRepository;
      //  parent::__construct($iconFactory, $pageRenderer, $uriBuilder, $moduleTemplateFactory);
        parent::__construct($iconFactory, $pageRenderer, $uriBuilder,$extensionConfiguration, $backendViewFactory);
    }

    protected function initialize(): void
    {
        parent::initialize();
        $this->initializeView();
        $this->initializeStorage();

        $this->pageRenderer->addCssFile(
            'EXT:pixelboxx_saas_fal/Resources/Public/Css/PixelboxxAssetBrowser.css'
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
                'LLL:EXT:pixelboxx_saas_fal/Resources/Private/Language/locallang_be.xlf:pixelboxx_asset_browser.title'
            )
        );

        $this->moduleTemplate->getView()->setTemplate('Search');
        $domain = $this->getAssetPickerDomain();
        $client = Client::createWithDomain($this->storage->getConfiguration()['pixelboxxDomain'])
            ->authenticate($this->storage->getConfiguration()['userName'], $this->storage->getConfiguration()['userPassword']);
        $this->moduleTemplate->getView()->assignMultiple([
            'storage' => $this->storage,
            'assetPickerDomain' => $domain,
            'token' => $client->getAccessToken(),
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

    /**
     * @param array<string, mixed> $values
     * @return array<string, string>
     */
    public function getUrlParameters(array $values): array
    {
        return [
            'mode' => 'pixelboxx',
            'bparams' => $this->bparams
        ];
    }

    /**
     * @param mixed[] $values
     * @return bool
     */
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
