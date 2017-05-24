# Penneo SDK for PHP
Penneo is all about digitizing the process of signing documents and contacts. The Penneo SDK for PHP enables PHP developers to use digital signing of documents in their PHP code. Get more info at [penneo.com](https://penneo.com/) about how to become a customer.

## Prerequisites
The Penneo SDK for PHP requires that you are using PHP 5.3 or newer. Also you must have a recent version of cURL >= 7.16.2 compiled with OpenSSL and zlib.

## Getting Started
You can install the SDK by simply cloning or downloading the source, or you can use Composer. We recommend that you use Composer:

### Installing via Composer

The recommended way to install the Penneo SDK is through [Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, update your project's composer.json file to include the SDK:

```javascript
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
You can find out more on how to install Composer, configure autoloading, and other best-practices for defining dependencies at [getcomposer.org](http://getcomposer.org).

## Documentation
This section documents the different objects available through the SDK and how to use them. 

### Authentication
In order to use the SDK, you will have to authenticate against the Penneo API. Authentication is done in a single line of code, using your Penneo API credentials:

```php
// Initialize the connection to the API
Penneo\SDK\ApiConnector::initialize('apiKeyHere', 'apiSecretHere', $endpoint);
```

If you have a reseller account, you can carry out operations on behalf of one of your customers, by specifying the customer id as well:

```php
// Initialize the connection to the API as customer
Penneo\SDK\ApiConnector::initialize('apiKeyHere','apiSecretHere', $endpoint, $customerId);
```

The endpoint url can point to either the sandbox (for testing) or the live system. Both endpoint urls are available on request.

### Document signing
* [Folders][folder-docs]
Folder objects are containers for case file objects.
* [Case files][casefile-docs]
The case file object is a container used to bundle documents and signers. Every signing process starts with a case file.
* [Documents][document-docs]
The document object represents (and contains) the actual PDF document.
* [Signature lines][signature-line-docs]
Every signable document must have at least one signature line. Think of it as the dashed line that people used to sign using a pen...
* [Signers][signer-docs]
A signer object represents the person that signs.
* [SigningRequests][signing-request-docs]
Think of the signing request as being the instructions for the signer on what to sign. It can either be the formal letter accompanying the document, the yellow post-its showing where to sign, or both.
* [Case file templates][template-docs]
Instead of specifying the mapping between documents and signers explicitly, it is possible to use one of the many pre-defined case file templates provided by Penneo.

### Identity validation
* [Validations][validation-docs]
Money laundering regulations require companies to validate the identity of their clients. The validation object can accomplish this, using only a social security number and an electronic ID.

## Quick Examples

### Signing a document (simple)
In this example, we show how to create a document with a single signer.
The link to the Penneo signing portal, where the actual signing takes place, is printed as a result.

```php
<?php
require 'vendor/autoload.php';

namespace Penneo\SDK;

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
The result is a link to the Penneo validation page. The person in question must follow the link and complete some actions in order to be validated.

```php
<?php
require 'vendor/autoload.php';

namespace Penneo\SDK;

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
