<?php

declare(strict_types=1);

namespace Penneo\SDK;

use Penneo\SDK\ApiConnector;

/**
 * You can configure Penneo to send a request to your servers when certain events occur.
 * See https://github.com/Penneo/sdk-php/blob/master/docs/webhooks.md for more details.
 */
class WebhookSubscription extends Entity
{
    protected static $relativeUrl = '/webhook/api/v1/subscriptions';
    protected static $propertyMapping = array(
        'create' => array(
            'eventTypes',
            'endpoint',
            'isActive'
        ),
        'update' => array(
            'eventTypes',
            'endpoint',
            'isActive'
        )
    );

    /** @var int */
    protected int $customerId;

    /** @var string */
    protected string $secret;

    /** @var bool */
    protected bool $isActive = true;

    /** @var EventType[] */
    protected array $eventTypes;

    /** @var string */
    protected string $endpoint;

    /** @return int */
    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return $this
     */
    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return EventType[]
     */
    public function getEventTypes(): array
    {
        return $this->eventTypes;
    }

    /**
     * @param EventType[] $eventTypes
     *
     * @return $this
     */
    public function setEventTypes(array $eventTypes): static
    {
        $this->eventTypes = $eventTypes;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     *
     * @return $this
     */
    public function setEndpoint(string $endpoint): static
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public static function test(): bool
    {
        return ApiConnector::callServer(self::$relativeUrl . '/test', null, 'post', array()) !== null;
    }
}
