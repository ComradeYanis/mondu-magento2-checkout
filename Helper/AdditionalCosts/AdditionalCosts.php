<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);


namespace Mondu\Mondu\Helper\AdditionalCosts;

use Magento\Quote\Model\Quote;

class AdditionalCosts implements AdditionalCostsInterface
{
    /**
     * Returns additional costs associated with quote
     *
     * @param Quote $quote
     * @return int
     */
    public function getAdditionalCostsFromQuote(Quote $quote): int
    {
        if ($quote->getPaymentSurchargeAmount()) {
            return (int)round($quote->getPaymentSurchargeAmount(), 2) ?? 0;
        }
        return 0;
    }
}
