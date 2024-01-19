<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

use Magento\Framework\Exception\LocalizedException;

class ConfirmOrder extends AbstractRequest implements RequestInterface
{
    /**
     * @inheritDoc
     */
    protected function request($params)
    {
        $url = $this->configProvider->getApiUrl('orders') . '/' . $params['orderUid'] . '/confirm';

        $resultJson = $this->sendRequestWithParams(
            'post',
            $url,
            $this->serializer->serialize(['external_reference_id' => $params['referenceId']])
        );

        if (!$resultJson) {
            throw new LocalizedException(__('Mondu: something went wrong'));
        }

        $result = $this->serializer->unserialize($resultJson);

        if (isset($result['errors']) || isset($result['error'])) {
            throw new LocalizedException(__('Mondu: something went wrong'));
        }

        return $result;
    }
}
