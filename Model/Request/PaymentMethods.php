<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

use Magento\Framework\Exception\LocalizedException;

class PaymentMethods extends AbstractRequest implements RequestInterface
{
    /**
     * @inheritdoc
     */
    public function request($params = null)
    {
        $url = $this->configProvider->getApiUrl('payment_methods');
        $resultJson = $this->sendRequestWithParams('get', $url);

        if (!$resultJson) {
            throw new LocalizedException(__('something went wrong'));
        }

        $result = $this->serializer->unserialize($resultJson);

        return $result['payment_methods'] ?? null;
    }
}
