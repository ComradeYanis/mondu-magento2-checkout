<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

use Magento\Framework\Exception\LocalizedException;

class Adjust extends AbstractRequest implements RequestInterface
{
    /**
     * @inheritDoc
     */
    public function request($params)
    {
        $url = $this->configProvider->getApiUrl('orders') . '/' . $params['orderUid'] . '/adjust';

        unset($params['orderUid']);
        $resultJson = $this->sendRequestWithParams('post', $url, $this->serializer->serialize($params));

        if (!$resultJson) {
            throw new LocalizedException(__('something went wrong'));
        }

        return $this->serializer->unserialize($resultJson);
    }
}
