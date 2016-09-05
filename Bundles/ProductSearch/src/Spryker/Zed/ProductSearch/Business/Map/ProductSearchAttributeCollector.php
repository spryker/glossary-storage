<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductSearch\Business\Map;

use Generated\Shared\Transfer\ProductSearchAttributeMapTransfer;
use Spryker\Zed\ProductSearch\Business\Attribute\AttributeReaderInterface;
use Spryker\Zed\ProductSearch\Business\Exception\InvalidFilterTypeException;
use Spryker\Zed\ProductSearch\ProductSearchConfig;

class ProductSearchAttributeCollector implements ProductSearchAttributeMapCollectorInterface
{

    /**
     * @var \Spryker\Zed\ProductSearch\Business\Attribute\AttributeReaderInterface
     */
    protected $attributeReader;

    /**
     * @var \Spryker\Zed\ProductSearch\ProductSearchConfig
     */
    protected $productSearchConfig;

    /**
     * @param \Spryker\Zed\ProductSearch\Business\Attribute\AttributeReaderInterface $attributeReader
     * @param \Spryker\Zed\ProductSearch\ProductSearchConfig $productSearchConfig
     */
    public function __construct(AttributeReaderInterface $attributeReader, ProductSearchConfig $productSearchConfig)
    {
        $this->attributeReader = $attributeReader;
        $this->productSearchConfig = $productSearchConfig;
    }

    /**
     * @throws \Spryker\Zed\ProductSearch\Business\Exception\InvalidFilterTypeException
     *
     * @return \Generated\Shared\Transfer\ProductSearchAttributeMapTransfer[]
     */
    public function getProductSearchAttributeMap()
    {
        $result = [];
        $filterTypeConfigs = $this->productSearchConfig->getFilterTypeConfigs();

        foreach ($this->attributeReader->getAttributeList() as $productSearchAttributeTransfer) {
            if (!isset($filterTypeConfigs[$productSearchAttributeTransfer->getFilterType()])) {
                throw new InvalidFilterTypeException(sprintf(
                    'Invalid filter type "%s"! Available options are [%s].',
                    $productSearchAttributeTransfer->getFilterType(),
                    implode(', ', array_keys($filterTypeConfigs))
                ));
            }

            $targetField = $filterTypeConfigs[$productSearchAttributeTransfer->getFilterType()]->getFieldName();

            $result[] = (new ProductSearchAttributeMapTransfer())
                ->setAttributeName($productSearchAttributeTransfer->getKey())
                ->setTargetFields([$targetField]);
        }

        return $result;
    }

    /**
     * @param array $attributeMaps
     *
     * @return \Generated\Shared\Transfer\ProductSearchAttributeMapTransfer[]
     */
    protected function processRawMap(array $attributeMaps)
    {
        $result = [];

        foreach ($attributeMaps as $attributeName => $targetFields) {
            $result[] = (new ProductSearchAttributeMapTransfer())
                ->setAttributeName($attributeName)
                ->setTargetFields($targetFields);
        }

        return $result;
    }

}
