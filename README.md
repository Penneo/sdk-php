# Penneo SDK for PHP

## Resources

* [API documentation][docs-api] - Information about the Penneo API, methods and responses

## Getting Started
More to come

## Quick Example

### Signing a document (simple)

```php
<?php
require 'vendor/autoload.php';

use Penneo\SDK\ApiConnector;
use Penneo\SDK\Document;
use Penneo\SDK\Signer;

ApiConnector::initialize('apiKeyHere','apiSecretHere');
$myDoc = new Document(file_get_contents('/path/to/pdfFile'),'Demo document');
$mySigner = new Signer('John Doe', 'john@doe.com');
$myDoc->addSigner($mySigner);
$url = $myDoc->createSigningRequest($mySigner, false, null, 'http://go/here/on/success', 'http://go/here/on/failure');

print('<a href="'.$url.'">Sign now</a><br>');
```

[docs-api]: https://penneo.com/api/docs