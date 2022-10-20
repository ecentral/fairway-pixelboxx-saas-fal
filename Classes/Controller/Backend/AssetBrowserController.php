<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Controller\Backend;

use Fairway\PixelboxxSaasApi\Model\Asset;
use Fairway\PixelboxxSaasFal\Driver\PixelboxxDriver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class AssetBrowserController
{
    protected StorageRepository $storageRepository;

    public function __construct()
    {
        $this->storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
    }

    public function importFile(ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $storage = $this->getCantoStorageByUid(2);
        if (is_array($data) && count($data) === 1) {
            $asset = Asset::createFromArray($data[0]);
            $file = $storage->getFile((string)$asset->getId());
            if ($file instanceof FileInterface) {
                return new JsonResponse([
                    'fileUid' => $file->getUid(),
                    'fileName' => $file->getName(),
                ]);
            }
        }
        return new Response(null, 400);
    }

    protected function getCantoStorageByUid(int $uid): ResourceStorage
    {
        $storage = $this->storageRepository->findByUid($uid);
        if ($storage === null || $storage->getDriverType() !== PixelboxxDriver::DRIVER_NAME) {
            throw new \Exception('The given storage is not a canto storage.', 1628166504);
        }
        return $storage;
    }
}
