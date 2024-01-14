<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);


namespace Mondu\Mondu\Helper;

use Magento\Sales\Model\Order;
use Mondu\Mondu\Model\Config\MonduConfigProvider;

class ContextHelper
{
    /**
     * @var MonduConfigProvider
     */
    private $configProvider;

    /**
     * @param MonduConfigProvider $configProvider
     */
    public function __construct(
        MonduConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     *
     * @todo REMOVE
     * Sets context depending on store of the order
     *
     * @param Order $order
     * @return void
     */
    public function setConfigContextForOrder($order)
    {
        $this->configProvider->setContextCode($order->getStore()->getId());
    }
}
