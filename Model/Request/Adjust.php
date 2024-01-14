<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);


namespace Mondu\Mondu\Model\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Mondu\Mondu\Model\Config\MonduConfigProvider;

class Adjust extends CommonRequest implements RequestInterface
{
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var MonduConfigProvider
     */
    private $configProvider;

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
     * @inheritDoc
     */
    public function request($params)
    {
        $url = $this->configProvider->getApiUrl('orders') . '/' . $params['orderUid'] . '/adjust';

        unset($params['orderUid']);
        $resultJson = $this->sendRequestWithParams('post', $url, json_encode($params));

        if (!$resultJson) {
            throw new LocalizedException(__('something went wrong'));
        }

        return json_decode($resultJson, true);
    }
}
