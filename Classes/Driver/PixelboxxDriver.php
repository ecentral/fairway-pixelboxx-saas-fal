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
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\Driver\AbstractHierarchicalFilesystemDriver;
use TYPO3\CMS\Core\Resource\Driver\StreamableDriverInterface;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\Exception\NotImplementedMethodException;

class PixelboxxDriver extends AbstractHierarchicalFilesystemDriver implements StreamableDriverInterface
{
    public const DRIVER_NAME = 'Pixelboxx';
    private ?Driver $driver;
    private bool $validConfiguration = false;

    /**
     * @param array{userName?: string, userPassword?: string, pixelboxxDomain?: string} $configuration
     * @param Driver|null $driver
     */
    public function __construct(array $configuration = [], Driver $driver = null)
    {
        parent::__construct($configuration);
        $this->capabilities = ResourceStorage::CAPABILITY_BROWSABLE;
        $this->driver = $driver;
    }

    public function processConfiguration(): void
    {
        $this->validConfiguration = is_int($this->storageUid)
            && $this->storageUid > 0
            && ($this->configuration['userName'] ?? '') !== ''
            && ($this->configuration['userPassword'] ?? '') !== ''
            && ($this->configuration['pixelboxxDomain'] ?? '') !== '';
    }

    public function initialize(): void
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
        return $this->getDriver()->getPublicUrl($identifier);
    }

    /**
     * @param string $newFolderName
     * @param string $parentFolderIdentifier
     * @param bool $recursive
     * @return string
     */
    public function createFolder($newFolderName, $parentFolderIdentifier = '', $recursive = false): string
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $folderIdentifier
     * @param string $newName
     * @return array<string, AbstractFile>
     */
    public function renameFolder($folderIdentifier, $newName): array
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $folderIdentifier
     * @param bool $deleteRecursively
     * @return bool
     */
    public function deleteFolder($folderIdentifier, $deleteRecursively = false): bool
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $fileIdentifier
     * @return bool
     * @throws \Exception
     */
    public function fileExists($fileIdentifier): bool
    {
        $identifier = $this->getIdentifier($fileIdentifier, FileType::FILE);
        if ($identifier === null) {
            throw new \Exception(sprintf('Identifier %s could not be found', $fileIdentifier));
        }
        return $this->getDriver()->exists($identifier, FileType::FILE);
    }

    /**
     * @param string $folderIdentifier
     * @return bool
     * @throws \Exception
     */
    public function folderExists($folderIdentifier): bool
    {
        $identifier = $this->getIdentifier($folderIdentifier, FileType::DIRECTORY);
        if ($identifier === null) {
            // root folder
            return true;
        }
        return $this->getDriver()->exists($identifier, FileType::DIRECTORY);
    }

    /**
     * @param string $folderIdentifier
     * @return bool
     * @throws \Exception
     */
    public function isFolderEmpty($folderIdentifier): bool
    {
        $identifier = $this->getIdentifier($folderIdentifier, FileType::DIRECTORY);
        if ($identifier === null) {
            throw new \Exception(sprintf('Identifier %s could not be found', $folderIdentifier));
        }
        return $this->getDriver()->listDirectory($identifier)->count() === 0;
    }

    /**
     * @param string $localFilePath
     * @param string $targetFolderIdentifier
     * @param string $newFileName
     * @param bool $removeOriginal
     * @return string
     */
    public function addFile($localFilePath, $targetFolderIdentifier, $newFileName = '', $removeOriginal = true): string
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $fileName
     * @param string $parentFolderIdentifier
     * @return string
     */
    public function createFile($fileName, $parentFolderIdentifier): string
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $fileIdentifier
     * @param string $targetFolderIdentifier
     * @param string $fileName
     * @return string
     */
    public function copyFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $fileName): string
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $fileIdentifier
     * @param string $newName
     * @return string
     */
    public function renameFile($fileIdentifier, $newName): string
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $fileIdentifier
     * @param string $localFilePath
     * @return bool
     */
    public function replaceFile($fileIdentifier, $localFilePath): bool
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $fileIdentifier
     * @return bool
     */
    public function deleteFile($fileIdentifier): bool
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $fileIdentifier
     * @param string $hashAlgorithm
     * @return string
     */
    public function hash($fileIdentifier, $hashAlgorithm): string
    {
        return hash($hashAlgorithm, $fileIdentifier);
    }

    /**
     * @param string $fileIdentifier
     * @param string $targetFolderIdentifier
     * @param string $newFileName
     * @return string
     */
    public function moveFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $newFileName): string
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $sourceFolderIdentifier
     * @param string $targetFolderIdentifier
     * @param string $newFolderName
     * @return array<string, string>
     */
    public function moveFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName): array
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $sourceFolderIdentifier
     * @param string $targetFolderIdentifier
     * @param string $newFolderName
     * @return bool
     */
    public function copyFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName): bool
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $fileIdentifier
     * @return string
     * @throws \Exception
     */
    public function getFileContents($fileIdentifier): string
    {
        $identifier = $this->getIdentifier($fileIdentifier, FileType::FILE);
        if ($identifier === null) {
            throw new \Exception(sprintf('Identifier %s not found', $identifier));
        }
        return $this->getDriver()->read($identifier);
    }

    /**
     * @param string $fileIdentifier
     * @param string $contents
     * @return int
     */
    public function setFileContents($fileIdentifier, $contents): int
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $fileName
     * @param string $folderIdentifier
     * @return bool
     */
    public function fileExistsInFolder($fileName, $folderIdentifier): bool
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $folderName
     * @param string $folderIdentifier
     * @return bool
     */
    public function folderExistsInFolder($folderName, $folderIdentifier): bool
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $fileIdentifier
     * @param bool $writable
     * @return string
     */
    public function getFileForLocalProcessing($fileIdentifier, $writable = true): string
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $identifier
     * @return array<string, bool>
     */
    public function getPermissions($identifier): array
    {
        return [
            'r' => true,
            'w' => false,
        ];
    }

    /**
     * @param string $identifier
     * @throws \Exception
     */
    public function dumpFileContents($identifier): void
    {
        $id = $this->getIdentifier($identifier, FileType::FILE);
        if ($id === null) {
            throw new \Exception(sprintf('Identifier %s not found', $identifier));
        }
        echo $this->getFileContents($id);
    }

    /**
     * @param string $folderIdentifier
     * @param string $identifier
     * @return bool
     */
    public function isWithin($folderIdentifier, $identifier): bool
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $fileIdentifier
     * @param array<string|int, mixed> $propertiesToExtract
     * @return array<string, string|int>
     */
    public function getFileInfoByIdentifier($fileIdentifier, array $propertiesToExtract = []): array
    {
        try {
            $prn = (string)(new PixelboxxResourceName($fileIdentifier));
        } catch (\Exception $exception) {
            $prn = $this->getIdentifier($fileIdentifier, FileType::FILE);
        }
        if ($prn === null) {
            throw new \Exception(sprintf('Identifier %s not found', $fileIdentifier));
        }
        $asset = $this->getDriver()->getFile($prn);
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

    /**
     * @param string $folderIdentifier
     * @return array<string, string|int>
     */
    public function getFolderInfoByIdentifier($folderIdentifier): array
    {
        $identifier = $this->getIdentifier($folderIdentifier, FileType::DIRECTORY);
        if ($identifier === null) {
            throw new \Exception(sprintf('Identifier %s not found', $folderIdentifier));
        }
        $directory = $this->getDriver()->getDirectory($identifier);
        if ($directory === null) {
            throw new \Exception(sprintf('Directory for Identifier %s not found', $identifier));
        }
        return [
            'identifier' => $directory->getIdentifier(),
            'name' => $directory->getFileName(),
            'mtime' => $directory->getMTime(),
            'ctime' => $directory->getCTime(),
            'storage' => $this->storageUid
        ];
    }

    /**
     * @param string $fileName
     * @param string $folderIdentifier
     * @return string
     */
    public function getFileInFolder($fileName, $folderIdentifier): string
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $folderIdentifier
     * @param int $start
     * @param int $numberOfItems
     * @param bool $recursive
     * @param array<string, callable> $filenameFilterCallbacks
     * @param string $sort
     * @param bool $sortRev
     * @return array<int, string>
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFilesInFolder($folderIdentifier, $start = 0, $numberOfItems = 0, $recursive = false, array $filenameFilterCallbacks = [], $sort = '', $sortRev = false): array
    {
        if (!$folderIdentifier) {
            return [];
        }
        $identifier = $this->getIdentifier($folderIdentifier, FileType::DIRECTORY);
        if ($identifier === null) {
            throw new \Exception(sprintf('Identifier %s not found', $identifier));
        }
        $folderWithAssets = $this->getDriver()->getClient()->folders()->getFolderAssets($identifier);
        if ($folderWithAssets === null) {
            return [];
        }
        $assets = [];
        foreach ($folderWithAssets->getFolder()->getAssets() as $asset) {
            $assets[] = (string)$asset->getId();
        }
        return $assets;
    }

    /**
     * @param string $folderName
     * @param string $folderIdentifier
     * @return string
     */
    public function getFolderInFolder($folderName, $folderIdentifier): string
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @param string $folderIdentifier
     * @param int $start
     * @param int $numberOfItems
     * @param bool $recursive
     * @param array<string, callable> $folderNameFilterCallbacks
     * @param string $sort
     * @param bool $sortRev
     * @return array<int, string>
     * @throws \Exception
     */
    public function getFoldersInFolder($folderIdentifier, $start = 0, $numberOfItems = 0, $recursive = false, array $folderNameFilterCallbacks = [], $sort = '', $sortRev = false): array
    {
        $folders = $this->getDriver()->listDirectory($this->getIdentifier($folderIdentifier, FileType::DIRECTORY), $recursive);
        $directories = [];
        /** @var Directory $folder */
        foreach ($folders as $folder) {
            $directories[] = $folder->getIdentifier();
        }
        return $directories;
    }

    /**
     * @param string $folderIdentifier
     * @param bool $recursive
     * @param array<string, callable> $filenameFilterCallbacks
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function countFilesInFolder($folderIdentifier, $recursive = false, array $filenameFilterCallbacks = []): int
    {
        if (!$folderIdentifier) {
            return 0;
        }
        $assets = $this->getDriver()->getClient()
            ->folders()
            ->getFolderAssets($this->getIdentifier($folderIdentifier, FileType::DIRECTORY));
        if ($assets === null) {
            return 0;
        }
        return count($assets->getFolder()->getAssets());
    }

    /**
     * @param string $folderIdentifier
     * @param bool $recursive
     * @param array<string, callable> $folderNameFilterCallbacks
     * @return int
     * @throws \Exception
     */
    public function countFoldersInFolder($folderIdentifier, $recursive = false, array $folderNameFilterCallbacks = []): int
    {
        // todo: recursive and filter is missing
        $directories = $this->getDriver()->listDirectory($this->getIdentifier($folderIdentifier, FileType::DIRECTORY));
        return $directories->count();
    }

    /**
     * @param string $identifier
     * @param array<string, string|int> $properties
     * @return ResponseInterface
     */
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
                'Last-Modified' => gmdate('D, d M Y H:i:s', (int)$fileInfo['mtime']) . ' GMT',
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
            return (string)PixelboxxResourceName::prnFromResource($this->getDriver()->getClient(), PixelboxxResourceName::FOLDER, $newIdentifier);
        }
        return (string)PixelboxxResourceName::prnFromResource($this->getDriver()->getClient(), PixelboxxResourceName::ASSET, $newIdentifier);
    }

    private function getDriver(): Driver
    {
        if ($this->driver === null) {
            throw new \Exception('Driver has not been initialized, did you all the initialize() method?');
        }
        return $this->driver;
    }
}
