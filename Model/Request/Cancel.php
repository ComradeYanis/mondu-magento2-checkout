<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

class Cancel extends AbstractRequest implements RequestInterface
{
    /**
     * @inheritdoc
     */
    public function request($params)
    {
        $url = $this->configProvider->getApiUrl('orders').'/'.$params['orderUid'].'/cancel';

        unset($params['orderUid']);
        $resultJson = $this->sendRequestWithParams('post', $url, $this->serializer->serialize([]));

        if ($resultJson) {
            $result = $this->serializer->unserialize($resultJson);
        }

        return $result ?? null;
    }
}
