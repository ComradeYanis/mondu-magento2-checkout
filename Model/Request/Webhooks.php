<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

class Webhooks extends AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $topic;

    /**
     * Request
     *
     * @param array|null $params
     * @return $this
     */
    public function request($params = null): Webhooks
    {
        $url = $this->configProvider->getApiUrl('webhooks');

        $this->sendRequestWithParams('post', $url, $this->serializer->serialize([
            'address' => $this->configProvider->getWebhookUrl(),
            'topic' => $this->getTopic()
        ]));

        return $this;
    }

    /**
     * Set webhook topic
     *
     * @param string $topic
     * @return $this
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;
        return $this;
    }

    /**
     * Get webhook topic
     *
     * @return string
     */
    private function getTopic()
    {
        return $this->topic;
    }
}
