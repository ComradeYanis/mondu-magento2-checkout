<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

namespace Mondu\Mondu\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;

interface MonduConfigProviderInterface extends ConfigProviderInterface
{

    /**
     *
     * @param string $status
     * @return void
     */
    public function updateOrderStatus(string $status): void;

    /**
     * True if any Mondu Payment method is active
     *
     * @return bool
     */
    public function isActive(): bool;
}
