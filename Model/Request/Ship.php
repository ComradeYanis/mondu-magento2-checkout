<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);


namespace Mondu\Mondu\Model\Request;

use Magento\Framework\HTTP\Client\Curl;
use Mondu\Mondu\Model\Config\MonduConfigProvider;

class Ship extends CommonRequest implements RequestInterface
{
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var MonduConfigProvider
     */
    protected $configProvider;

    /**
     * @param Curl $curl
     * @param MonduConfigProvider $configProvider
     */
    public function __construct(
        Curl $curl,
        MonduConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
        $this->curl = $curl;
    }

    /**
     * @inheritdoc
     */
    public function request($params)
    {
        $url = $this->configProvider->getApiUrl('orders').'/' . $params['order_uid'] . '/invoices';
        unset($params['orderUid']);

        $resultJson = $this->sendRequestWithParams('post', $url, json_encode($params));

        if ($resultJson) {
            $result = json_decode($resultJson, true);
        }

        return $result ?? null;
    }
}
