# Webhook Subscriptions
You set up Penneo to call your servers on certain actions like a signer signing, or a case file finalizing.

You can use https://webhook.site to play around with the system.
More info about our webhooks can be found here https://penneo.readme.io/docs/webhooks.


If for some reason your servers don't respond with a OK range header (200-299), our system will re-try the request a few times before giving up.

## Setting up a webhook subscription
```php
$webhook = new WebhookSubscription;
$webhook->setEventTypes([EventType::WebhookSubscriptionTest, EventType::SignerRequestSent]);
$webhook->setEndpoint('https://your.endpint/here');

WebhookSubscription::persist($webhook);
```

See `EventType` for a list of possible event types.

If the call was successful, you will get a `WebhookSubscription` object back with the `Id` field set.
You should now receive notifications to the specified endpoint. The notifications will be sent as a POST request with a JSON body.
Example of a notification payload:
```json
{
  "topic": "casefile",
  "eventType": "completed",
  "eventTime": {
    "date": "2025-02-19 13:01:55.517791",
    "timezone_type": 3,
    "timezone": "UTC"
  },
  "payload": {
    "id": 531,
    "status": 2
  }
}
```
The request will have an `x-event-type` header with the event type, `x-event-id` with a unique id for the event and `x-event-signature` with a timestamp and signature of the payload.
> Important: The endpoint must be a valid URL that can receive POST requests over the internet.

## Finding webhook subscriptions
```php
$webhook = WebhookSubscription::find(3410);
$webhooks = WebhookSubscription::findAll();
```

## Deleting a webhook subscription
```php
WebhookSubscription::delete($webhook);
```
> **⚠️ Deprecated:** This section describes an older version of WebhookSubscription now called WebhookSubscriptionLegacy
>
>
> ## Setting up a webhook subscription
> ```php
> $webhook = new WebhookSubscriptionLegacy();
> $webhook->setEndpoint("https://your.endpint/here");
> $webhook->setTopic("casefile");
> 
> WebhookSubscriptionLegacy::persist($webhook);
> ```
> 
> Currently, the `casefile` and `signer` topics are the only ones supported.
> 
> After calling `::persist()`, your servers will receive a HTTP call which will have a `confirmationToken` field in the body.
> > Note: the body of the request is a JSON object, even though the proper `application/json` header might not be set.
> 
> 
> ## Confirming a webhook subscription
> ```php
> $webhook = WebhookSubscriptionLegacy::find(3410);
> $webhook->confirm($token);
> ```
> 
> If everything was successful, your servers will now be called on case file/signer updates.
> 
> ## Finding webhook subscriptions
> ```php
> $webhook = WebhookSubscriptionLegacy::find(3410);
> $webhooks = WebhookSubscriptionLegacy::findAll();
> ```
> 
> 
> ## Deleting a webhook subscription
> ```php
> WebhookSubscriptionLegacy::delete($webhook);
> ```
>
> You should no longer use this functionality.

