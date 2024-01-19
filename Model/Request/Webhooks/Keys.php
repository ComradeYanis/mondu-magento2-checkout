<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request\Webhooks;

use Magento\Framework\Exception\LocalizedException;
use Mondu\Mondu\Model\Request\AbstractRequest;
use Mondu\Mondu\Model\Request\RequestInterface;

class Keys extends AbstractRequest implements RequestInterface
{
    /**
     * @var int
     */
    protected $storeId = 0;

    /**
     * @var string
     */
    protected $webhookSecret;

    /**
     * @var int
     */
    protected $responseStatus;

    /**
     * Request
     *
     * @param array|null $params
     * @return $this
     */
    protected function request($params = null): Keys
    {
        $url = $this->configProvider->getApiUrl('webhooks/keys');
        $resultJson = $this->sendRequestWithParams('get', $url);

        if ($resultJson) {
            $result = $this->serializer->unserialize($resultJson);
        }

        $this->webhookSecret = $result['webhook_secret'] ?? null;
        $this->responseStatus = $this->curl->getStatus();

        return $this;
    }

    /**
     * Check if request was successful
     *
     * @return $this
     * @throws LocalizedException
     */
    public function checkSuccess(): Keys
    {
        if ($this->responseStatus !== 200 && $this->responseStatus !== 201) {
            throw new LocalizedException(__(
                'Could\'t register webhooks, check to see if you entered Mondu api key correctly'
            ));
        }
        return $this;
    }

    /**
     *
     * REMOVE NOT NEEDED SINGLE METHOD
     * Update
     *
     * @return $this
     */
    public function update(): Keys
    {
        $this->configProvider->updateWebhookSecret($this->getWebhookSecret());
        return $this;
    }

    /**
     * Get Webhook Secret
     *
     * @return string
     */
    public function getWebhookSecret()
    {
        return $this->webhookSecret;
    }
}
