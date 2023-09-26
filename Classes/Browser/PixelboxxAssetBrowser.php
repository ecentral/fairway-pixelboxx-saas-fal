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
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\View\FluidViewAdapter;
use TYPO3\CMS\Backend\Template\ModuleTemplate;

final class PixelboxxAssetBrowser extends \TYPO3\CMS\Backend\ElementBrowser\AbstractElementBrowser
    implements \TYPO3\CMS\Backend\ElementBrowser\ElementBrowserInterface, \TYPO3\CMS\Backend\Tree\View\LinkParameterProviderInterface
{
    private ResourceStorage $storage;
    private StorageRepository $storageRepository;

    public const IDENTIFIER = 'pixelboxx';
    protected string $identifier = self::IDENTIFIER;

    public function __construct(
        IconFactory            $iconFactory,
        PageRenderer           $pageRenderer,
        UriBuilder             $uriBuilder,
        ExtensionConfiguration $extensionConfiguration,
        BackendViewFactory     $backendViewFactory,
        StorageRepository      $storageRepository
    )
    {
        $this->storageRepository = $storageRepository;
        parent::__construct($iconFactory, $pageRenderer, $uriBuilder, $extensionConfiguration, $backendViewFactory);
    }

    protected function initialize(): void
    {
        parent::initialize();
        $this->initializeStorage();

        $this->pageRenderer->addCssFile(
            'EXT:pixelboxx_saas_fal/Resources/Public/Css/PixelboxxAssetBrowser.css'
        );
        if ((new Typo3Version())->getMajorVersion() >= 12) {
            $this->pageRenderer->loadJavaScriptModule('@fairway/pixelboxx-saas-fal/pixelboxx-asset-picker.js');
        } else {
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/PixelboxxSaasFal/PixelboxxAssetPicker');
        }

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
        $templateView = $this->view;
        // Make sure that the base initialization creates an FluidView within an FluidViewAdapter
        $templateView = (fn($templateView): FluidViewAdapter => $templateView) ($templateView);


        $contentOnly = (bool)($this->getRequest()->getQueryParams()['contentOnly'] ?? false);
        $this->pageRenderer->setTitle($this->getLanguageService()->sL('LLL:EXT:pixelboxx_saas_fal/Resources/Private/Language/locallang_be.xlf:pixelboxx_asset_browser.title'));

        $domain = $this->getAssetPickerDomain();
        $client = Client::createWithDomain($this->storage->getConfiguration()['pixelboxxDomain'])
            ->authenticate($this->storage->getConfiguration()['userName'], $this->storage->getConfiguration()['userPassword']);

        $templateView->assignMultiple([
            'storage' => $this->storage,
            'assetPickerDomain' => $domain,
            'token' => $client->getAccessToken(),
        ]);

        $content = $this->view->render('PixelboxxAssetBrowser/Search');
        if ($contentOnly) {
            return $content;
        }

        $this->pageRenderer->setBodyContent('<body ' . $this->getBodyTagParameters() . '>' . $content);
        return $this->pageRenderer->render();
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
        $thisScript = (string)$this->uriBuilder->buildUriFromRoute(
            $this->getRequest()->getAttribute('route')->getOption('_identifier')
        );
        return $thisScript;
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
