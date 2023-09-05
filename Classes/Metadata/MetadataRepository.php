<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Metadata;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\RootLevelRestriction;
use TYPO3\CMS\Core\Resource\Event\AfterFileMetaDataUpdatedEvent;
use TYPO3\CMS\Core\Resource\Event\EnrichFileMetaDataEvent;
use TYPO3\CMS\Core\Resource\Index\MetaDataRepository as Typo3MetaDataRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MetadataRepository extends Typo3MetaDataRepository
{
    /**
     * @param int $uid
     * @param int $languageUid
     * @return array<mixed>
     */
    public function findByFileUidAndLanguageUid(int $uid, int $languageUid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->tableName);
        $queryBuilder
            ->getRestrictions()
            ->add(GeneralUtility::makeInstance(RootLevelRestriction::class))
        ;

        $statement = $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->eq(
                    'file',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($languageUid, Connection::PARAM_INT)
                )
            )
            ->execute();

        if (is_int($statement)) {
            return [];
        }

        $record = null;
        if (method_exists($statement, 'fetchAllAssociative')) {
            $record = $statement->fetchAllAssociative();
        } elseif (method_exists($statement, 'fetch')) {
            $record = $statement->fetch(FetchMode::ASSOCIATIVE);
        }
        if (empty($record)) {
            return [];
        }
        /** @var array{uid: int} $result */
        $result = $record;

        /** @var EnrichFileMetaDataEvent $dispatched */
        $dispatched = $this->eventDispatcher->dispatch(new EnrichFileMetaDataEvent($uid, (int)$result['uid'], $result));
        return $dispatched->getRecord();
    }

    /**
     * Updates the metadata record in the database
     *
     * @param int $fileUid the file uid to update
     * @param array<mixed> $data Data to update
     * @internal
     */
    public function updateByFileUidAndLanguageUid(int $fileUid, int $languageUid, array $data): void
    {
        $updateRow = array_intersect_key($data, $this->getTableFields());
        if (array_key_exists('uid', $updateRow)) {
            unset($updateRow['uid']);
        }
        $row = $this->findByFileUidAndLanguageUid($fileUid, $languageUid);
        if (!empty($updateRow)) {
            $updateRow['tstamp'] = time();
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->tableName);
            $types = [];
            if ($connection->getDatabasePlatform() instanceof SQLServerPlatform) {
                // mssql needs to set proper PARAM_LOB and others to update fields
                $tableDetails = $connection->getSchemaManager()->listTableDetails($this->tableName);
                foreach ($updateRow as $columnName => $columnValue) {
                    $types[$columnName] = $tableDetails->getColumn($columnName)->getType()->getBindingType();
                }
            }
            $connection->update(
                $this->tableName,
                $updateRow,
                [
                    'uid' => (int)$row['uid']
                ],
                $types
            );

            // $this->eventDispatcher->dispatch(new AfterFileMetaDataUpdatedEvent($fileUid, (int)$row['uid'], array_merge($row, $updateRow)));
        }
    }
}
