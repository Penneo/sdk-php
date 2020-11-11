<?php

declare(strict_types=1);

namespace Penneo\SDK;

/**
 * You can configure Penneo to send a request to your servers whenever certain entities change.
 * See https://github.com/Penneo/sdk-net/blob/master/docs/webhooks.md for more details.
 */
class WebhookSubscription extends Entity
{
    protected static $relativeUrl = 'webhook/subscriptions';
    protected static $propertyMapping = [
        'create' => ['topic', 'endpoint'],
    ];

    /** @var int|null */
    protected $userId;

    /** @var int|null */
    protected $customerId;

    /** @var bool */
    protected $confirmed = false;

    /** @var string|null */
    protected $topic;

    /** @var string|null */
    protected $endpoint;

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): WebhookSubscription
    {
        $this->userId = $userId;
        return $this;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    public function setCustomerId(?int $customerId): WebhookSubscription
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): WebhookSubscription
    {
        $this->confirmed = $confirmed;
        return $this;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(?string $topic): WebhookSubscription
    {
        $this->topic = $topic;
        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(?string $endpoint): WebhookSubscription
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function confirm(string $confirmationToken): bool
    {
        return self::callAction(
            $this,
            'confirm',
            'POST',
            ['token' => $confirmationToken]
        );
    }
}
