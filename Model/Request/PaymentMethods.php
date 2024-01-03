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
use Mondu\Mondu\Model\Ui\ConfigProvider;

class PaymentMethods extends CommonRequest implements RequestInterface
{
    /**
     * @var Curl
     */
    protected $curl;
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @param Curl $curl
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        Curl $curl,
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
        $this->curl = $curl;
    }

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

        $result = json_decode($resultJson, true);

        return $result['payment_methods'] ?? null;
    }
}
