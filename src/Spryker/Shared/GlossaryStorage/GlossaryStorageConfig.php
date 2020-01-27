<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\GlossaryStorage;

use Spryker\Shared\Kernel\AbstractBundleConfig;

class GlossaryStorageConfig extends AbstractBundleConfig
{
    /**
     * Defines queue name as used for processing translation messages.
     */
    public const SYNC_QUEUE_NAME = 'sync.storage.translation';

    /**
     * Defines resource name, that will be used for key generation.
     */
    public const RESOURCE_NAME = 'translation';

    /**
     * This events that will be used for key writing.
     */
    public const GLOSSARY_KEY_WRITE = 'Glossary.key.write';

    /**
     * This events that will be used for key deleting.
     */
    public const GLOSSARY_KEY_DELETE = 'Glossary.key.delete';

    /**
     * This events will be used for spy_glossary_key entity creation.
     */
    public const ENTITY_SPY_GLOSSARY_KEY_CREATE = 'Entity.spy_glossary_key.create';

    /**
     * This events will be used for spy_glossary_key entity changes.
     */
    public const ENTITY_SPY_GLOSSARY_KEY_UPDATE = 'Entity.spy_glossary_key.update';

    /**
     * This events will be used for spy_glossary_key entity deletion.
     */
    public const ENTITY_SPY_GLOSSARY_KEY_DELETE = 'Entity.spy_glossary_key.delete';

    /**
     * This events will be used for spy_glossary_translation entity creation
     */
    public const ENTITY_SPY_GLOSSARY_TRANSLATION_CREATE = 'Entity.spy_glossary_translation.create';

    /**
     * This events will be used for spy_glossary_translation entity changes
     */
    public const ENTITY_SPY_GLOSSARY_TRANSLATION_UPDATE = 'Entity.spy_glossary_translation.update';
}
