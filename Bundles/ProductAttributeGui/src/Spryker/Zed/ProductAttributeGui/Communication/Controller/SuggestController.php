<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductAttributeGui\Communication\Controller;

use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\ProductAttributeGui\Business\ProductAttributeGuiFacade getFacade()
 * @method \Spryker\Zed\ProductAttributeGui\Communication\ProductAttributeGuiCommunicationFactory getFactory()
 */
class SuggestController extends AbstractController
{

    const PARAM_ID_PRODUCT_ABSTRACT = 'id-product-abstract';
    const PARAM_ID = 'id';
    const PARAM_SEARCH_TEXT = 'q';
    const PARAM_TERM = 'term';
    const PARAM_LOCALE_CODE = 'locale_code';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function keysAction(Request $request)
    {
        $searchTerm = $request->query->get(self::PARAM_TERM);
        $keys = $this->getFacade()->suggestProductAttributeKeys($searchTerm);

        return $this->jsonResponse($keys);
    }

}
