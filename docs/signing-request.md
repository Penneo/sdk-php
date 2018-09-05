# Signing requests
Think of the signing request as being the instructions for the signer on what to sign. It can either be the formal letter accompanying the document, the yellow post-its showing where to sign, or both. Using Penneo, the envelope containing the contract is replaced by a hyperlink. This link points to the Penneo signing portal, where the signer can go and sign.

So, before the signer can actually sign anything, he/she needs a link to the Penneo signing portal. This link can be distributed in two ways. Either you let Penneo handle the distribution of a signing link via email, or you handle the distribution yourself. Both approaches are handled through the signing request.

It should be noted, that a signing request is implicitly created, every time a new signer is created.

## Distributing the signing link through Penneo
Penneo can distribute the signing link for you. The link will be send out to the signer in an email through Penneos infrastructure.

The process is best explained by an example. Lets say that we have a signing request in _$mySigningRequest_ that we wish to deliver via Penneo:

```php
// Set the signers email address
$mySigningRequest->setEmail('john@doe.com');

// Define the content of the email
$mySigningRequest->setEmailSubject('Contract for signing');
$mySigningRequest->setEmailText('Dear john. Please sign the contract.');

// Store the changes to the signing request
SigningRequest::persist($mySigningRequest);
```

Note that the signing request emails won't actually be send out until you call the __send()__ method on the owning case file object.

If you need to re-send the signing request email (fx. due to a change in the email address), all you need to do is call the __send()__ method on the signing request object like so:

```php
// Re-send the signing request email
$mySigningRequest->send();
```

### Reminder emails
When using Penneo to distribute signing links, it is also possible to have Penneo remind the signers regularly by email, until the signer either signs or rejects to sign. To set up a reminder, just use the __setReminderInterval()__ method to set the number of days between reminders.

### Customizing email messages

The following types of emails are sent to the signer based on the status of the signing request:

1. Initial email containing the singing request link
2. Reminder to the signer
3. Case file completed notification to the signer

The emails can also be configured as follows:

```php
// Specify the content of the email
$mySigningRequest->setEmailSubject('Contract for signing');
$mySigningRequest->setEmailText('Dear john. Please sign the contract.');

// Specify the content of the reminder email
$mySigningRequest->setReminderEmailSubject('Reminder for Contract for signing');
$mySigningRequest->setReminderEmailText('Dear john. This is to remind you to please sign the contract.');

// Specify the content of the completed email
$mySigningRequest->setCompletedEmailSubject('Case file signed');
$mySigningRequest->setCompletedEmailText('All parties have now signed and the case file is completed');

// Store the changes to the signing request
SigningRequest::persist($mySigningRequest);
```

### Using html in the emails
You can use html instead of plain texts to completely configure the emails, provided that email customization is allowed for your account. Please get in touch with support@penneo.com if you want to enable this.

```php
// NOTE: Your Penneo account must allow email customizations
$mySigningRequest->setEmailFomat('html');
```

## Distributing the signing link yourself
If you don't want Penneo to distribute your signing links, you can handle the process yourself. All you need to do is to fetch the link from the signing request object:

```php
$myLink = $mySigningRequest->getLink();
```

Note that the signing link won't be active until you activate the case file by calling the __activate()__ method on the owning case file object.

## Customizing the signing process
When the signer completes an action on the Penneo signing portal (that is, he/she signs or rejects to sign), the signer is redirected to the default Penneo success/failure page. You can choose to use your own custom status pages instead. All you need to do is pass the urls to the signing request like so:

```php
// Set the url for the custom success page
$mySigningRequest->setSuccessUrl('http://go/here/on/success');

// Set the url for the customer failure/reject page
$mySigningRequeset->setFailUrl('http://go/here/on/failure');

// Store the changes to the signing request
SigningRequest::persist($mySigningRequest);
```

## Protecting the signing link
Per default, the signing link has no access control. That means that anyone who gets their hands on it is able to see and download the documents in the case file.

It is possible to protect the signing link, by requiring the user to identify using their EID like so:

```php
// Enable access control for the signing request
$mySigningRequest->setAccessControl(true);
```
Note that for access control to work, you must either specify a social security number or VAT identification number when creating the signer. The user will then be matched to the identification information specified.

## State variables
A series state variables are used to describe the signing state. The methods for retrieving the state variables are described below:

* __getStatus()__
Returns the status of the signer as a string. Possible status values are:
 * _new_: The signing request hasn't been sent out yet
 * _pending_: The signer still needs to sign something.
 * _rejected_: The signer has rejected to sign
 * _deleted_: The signing request has been distributed, but has since been deleted.
 * _signed_: The signer is done signing.
 * _undeliverable_: The signing request email could not be delivered
* __getRejectReason()__
Returns the reason, given by the signer, for rejecting to sign the documents in the case file. The response is only valid, if the status of the signing request is _rejected_.
