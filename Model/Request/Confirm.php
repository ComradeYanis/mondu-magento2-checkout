<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

use Magento\Framework\Exception\LocalizedException;

class Confirm extends AbstractRequest
{
    public const ORDER_STATE = ['pending', 'confirmed', 'authorized'];

    /**
     * @var bool
     */
    private $validate = true;

    /**
     * Request
     *
     * @param array $params
     * @return mixed
     * @throws LocalizedException
     */
    protected function request($params)
    {
        if (!$params['orderUid']) {
            throw new LocalizedException(__('Error placing an order'));
        }

        $url = $this->configProvider->getApiUrl('orders').'/'.$params['orderUid'];
        $resultJson = $this->sendRequestWithParams('get', $url);
        $result = $this->serializer->unserialize($resultJson);

        if ($this->validate && !in_array($result['order']['state'] ?? null, self::ORDER_STATE)) {
            throw new LocalizedException(__('Error placing an order'));
        }

        return $result;
    }

    /**
     * SetValidate ( will check if order state is in self::ORDER_STATE )
     *
     * @param bool $validate
     * @return $this
     */
    public function setValidate($validate): Confirm
    {
        $this->validate = $validate;
        return $this;
    }
}
