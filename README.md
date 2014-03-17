# Penneo SDK for PHP

## Resources

* [API documentation][docs-api] - Information about the Penneo API, methods and responses

## Getting Started
More to come

## Quick Example

### Signing a document (simple)
In this example, we show how to create a document with a single signer.
The link to the Penneo signing portal, where the actual signing takes place, is printed as a result.

```php
<?php
require 'vendor/autoload.php';

use Penneo\SDK\ApiConnector;
use Penneo\SDK\CaseFile;
use Penneo\SDK\Document;
use Penneo\SDK\SignatureLine;
use Penneo\SDK\Signer;
use Penneo\SDK\SigningRequest;

// Initialize the connection to the API
ApiConnector::initialize('apiKeyHere','apiSecretHere');

// Create a new case file
$myCaseFile = new CaseFile();
$myCaseFile->setTitle('Demo case file');
CaseFile::persist($myCaseFile);

// Create a new signable document in this case file
$myDocument = new Document();
$myDocument->setCaseFile($myCaseFile);
$myDocument->setTitle('Demo document');
$myDocument->setPdfFile('/path/to/pdfFile');
$myDocument->makeSignable();
Document::persist($myDocument);

// Create a new signature line on the document
$mySignatureLine = new SignatureLine($myDocument);
$mySignatureLine->setRole('MySignerRole');
SignatureLine::persist($mySignatureLine);

// Create a new signer that can sign documents in the case file
$mySigner = new Signer($myCaseFile);
$mySigner->setName('John Doe');
Signer::persist($mySigner);

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

### Validating a person
In this example we demontrate, how to validate a person from his/her electronic ID, social security number and lookup 
the needed info in various public registers.

```php
<?php
require 'vendor/autoload.php';

use Penneo\SDK\ApiConnector;
use Penneo\SDK\Validation;

// Initialize the connection to the API
ApiConnector::initialize('apiKeyHere','apiSecretHere');

// Create a new validation
$myValidation = new Validation();
$myValidation->setName('John Doe');
Validation::persist($myValidation);

// Output the validation link.
print('<a href="'.$myValidation->getLink().'">Validate now</a>');

```

[docs-api]: https://penneo.com/api/docs
