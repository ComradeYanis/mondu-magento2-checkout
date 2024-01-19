<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

use Magento\Framework\Exception\LocalizedException;

class Edit extends AbstractRequest
{
    /**
     * @var string
     */
    protected $uid;

    /**
     * Request
     *
     * @param array $params
     * @return mixed
     * @throws LocalizedException
     */
    protected function request($params)
    {
        if (!$this->uid) {
            throw new LocalizedException(__('No order uid provided to adjust the order'));
        }

        $url = $this->configProvider->getApiUrl('orders') . '/' . $this->uid . '/adjust';
        $resultJson = $this->sendRequestWithParams('post', $url, $this->serializer->serialize($params));

        if (!$resultJson) {
            throw new LocalizedException(__('Mondu: something went wrong'));
        }

        return $this->serializer->unserialize($resultJson);
    }

    /**
     * Sets order uid ( used before sending the request )
     *
     * @param string $uid
     * @return $this
     */
    public function setOrderUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }
}
