<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\GlossaryStorage\Persistence;

use Generated\Shared\Transfer\SpyGlossaryStorageEntityTransfer;
use Orm\Zed\GlossaryStorage\Persistence\SpyGlossaryStorage;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;
use Spryker\Zed\Propel\Persistence\BatchProcessor\ActiveRecordBatchProcessorTrait;

/**
 * @method \Spryker\Zed\GlossaryStorage\Persistence\GlossaryStoragePersistenceFactory getFactory()
 */
class GlossaryStorageEntityManager extends AbstractEntityManager implements GlossaryStorageEntityManagerInterface
{
    use ActiveRecordBatchProcessorTrait;

    /**
     * @param array<\Generated\Shared\Transfer\SpyGlossaryStorageEntityTransfer> $glossaryStorageEntityTransfers
     *
     * @return void
     */
    public function saveGlossaryStorageEntities(array $glossaryStorageEntityTransfers): void
    {
        $glossaryStorageEntityMap = $this->getGlossaryStorageEntityMap($glossaryStorageEntityTransfers);
        $mapper = $this->getFactory()->createGlossaryStorageMapper();

        foreach ($glossaryStorageEntityTransfers as $glossaryStorageEntityTransfer) {
            $glossaryStorageEntityTransfer->requireFkGlossaryKey();

            $fkGlossaryKey = $glossaryStorageEntityTransfer->getFkGlossaryKeyOrFail();
            $locale = $glossaryStorageEntityTransfer->getLocale() ?? '';

            $glossaryStorage = $glossaryStorageEntityMap[$fkGlossaryKey][$locale] ?? new SpyGlossaryStorage();
            $glossaryStorage = $mapper->hydrateSpyGlossaryStorageEntity($glossaryStorage, $glossaryStorageEntityTransfer);

            $this->persist($glossaryStorage);
        }

        $this->commit();
    }

    /**
     * @param array<\Generated\Shared\Transfer\SpyGlossaryStorageEntityTransfer> $glossaryStorageEntityTransfers
     *
     * @return array<int, array<string, \Orm\Zed\GlossaryStorage\Persistence\SpyGlossaryStorage>>
     */
    protected function getGlossaryStorageEntityMap(array $glossaryStorageEntityTransfers): array
    {
        $fkGlossaryKeys = array_map(
            static fn (SpyGlossaryStorageEntityTransfer $transfer) => $transfer->getFkGlossaryKeyOrFail(),
            $glossaryStorageEntityTransfers,
        );

        $glossaryStorageEntities = $this->getFactory()
            ->createGlossaryStorageQuery()
            ->filterByFkGlossaryKey_In($fkGlossaryKeys)
            ->find();

        $glossaryStorageEntityMap = [];
        foreach ($glossaryStorageEntities as $glossaryStorageEntity) {
            $glossaryStorageEntityMap[$glossaryStorageEntity->getFkGlossaryKey()][$glossaryStorageEntity->getLocale()] = $glossaryStorageEntity;
        }

        return $glossaryStorageEntityMap;
    }

    public function deleteGlossaryStorageEntity(int $idGlossaryStorage): void
    {
        $glossaryStorage = $this->getFactory()
            ->createGlossaryStorageQuery()
            ->filterByIdGlossaryStorage($idGlossaryStorage)
            ->findOne();

        if ($glossaryStorage) {
            $glossaryStorage->delete();
        }
    }
}
