<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);


namespace Mondu\Mondu\Helper\AdditionalCosts;

use Magento\Quote\Model\Quote;

/**
 *     Map this interface onto your custom class if you have additional costs attached to payment methods ( in di.xml )
 *     also make sure your module is loaded after Mondu
 *     <preference for="Mondu\Mondu\Helper\AdditionalCosts\AdditionalCostsInterface"
 *                 type="My\Module\Mondu\AdditionalCosts" />
 */

interface AdditionalCostsInterface
{
    /**
     * Returns additional costs associated with quote
     *
     * @param Quote $quote
     * @return int
     */
    public function getAdditionalCostsFromQuote(Quote $quote): int;
}
