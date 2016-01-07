<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Sales\Communication\Controller;

use Generated\Shared\Transfer\OrderListTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Sales\Business\SalesFacade;
use Spryker\Zed\Kernel\Communication\Controller\AbstractGatewayController;

/**
 * @method SalesFacade getFacade()
 */
class GatewayController extends AbstractGatewayController
{

    /**
     * @param OrderListTransfer $orderListTransfer
     *
     * @return OrderListTransfer
     */
    public function getOrdersAction(OrderListTransfer $orderListTransfer)
    {
        return $this->getFacade()->getOrders($orderListTransfer);
    }

    /**
     * @param OrderTransfer $orderTransfer
     *
     * @return OrderTransfer
     */
    public function getOrderDetailsAction(OrderTransfer $orderTransfer)
    {
        return $this->getFacade()->getOrderDetails($orderTransfer);
    }

}