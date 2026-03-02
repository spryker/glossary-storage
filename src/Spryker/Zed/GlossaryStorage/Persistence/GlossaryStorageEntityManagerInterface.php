<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\GlossaryStorage\Persistence;

interface GlossaryStorageEntityManagerInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\SpyGlossaryStorageEntityTransfer> $glossaryStorageEntityTransfers
     *
     * @return void
     */
    public function saveGlossaryStorageEntities(array $glossaryStorageEntityTransfers): void;

    public function deleteGlossaryStorageEntity(int $idGlossaryStorage): void;
}
