<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

class Ship extends AbstractRequest implements RequestInterface
{
    /**
     * @inheritdoc
     */
    public function request($params)
    {
        $url = $this->configProvider->getApiUrl('orders').'/' . $params['order_uid'] . '/invoices';
        unset($params['orderUid']);

        $resultJson = $this->sendRequestWithParams('post', $url, $this->serializer->serialize($params));

        if ($resultJson) {
            $result = $this->serializer->unserialize($resultJson);
        }

        return $result ?? null;
    }
}
