<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Driver;

use Fairway\FairwayFilesystemApi\Adapter\PixelboxxAdapter\Driver;
use Fairway\FairwayFilesystemApi\Directory;
use Fairway\FairwayFilesystemApi\FileType;
use Fairway\PixelboxxSaasApi\Client;
use Fairway\PixelboxxSaasApi\PixelboxxResourceName;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\FalDumpFileContentsDecoratorStream;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Resource\Driver\AbstractHierarchicalFilesystemDriver;
use TYPO3\CMS\Core\Resource\Driver\StreamableDriverInterface;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\Exception\NotImplementedMethodException;

class PixelboxxDriver extends AbstractHierarchicalFilesystemDriver implements StreamableDriverInterface
{
    public const DRIVER_NAME = 'Pixelboxx';
    private ?Driver $driver;
    private bool $validConfiguration = false;

    public function __construct(array $configuration = [], Driver $driver = null)
    {
        parent::__construct($configuration);
        $this->capabilities = ResourceStorage::CAPABILITY_BROWSABLE;
        $this->driver = $driver;
    }

    public function processConfiguration()
    {
        $this->validConfiguration = is_int($this->storageUid)
            && $this->storageUid > 0
            && ($this->configuration['userName'] ?? '') !== ''
            && ($this->configuration['userPassword'] ?? '') !== ''
            && ($this->configuration['pixelboxxDomain'] ?? '') !== '';
    }

    public function initialize()
    {
        if ($this->driver === null && $this->validConfiguration === true) {
            $client = Client::createWithDomain($this->configuration['pixelboxxDomain'])
                ->authenticate($this->configuration['userName'], $this->configuration['userPassword']);
            $this->driver = new Driver($client);
        }
    }

    public function mergeConfigurationCapabilities($capabilities): int
    {
        $this->capabilities &= $capabilities;
        return $this->capabilities;
    }

    public function getRootLevelFolder(): string
    {
        return '';
    }

    public function getDefaultFolder(): string
    {
        return $this->getRootLevelFolder();
    }

    public function getPublicUrl($identifier): string
    {
        return $this->driver->getPublicUrl($identifier);
    }

    public function createFolder($newFolderName, $parentFolderIdentifier = '', $recursive = false)
    {
        throw new NotImplementedMethodException();
    }

    public function renameFolder($folderIdentifier, $newName)
    {
        throw new NotImplementedMethodException();
    }

    public function deleteFolder($folderIdentifier, $deleteRecursively = false)
    {
        throw new NotImplementedMethodException();
    }

    public function fileExists($fileIdentifier): bool
    {
        return $this->driver->exists($this->getIdentifier($fileIdentifier, FileType::FILE), FileType::FILE);
    }

    public function folderExists($folderIdentifier): bool
    {
        $identifier = $this->getIdentifier($folderIdentifier, FileType::DIRECTORY);
        if ($identifier === null) {
            // root folder
            return true;
        }
        return $this->driver->exists($identifier, FileType::DIRECTORY);
    }

    public function isFolderEmpty($folderIdentifier): bool
    {
        return $this->driver->listDirectory($this->getIdentifier($folderIdentifier, FileType::DIRECTORY))->count() === 0;
    }

    public function addFile($localFilePath, $targetFolderIdentifier, $newFileName = '', $removeOriginal = true)
    {
        throw new NotImplementedMethodException();
    }

    public function createFile($fileName, $parentFolderIdentifier)
    {
        throw new NotImplementedMethodException();
    }

    public function copyFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $fileName)
    {
        throw new NotImplementedMethodException();
    }

    public function renameFile($fileIdentifier, $newName)
    {
        throw new NotImplementedMethodException();
    }

    public function replaceFile($fileIdentifier, $localFilePath)
    {
        throw new NotImplementedMethodException();
    }

    public function deleteFile($fileIdentifier)
    {
        throw new NotImplementedMethodException();
    }

    public function hash($fileIdentifier, $hashAlgorithm)
    {
        return hash($hashAlgorithm, $fileIdentifier);
    }

    public function moveFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $newFileName)
    {
        throw new NotImplementedMethodException();
    }

    public function moveFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName)
    {
        throw new NotImplementedMethodException();
    }

    public function copyFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName)
    {
        throw new NotImplementedMethodException();
    }

    public function getFileContents($fileIdentifier)
    {
        return $this->driver->read($this->getIdentifier($fileIdentifier, FileType::FILE));
    }

    public function setFileContents($fileIdentifier, $contents)
    {
        throw new NotImplementedMethodException();
    }

    public function fileExistsInFolder($fileName, $folderIdentifier)
    {
        throw new NotImplementedMethodException();
    }

    public function folderExistsInFolder($folderName, $folderIdentifier)
    {
        throw new NotImplementedMethodException();
    }

    public function getFileForLocalProcessing($fileIdentifier, $writable = true)
    {
        throw new NotImplementedMethodException();
    }

    public function getPermissions($identifier)
    {
        return [
            'r' => true,
            'w' => false,
        ];
    }

    public function dumpFileContents($identifier)
    {
        echo $this->getFileContents($this->getIdentifier($identifier, FileType::FILE));
    }

    public function isWithin($folderIdentifier, $identifier)
    {
        throw new NotImplementedMethodException();
    }

    public function getFileInfoByIdentifier($fileIdentifier, array $propertiesToExtract = [])
    {
        try {
            $prn = (string)(new PixelboxxResourceName($fileIdentifier));
        } catch (\Exception $exception) {
            $prn = $this->getIdentifier($fileIdentifier, FileType::FILE);
        }
        $asset = $this->driver->getFile($prn);
        $combinedDirectoryIdentifier = implode(',', array_map(
            fn (Directory $directory) => $directory->getIdentifier(),
            $asset->getParentOfIdentifier()->toArray()
        ));
        return [
            'identifier' => $asset->getIdentifier(),
            'name' => $asset->getFileName(),
            'mtime' => $asset->getMTime(),
            'ctime' => $asset->getCTime(),
            'hash' => $this->hash($prn, 'md5'),
            'extension' => $asset->getExtension(),
            'mimetype' => $asset->getMimeType(),
            'size' => $asset->getSize(),
            'folder_hash' => $this->hash($combinedDirectoryIdentifier, 'md5'),
            'storage' => $this->storageUid
        ];
    }

    public function getFolderInfoByIdentifier($folderIdentifier): array
    {
        $directory = $this->driver->getDirectory($this->getIdentifier($folderIdentifier, FileType::DIRECTORY));
        return [
            'identifier' => $directory->getIdentifier(),
            'name' => $directory->getFileName(),
            'mtime' => $directory->getMTime(),
            'ctime' => $directory->getCTime(),
            'storage' => $this->storageUid
        ];
    }

    public function getFileInFolder($fileName, $folderIdentifier)
    {
        throw new NotImplementedMethodException();
    }

    public function getFilesInFolder($folderIdentifier, $start = 0, $numberOfItems = 0, $recursive = false, array $filenameFilterCallbacks = [], $sort = '', $sortRev = false)
    {
        if (!$folderIdentifier) {
            return [];
        }
        $folderWithAssets = $this->driver->getClient()->folders()->getFolderAssets($this->getIdentifier($folderIdentifier, FileType::DIRECTORY));
        if ($folderWithAssets === null) {
            return [];
        }
        $assets = [];
        foreach ($folderWithAssets->getFolder()->getAssets() as $asset) {
            $assets[] = (string)$asset->getId();
        }
        return $assets;
    }

    public function getFolderInFolder($folderName, $folderIdentifier)
    {
        throw new NotImplementedMethodException();
    }

    public function getFoldersInFolder($folderIdentifier, $start = 0, $numberOfItems = 0, $recursive = false, array $folderNameFilterCallbacks = [], $sort = '', $sortRev = false)
    {
        $folders = $this->driver->listDirectory($this->getIdentifier($folderIdentifier, FileType::DIRECTORY), $recursive);
        $directories = [];
        /** @var Directory $folder */
        foreach ($folders as $folder) {
            $directories[] = $folder->getIdentifier();
        }
        return $directories;
    }

    public function countFilesInFolder($folderIdentifier, $recursive = false, array $filenameFilterCallbacks = []): int
    {
        if (!$folderIdentifier) {
            return 0;
        }
        $assets = $this->driver->getClient()
            ->folders()
            ->getFolderAssets($this->getIdentifier($folderIdentifier, FileType::DIRECTORY))
            ->getFolder()
            ->getAssets();
        return count($assets);
    }

    public function countFoldersInFolder($folderIdentifier, $recursive = false, array $folderNameFilterCallbacks = []): int
    {
        // todo: recursive and filter is missing
        $directories = $this->driver->listDirectory($this->getIdentifier($folderIdentifier, FileType::DIRECTORY));
        return $directories->count();
    }

    public function streamFile(string $identifier, array $properties): ResponseInterface
    {
        $fileInfo = $this->getFileInfoByIdentifier($identifier, ['name', 'mimetype', 'mtime', 'size']);
        $downloadName = $properties['filename_overwrite'] ?? $fileInfo['name'] ?? '';
        $mimeType = $properties['mimetype_overwrite'] ?? $fileInfo['mimetype'] ?? '';
        $contentDisposition = ($properties['as_download'] ?? false) ? 'attachment' : 'inline';
        return new Response(
            new FalDumpFileContentsDecoratorStream($identifier, $this, (int)$fileInfo['size']),
            200,
            [
                'Content-Disposition' => $contentDisposition . '; filename="' . $downloadName . '"',
                'Content-Type' => $mimeType,
                'Content-Length' => (string)$fileInfo['size'],
                'Last-Modified' => gmdate('D, d M Y H:i:s', $fileInfo['mtime']) . ' GMT',
                'Cache-Control' => '',
            ]
        );
    }

    private function getIdentifier(string $identifier, string $fileType): ?string
    {
        try {
            return (string)(new PixelboxxResourceName($identifier));
        } catch (\Exception $exception) {
        }
        $newIdentifier = trim($identifier, '/');
        $newIdentifier = array_reverse(explode('/', $newIdentifier))[0];
        if ($newIdentifier === '') {
            return null;
        }
        if ($fileType === FileType::DIRECTORY) {
            return (string)PixelboxxResourceName::prnFromResource($this->driver->getClient(), PixelboxxResourceName::FOLDER, $newIdentifier);
        }
        return (string)PixelboxxResourceName::prnFromResource($this->driver->getClient(), PixelboxxResourceName::ASSET, $newIdentifier);
    }
}
