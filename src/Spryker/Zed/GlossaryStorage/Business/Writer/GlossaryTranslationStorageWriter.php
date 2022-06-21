<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\GlossaryStorage\Business\Writer;

use Generated\Shared\Transfer\SpyGlossaryStorageEntityTransfer;
use Generated\Shared\Transfer\SpyGlossaryTranslationEntityTransfer;
use Spryker\Zed\GlossaryStorage\Business\Mapper\GlossaryTranslationStorageMapperInterface;
use Spryker\Zed\GlossaryStorage\Dependency\Facade\GlossaryStorageToEventBehaviorFacadeInterface;
use Spryker\Zed\GlossaryStorage\Persistence\GlossaryStorageEntityManagerInterface;
use Spryker\Zed\GlossaryStorage\Persistence\GlossaryStorageRepositoryInterface;

class GlossaryTranslationStorageWriter implements GlossaryTranslationStorageWriterInterface
{
    /**
     * @uses \Orm\Zed\Glossary\Persistence\Map\SpyGlossaryTranslationTableMap::COL_FK_GLOSSARY_KEY
     *
     * @var string
     */
    protected const COL_FK_GLOSSARY_KEY = 'spy_glossary_translation.fk_glossary_key';

    /**
     * @var string
     */
    protected const TRANSLATION_VALUE_ZERO = '0';

    /**
     * @var \Spryker\Zed\GlossaryStorage\Dependency\Facade\GlossaryStorageToEventBehaviorFacadeInterface
     */
    protected $eventBehaviorFacade;

    /**
     * @var \Spryker\Zed\GlossaryStorage\Persistence\GlossaryStorageRepositoryInterface
     */
    protected $glossaryStorageRepository;

    /**
     * @var \Spryker\Zed\GlossaryStorage\Persistence\GlossaryStorageEntityManagerInterface
     */
    protected $glossaryStorageEntityManager;

    /**
     * @var \Spryker\Zed\GlossaryStorage\Business\Mapper\GlossaryTranslationStorageMapperInterface
     */
    protected $glossaryTranslationStorageMapper;

    /**
     * @param \Spryker\Zed\GlossaryStorage\Dependency\Facade\GlossaryStorageToEventBehaviorFacadeInterface $eventBehaviorFacade
     * @param \Spryker\Zed\GlossaryStorage\Persistence\GlossaryStorageRepositoryInterface $glossaryStorageRepository
     * @param \Spryker\Zed\GlossaryStorage\Persistence\GlossaryStorageEntityManagerInterface $glossaryStorageEntityManager
     * @param \Spryker\Zed\GlossaryStorage\Business\Mapper\GlossaryTranslationStorageMapperInterface $glossaryTranslationStorageMapper
     */
    public function __construct(
        GlossaryStorageToEventBehaviorFacadeInterface $eventBehaviorFacade,
        GlossaryStorageRepositoryInterface $glossaryStorageRepository,
        GlossaryStorageEntityManagerInterface $glossaryStorageEntityManager,
        GlossaryTranslationStorageMapperInterface $glossaryTranslationStorageMapper
    ) {
        $this->eventBehaviorFacade = $eventBehaviorFacade;
        $this->glossaryStorageRepository = $glossaryStorageRepository;
        $this->glossaryStorageEntityManager = $glossaryStorageEntityManager;
        $this->glossaryTranslationStorageMapper = $glossaryTranslationStorageMapper;
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\GlossaryStorage\Business\Writer\GlossaryTranslationStorageWriter::writeGlossaryStorageCollectionByGlossaryKeyEvents()} instead.
     *
     * @param array<int> $glossaryKeyIds
     *
     * @return void
     */
    public function publish(array $glossaryKeyIds)
    {
        $this->writerGlossaryStorageCollection($glossaryKeyIds);
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     *
     * @return void
     */
    public function writeGlossaryStorageCollectionByGlossaryKeyEvents(array $eventTransfers): void
    {
        $glossaryKeyIds = $this->eventBehaviorFacade->getEventTransferIds($eventTransfers);

        $this->writerGlossaryStorageCollection($glossaryKeyIds);
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     *
     * @return void
     */
    public function writeGlossaryStorageCollectionByGlossaryTranslationEvents(array $eventTransfers): void
    {
        $glossaryKeyIds = $this->eventBehaviorFacade->getEventTransferForeignKeys($eventTransfers, static::COL_FK_GLOSSARY_KEY);

        $this->writerGlossaryStorageCollection($glossaryKeyIds);
    }

    /**
     * @param array<int> $glossaryKeyIds
     *
     * @return void
     */
    protected function writerGlossaryStorageCollection(array $glossaryKeyIds): void
    {
        $glossaryTranslationEntityTransfers = $this->glossaryStorageRepository->findGlossaryTranslationEntityTransfer($glossaryKeyIds);
        $glossaryStorageEntityTransfers = $this->glossaryStorageRepository->findGlossaryStorageEntityTransfer($glossaryKeyIds);
        $mappedGlossaryStorageEntityTransfers = $this->glossaryTranslationStorageMapper->mapGlossaryStorageEntityTransferByGlossaryIdAndLocale($glossaryStorageEntityTransfers);

        [$glossaryStorageInactiveEntityTransfer, $glossaryTranslationEntityTransfers] = $this
            ->filterInactiveAndEmptyLocalizedStorageEntityTransfers(
                $glossaryTranslationEntityTransfers,
                $mappedGlossaryStorageEntityTransfers,
            );

        /** @var \Generated\Shared\Transfer\SpyGlossaryStorageEntityTransfer $glossaryStorageInactiveEntity */
        foreach ($glossaryStorageInactiveEntityTransfer as $glossaryStorageInactiveEntity) {
            $this->glossaryStorageEntityManager->deleteGlossaryStorageEntity((int)$glossaryStorageInactiveEntity->getIdGlossaryStorage());
        }

        $this->storeData($glossaryTranslationEntityTransfers, $mappedGlossaryStorageEntityTransfers);
    }

    /**
     * @param array<\Generated\Shared\Transfer\SpyGlossaryTranslationEntityTransfer> $glossaryTranslationEntityTransfers
     * @param array $mappedGlossaryStorageEntityTransfers
     *
     * @return array
     */
    protected function filterInactiveAndEmptyLocalizedStorageEntityTransfers(
        array $glossaryTranslationEntityTransfers,
        array $mappedGlossaryStorageEntityTransfers
    ): array {
        $glossaryStorageEntityTransfers = [];
        foreach ($glossaryTranslationEntityTransfers as $id => $glossaryTranslationEntityTransfer) {
            $idGlossaryKey = $glossaryTranslationEntityTransfer->getFkGlossaryKey();
            $localeName = $glossaryTranslationEntityTransfer->getLocale()->getLocaleName();

            if (!$this->isGlossaryTranslationActive($glossaryTranslationEntityTransfer) || !$this->isTranslationValueValid($glossaryTranslationEntityTransfer)) {
                unset($glossaryTranslationEntityTransfers[$id]);

                if (isset($mappedGlossaryStorageEntityTransfers[$idGlossaryKey][$localeName])) {
                    $glossaryStorageEntityTransfers[] = $mappedGlossaryStorageEntityTransfers[$idGlossaryKey][$localeName];
                }
            }
        }

        return [$glossaryStorageEntityTransfers, $glossaryTranslationEntityTransfers];
    }

    /**
     * @param array<\Generated\Shared\Transfer\SpyGlossaryTranslationEntityTransfer> $glossaryTranslationEntityTransfers
     * @param array $mappedGlossaryStorageEntityTransfers
     *
     * @return void
     */
    protected function storeData(array $glossaryTranslationEntityTransfers, array $mappedGlossaryStorageEntityTransfers)
    {
        $glossaryStorageEntityTransfers = [];
        foreach ($glossaryTranslationEntityTransfers as $id => $glossaryTranslationEntityTransfer) {
            $idGlossaryKey = $glossaryTranslationEntityTransfer->getFkGlossaryKey();
            $localeName = $glossaryTranslationEntityTransfer->getLocale()->getLocaleName();
            if (isset($mappedGlossaryStorageEntityTransfers[$idGlossaryKey][$localeName])) {
                $glossaryStorageEntityTransfers[] = $this->storeDataSet($glossaryTranslationEntityTransfer, $mappedGlossaryStorageEntityTransfers[$idGlossaryKey][$localeName]);

                continue;
            }

            $glossaryStorageEntityTransfers[] = $this->storeDataSet($glossaryTranslationEntityTransfer);
        }

        $this->glossaryStorageEntityManager->saveGlossaryStorageEntities($glossaryStorageEntityTransfers);
    }

    /**
     * @param \Generated\Shared\Transfer\SpyGlossaryTranslationEntityTransfer $glossaryTranslationEntityTransfer
     * @param \Generated\Shared\Transfer\SpyGlossaryStorageEntityTransfer|null $glossaryStorageEntityTransfer
     *
     * @return \Generated\Shared\Transfer\SpyGlossaryStorageEntityTransfer
     */
    protected function storeDataSet(
        SpyGlossaryTranslationEntityTransfer $glossaryTranslationEntityTransfer,
        ?SpyGlossaryStorageEntityTransfer $glossaryStorageEntityTransfer = null
    ) {
        if ($glossaryStorageEntityTransfer === null) {
            $glossaryStorageEntityTransfer = new SpyGlossaryStorageEntityTransfer();
        }

        $glossaryStorageEntityTransfer->setFkGlossaryKey($glossaryTranslationEntityTransfer->getFkGlossaryKey());
        $glossaryStorageEntityTransfer->setGlossaryKey($glossaryTranslationEntityTransfer->getGlossaryKey()->getKey());
        $glossaryStorageEntityTransfer->setLocale($glossaryTranslationEntityTransfer->getLocale()->getLocaleName());

        /*
         * This line added to keep the glossary data structure in backward compatible and
         * will be removed in the next major version.
         */
        $data = $this->makeGlossaryDataBackwardCompatible($glossaryTranslationEntityTransfer->modifiedToArray());
        $glossaryStorageEntityTransfer->setData(json_encode($data) ?: null);

        return $glossaryStorageEntityTransfer;
    }

    /**
     * @deprecated This method was added to keep the glossary data structure backward compatible and
     * will be removed in the next major version.
     *
     * @param array<string, mixed> $data
     *
     * @return array
     */
    protected function makeGlossaryDataBackwardCompatible(array $data): array
    {
        $data['GlossaryKey'] = $data['glossary_key'];
        $data['Locale'] = $data['locale'];

        return $data;
    }

    /**
     * @param \Generated\Shared\Transfer\SpyGlossaryTranslationEntityTransfer $glossaryTranslationEntityTransfer
     *
     * @return bool
     */
    protected function isGlossaryTranslationActive(SpyGlossaryTranslationEntityTransfer $glossaryTranslationEntityTransfer): bool
    {
        return $glossaryTranslationEntityTransfer->getIsActive() && $glossaryTranslationEntityTransfer->getGlossaryKeyOrFail()->getIsActive();
    }

    /**
     * @param \Generated\Shared\Transfer\SpyGlossaryTranslationEntityTransfer $glossaryTranslationEntityTransfer
     *
     * @return bool
     */
    protected function isTranslationValueValid(SpyGlossaryTranslationEntityTransfer $glossaryTranslationEntityTransfer): bool
    {
        return $glossaryTranslationEntityTransfer->getValue() || $glossaryTranslationEntityTransfer->getValue() === static::TRANSLATION_VALUE_ZERO;
    }
}
