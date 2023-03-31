<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\GlossaryStorage\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\EventEntityTransfer;
use Generated\Shared\Transfer\KeyTranslationTransfer;
use Generated\Shared\Transfer\SpyGlossaryTranslationEntityTransfer;
use Orm\Zed\Glossary\Persistence\Map\SpyGlossaryTranslationTableMap;
use Orm\Zed\Glossary\Persistence\SpyGlossaryKey;
use Orm\Zed\Glossary\Persistence\SpyGlossaryKeyQuery;
use Orm\Zed\Glossary\Persistence\SpyGlossaryTranslationQuery;
use Orm\Zed\GlossaryStorage\Persistence\SpyGlossaryStorageQuery;
use Spryker\Client\Kernel\Container;
use Spryker\Client\Queue\QueueDependencyProvider;
use Spryker\Zed\GlossaryStorage\Business\GlossaryStorageBusinessFactory;
use Spryker\Zed\GlossaryStorage\Business\GlossaryStorageFacade;
use SprykerTest\Zed\GlossaryStorage\GlossaryStorageConfigMock;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group GlossaryStorage
 * @group Business
 * @group Facade
 * @group GlossaryStorageFacadeTest
 * Add your own group annotations below this line
 */
class GlossaryStorageFacadeTest extends Unit
{
    /**
     * @var int
     */
    public const ID_GLOSSARY = 1;

    /**
     * @var string
     */
    public const LOCALE_EN_US = 'en_US';

    /**
     * @var string
     */
    protected const TRANSLATION_VALUE_ZERO = '0';

    /**
     * @var \SprykerTest\Zed\GlossaryStorage\GlossaryStorageCommunicationTester
     */
    protected $tester;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->setDependency(QueueDependencyProvider::QUEUE_ADAPTERS, function (Container $container) {
            return [
                $container->getLocator()->rabbitMq()->client()->createQueueAdapter(),
            ];
        });
    }

    /**
     * @return void
     */
    public function testWriteCollectionByGlossaryTranslationEvents(): void
    {
        // Assign
        $this->cleanUpGlossaryStorage(static::ID_GLOSSARY);
        $glossaryStorageCount = $this->createGlossaryStorageQuery()->filterByFkGlossaryKey(static::ID_GLOSSARY)->count();

        $eventTransfers = [
            (new EventEntityTransfer())->setForeignKeys([
                SpyGlossaryTranslationTableMap::COL_FK_GLOSSARY_KEY => static::ID_GLOSSARY,
            ]),
        ];

        // Act
        $this->getGlossaryStorageFacade()->writeCollectionByGlossaryTranslationEvents($eventTransfers);

        // Assert
        $this->assertGlossaryStorage(static::ID_GLOSSARY, $glossaryStorageCount);
    }

    /**
     * @return void
     */
    public function testWriteCollectionByGlossaryTranslationEventsPublishesZeroStringValueCorrectly(): void
    {
        // Arrange
        $localeTransfers = $this->tester->getLocator()->locale()->facade()->getLocaleCollection();

        $seedData = [];
        foreach ($localeTransfers as $localeTransfer) {
            $seedData[KeyTranslationTransfer::LOCALES][$localeTransfer->getLocaleName()] = static::TRANSLATION_VALUE_ZERO;
        }
        $idGlossaryKey = $this->tester->haveTranslation($seedData);

        $this->cleanUpGlossaryStorage($idGlossaryKey);
        $glossaryStorageEntitiesCount = $this->createGlossaryStorageQuery()->filterByFkGlossaryKey($idGlossaryKey)->count();

        $eventEntityTransfers = [
            (new EventEntityTransfer())->setForeignKeys([
                SpyGlossaryTranslationTableMap::COL_FK_GLOSSARY_KEY => $idGlossaryKey,
            ]),
        ];

        // Act
        $this->getGlossaryStorageFacade()->writeCollectionByGlossaryTranslationEvents($eventEntityTransfers);

        // Assert
        $this->assertGlossaryStorage($idGlossaryKey, $glossaryStorageEntitiesCount);
    }

    /**
     * @return void
     */
    public function testDeleteCollectionByGlossaryKeyEvents(): void
    {
        // Assign
        $this->cleanUpGlossaryStorage(static::ID_GLOSSARY);
        $glossaryStorageCount = $this->createGlossaryStorageQuery()->filterByFkGlossaryKey(static::ID_GLOSSARY)->count();
        $this->assertSame(0, $glossaryStorageCount);

        $eventTransfers = [
            (new EventEntityTransfer())->setId(static::ID_GLOSSARY),
        ];

        // Act
        $this->getGlossaryStorageFacade()->writeCollectionByGlossaryKeyEvents($eventTransfers);
        $this->getGlossaryStorageFacade()->deleteCollectionByGlossaryKeyEvents($eventTransfers);

        // Assert
        $glossaryStorageCountAfterDeleteFacadeCall = $this->createGlossaryStorageQuery()->filterByFkGlossaryKey(static::ID_GLOSSARY)->count();
        $this->assertSame($glossaryStorageCountAfterDeleteFacadeCall, 0);
    }

    /**
     * @return void
     */
    public function testWriteCollectionByGlossaryKeyEvents(): void
    {
        // Assign
        $this->cleanUpGlossaryStorage(static::ID_GLOSSARY);
        $glossaryStorageCount = $this->createGlossaryStorageQuery()->filterByFkGlossaryKey(static::ID_GLOSSARY)->count();

        $eventTransfers = [
            (new EventEntityTransfer())->setId(static::ID_GLOSSARY),
        ];

        // Act
        $this->getGlossaryStorageFacade()->writeCollectionByGlossaryKeyEvents($eventTransfers);

        // Assert
        $this->assertGlossaryStorage(static::ID_GLOSSARY, $glossaryStorageCount);
    }

    /**
     * @param int $idGlossaryKey
     *
     * @return void
     */
    protected function cleanUpGlossaryStorage(int $idGlossaryKey): void
    {
        SpyGlossaryStorageQuery::create()->filterByFkGlossaryKey($idGlossaryKey)->delete();
    }

    /**
     * @param int $idGlossaryKey
     * @param int $beforeCount
     *
     * @return void
     */
    protected function assertGlossaryStorage(int $idGlossaryKey, int $beforeCount): void
    {
        $glossaryStorageCount = $this->createGlossaryStorageQuery()->filterByFkGlossaryKey($idGlossaryKey)->count();
        $this->assertGreaterThan($beforeCount, $glossaryStorageCount);
        $spyGlossaryStorage = $this->createGlossaryStorageQuery()
            ->orderByFkGlossaryKey()
            ->filterByLocale(static::LOCALE_EN_US)
            ->findOneByFkGlossaryKey($idGlossaryKey);

        $glossaryKeyEntity = $this->findGlossaryKeyEntity($idGlossaryKey);
        $glossaryTranslationEntityTransfer = new SpyGlossaryTranslationEntityTransfer();
        $glossaryTranslationEntityTransfer->fromArray($spyGlossaryStorage->getData(), true);

        $this->assertNotNull($spyGlossaryStorage);
        $this->assertSame($glossaryKeyEntity->getKey(), $glossaryTranslationEntityTransfer->getGlossaryKey()->getKey());
        $this->assertSame($glossaryKeyEntity->getSpyGlossaryTranslations()->getFirst()->getValue(), $glossaryTranslationEntityTransfer->getValue());
    }

    /**
     * @return \Orm\Zed\Glossary\Persistence\SpyGlossaryTranslationQuery
     */
    protected function createGlossaryTranslationQuery(): SpyGlossaryTranslationQuery
    {
        return SpyGlossaryTranslationQuery::create();
    }

    /**
     * @return \Orm\Zed\GlossaryStorage\Persistence\SpyGlossaryStorageQuery
     */
    protected function createGlossaryStorageQuery(): SpyGlossaryStorageQuery
    {
        return SpyGlossaryStorageQuery::create();
    }

    /**
     * @return \Orm\Zed\Glossary\Persistence\SpyGlossaryKeyQuery
     */
    protected function createGlossaryKeyQuery(): SpyGlossaryKeyQuery
    {
        return SpyGlossaryKeyQuery::create();
    }

    /**
     * @return \Spryker\Zed\GlossaryStorage\Business\GlossaryStorageFacade
     */
    protected function getGlossaryStorageFacade(): GlossaryStorageFacade
    {
        $factory = new GlossaryStorageBusinessFactory();
        $factory->setConfig(new GlossaryStorageConfigMock());

        $facade = new GlossaryStorageFacade();
        $facade->setFactory($factory);

        return $facade;
    }

    /**
     * @param int|null $idGlossaryKey
     *
     * @return \Orm\Zed\Glossary\Persistence\SpyGlossaryKey|null
     */
    protected function findGlossaryKeyEntity(?int $idGlossaryKey = null): ?SpyGlossaryKey
    {
        if ($idGlossaryKey === null) {
            $idGlossaryKey = static::ID_GLOSSARY;
        }

        return $this->createGlossaryKeyQuery()
            ->joinWithSpyGlossaryTranslation()
                ->useSpyGlossaryTranslationQuery()
                    ->useLocaleQuery()
                    ->filterByLocaleName(static::LOCALE_EN_US)
                    ->endUse()
                ->endUse()
            ->findByIdGlossaryKey($idGlossaryKey)
            ->getFirst();
    }
}
