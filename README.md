# Penneo SDK for PHP

Penneo is all about digitizing the process of signing documents and contacts. The Penneo SDK for PHP enables PHP
developers to use digital signing of documents in their PHP code. Get more info at [penneo.com](https://penneo.com/)
about how to become a customer.

## Prerequisites

The Penneo SDK for PHP requires that you are using PHP 5.3 or newer. Also you must have a recent version of cURL >=
7.16.2 compiled with OpenSSL and zlib.

## Getting Started

You can install the SDK by simply cloning or downloading the source, or you can use Composer. We recommend that you use
Composer:

### Installing via Composer

The recommended way to install the Penneo SDK is through [Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, update your project's composer.json file to include the SDK:

```json
{
    "require": {
        "penneo/penneo-sdk-php": "1.*"
    }
}
```

After installing, you need to require Composer's autoloader before calling any SDK functions e.g.:

```php
<?php

require 'vendor/autoload.php';

// Call SDK functions here..

?>
```

You can find out more on how to install Composer, configure autoloading, and other best-practices for defining
dependencies at [getcomposer.org](http://getcomposer.org).

## Documentation

This section documents the different objects available through the SDK and how to use them.

### Authentication

The SDK supports two different methods of authentication:

- **OAuth 2.0**: This is the recommended method of authentication. It is more secure and allows you to perform
  operations
  on behalf of your users.
- **WSSE**: This is the legacy method of authentication. It requires you to store your users' API credentials in your
  application.

#### OAuth 2.0

Read more about OAuth 2.0 [here](https://oauth.net/2/).

##### OAuth client

You will need an OAuth client to be used by your integration to perform the OAuth 2.0 authentication flow.

To create such a client please open a ticket in
our [Support Center](https://penneo.my.site.com/support/s/contactsupport?language=en_US) asking for the creation of an
integration client. Please specify in the request the name of the client and the redirect_uri towards which you will
receive the callback
requests.

We will provide you with a `client_id` and `client_secret`.

##### Authorization request

You will have to build the authorization URI using your `client_id`, `client_secret` and your chosen `redirect_uri`,
then redirect the user to the `$authorizationUrl`:

```php
// Build the OAuth instance
$oAuth = Penneo\SDK\OAuth\OAuthBuilder::start()
    ->setEnvironment('environmentHere')
    ->setClientId('clientIdHere')
    ->setClientSecret('clientSecretHere')
    ->setRedirectUri('redirectUriHere')
    ->setTokenStorage(new SessionTokenStorage())
    ->build();

// Generate code verfier and a code challenge
$pkce = new Penneo\SDK\OAuth\PKCE\PKCE();
// Code verifier should be stored (e.g. in user session) as it will be required later for the authorization code exchange
$codeVerifier = $pkce->getCodeVerifier();
$codeChallenge = $pkce->getCodeChallenge($codeVerifier);

// Build authorization request URL
$authorizationUrl = $oAuth->buildRedirectUrl($scope, $codeChallenge)

// Redirect currently logged in user to the $authorizationUrl (Penneo Auth Service)
```

The environment can either be `sandbox` for testing, or `production` for the live system.

Following the standard OAuth 2.0 flow, the user is brought to the authorization page where they can login into Penneo
with their chosen method (e.g. username and password, Google, Microsoft, etc.) and authorize your application to access
their Penneo account.

The user is then redirected back to the `redirect_uri` with a single-use authorization code.

##### Exchanging authorization code with access token

Now you have a single-use `authorization code` and a `code verifier` that you can use to exchange them for
an `access_token` which will be stored in the token storage defined previously in the `OAuthBuilder`:

```php
// Exchage received authorization code with the access token
$oAuth->exchangeAuthCode($authCode, $codeVerifier);
```

When the authorization code is successfully exchanged with a new `token`, you can then initialize the OAuth 2.0
connector using the already authorized `$oAuth` instance:

```php
// Initialize the connection to the API as customer
Penneo\SDK\ApiConnector::initializeOAuth($oAuth);
```

##### OAuth Token Storage

The SDK will store the OAuth 2.0 token in the session using the `SessionTokenStorage` by default. If you want to use
another storage, you can implement your own by using the `TokenStorage` interface.

#### WSSE

The Web Services Security (WSSE) authentication is done in a single line of code, using your Penneo API credentials:

```php
// Initialize the connection to the API
Penneo\SDK\ApiConnector::initializeWsse('apiKeyHere', 'apiSecretHere', $endpoint);
```

If you have a reseller account, you can carry out operations on behalf of one of your customers, by specifying the
customer id as well:

```php
// Initialize the connection to the API as customer
Penneo\SDK\ApiConnector::initializeWsse('apiKeyHere','apiSecretHere', $endpoint, $customerId);
```

The endpoint URL can point to either the sandbox (for testing) or the live system. Both endpoint URLs are available on
request.

### Problems in production?

You should add a logger by calling `ApiConnector::setLogger()`. If you contact support, please include any
relevant `requestIds` you find in the logs.

### Document signing

* [Folders][folder-docs]
  Folder objects are containers for case file objects.
* [Case files][casefile-docs]
  The case file object is a container used to bundle documents and signers. Every signing process starts with a case
  file.
* [Documents][document-docs]
  The document object represents (and contains) the actual PDF document.
* [Signature lines][signature-line-docs]
  Every signable document must have at least one signature line. Think of it as the dashed line that people used to sign
  using a pen.
* [Signers][signer-docs]
  A signer object represents the person that signs.
* [SigningRequests][signing-request-docs]
  Think of the signing request as being the instructions for the signer on what to sign. It can either be the formal
  letter accompanying the document, the yellow post-its showing where to sign, or both.
* [Case file templates][template-docs]
  Instead of specifying the mapping between documents and signers explicitly, it is possible to use one of the many
  pre-defined case file templates provided by Penneo.

### Identity validation

* [Validations][validation-docs]
  Money laundering regulations require companies to validate the identity of their clients. The validation object can
  accomplish this, using only a social security number and an electronic ID.

## Quick Examples

### Signing a document (simple)

In this example, we show how to create a document with a single signer.
The link to the Penneo signing portal, where the actual signing takes place, is printed as a result.

```php
<?php
namespace Penneo\SDK;

require 'vendor/autoload.php';

// Create a new case file
$myCaseFile = new CaseFile();
$myCaseFile->setTitle('Demo case file');
CaseFile::persist($myCaseFile);

// Create a new signable document in this case file
$myDocument = new Document($myCaseFile);
$myDocument->setTitle('Demo document');
$myDocument->setPdfFile('/path/to/pdfFile');
$myDocument->makeSignable();
Document::persist($myDocument);

// Create a new signer that can sign documents in the case file
$mySigner = new Signer($myCaseFile);
$mySigner->setName('John Doe');
Signer::persist($mySigner);

// Create a new signature line on the document
$mySignatureLine = new SignatureLine($myDocument);
$mySignatureLine->setRole('MySignerRole');
SignatureLine::persist($mySignatureLine);

// Link the signer to the signature line
$mySignatureLine->setSigner($mySigner);

// Update the signing request for the new signer
$mySigningRequest = $mySigner->getSigningRequest();
$mySigningRequest->setSuccessUrl('http://go/here/on/success');
$mySigningRequest->setFailUrl('http://go/here/on/failure');
SigningRequest::persist($mySigningRequest);

// "Package" the case file for "sending".
$myCaseFile->send();

// And finally, print out the link leading to the signing portal.
// The signer uses this link to sign the document.
print('<a href="'.$mySigningRequest->getLink().'">Sign now</a>');
```

### Validating a person (money laundering regulations)

In this example we demontrate, how to validate a person from his/her electronic ID and social security number.
The result is a link to the Penneo validation page. The person in question must follow the link and complete some
actions in order to be validated.

```php
<?php
namespace Penneo\SDK;

require 'vendor/autoload.php';

// Create a new validation
$myValidation = new Validation();
$myValidation->setTitle('My new validation');
$myValidation->setName('John Doe');
Validation::persist($myValidation);

// Output the validation link.
print('<a href="'.$myValidation->getLink().'">Validate now</a>');

```

## Resources

* [API documentation][docs-api] - Information about the Penneo API, methods and responses

[docs-api]: https://app.penneo.com/api/docs

[folder-docs]: docs/folder.md

[casefile-docs]: docs/casefile.md

[document-docs]: docs/document.md

[signature-line-docs]: docs/signature-line.md

[signer-docs]: docs/signer.md

[signing-request-docs]: docs/signing-request.md

[template-docs]: docs/templates.md

[validation-docs]: docs/validation.md
