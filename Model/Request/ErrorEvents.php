<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

class ErrorEvents extends AbstractRequest
{
    /**
     * @var bool
     */
    protected $sendEvents = false;

    /**
     * Request
     *
     * @param array $params
     * @return mixed
     */
    public function request($params)
    {
        $url = $this->configProvider->getApiUrl('plugin/events');
        $resultJson = $this->sendRequestWithParams('post', $url, $this->serializer->serialize($params));

        return $this->serializer->unserialize($resultJson);
    }
}
