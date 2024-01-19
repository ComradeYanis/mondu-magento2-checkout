<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

class Memo extends AbstractRequest implements RequestInterface
{
    /**
     * @inheritdoc
     */
    protected function request($params)
    {
        $url = $this->configProvider->getApiUrl('invoices').'/'.$params['invoice_uid'].'/credit_notes';

        unset($params['invoice_uid']);
        $resultJson = $this->sendRequestWithParams('post', $url, $this->serializer-serialize($params));

        if ($resultJson) {
            $result = $this->serializer->unserialize($resultJson);
        }

        return $result ?? null;
    }
}
