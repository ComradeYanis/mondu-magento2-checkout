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

class ConfirmOrder extends CommonRequest implements RequestInterface
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
        $this->curl = $curl;
        $this->configProvider = $configProvider;
    }

    /**
     * @inheritDoc
     */
    protected function request($params)
    {
        $url = $this->configProvider->getApiUrl('orders') . '/' . $params['orderUid'] . '/confirm';

        $resultJson = $this->sendRequestWithParams(
            'post',
            $url,
            json_encode(['external_reference_id' => $params['referenceId']])
        );

        if (!$resultJson) {
            throw new LocalizedException(__('Mondu: something went wrong'));
        }

        $result = json_decode($resultJson, true);

        if (isset($result['errors']) || isset($result['error'])) {
            throw new LocalizedException(__('Mondu: something went wrong'));
        }

        return $result;
    }
}
