# Signers
A signer object represents the person that signs. Apart from the obvious name, a signer can also be assigned and organisation name (that he/she signs on behalf of) and a social security number or VAT identification number to verify his/her identity.

## Creating a signer
A signer is always linked to a case file and can't exist on its own. On construction of a signer object, a case file object must be passed to the signer constructor.
The example below illustrates how to create a new signer on an existing case file object in _$myCaseFile_:

```php
// Create a new signer object
$mySigner = new Signer($myCaseFile);

// Set the signer name
$mySigner->setName('John Doe');

// Set the optional organisation to sign on behalf of
$mySigner->setOnBehalfOf('Acme Corporation');

// Set the optional social security number
$mySigner->setSocialSecurityNumber('0101501111', 'dk:cpr');

// The SMS validation is relying on the same mechanism using 'sms' as the ssnType
// The social security number to use must then contain a mobile number include the country code but not the leading +
$mySigner->setSocialSecurityNumber('4511223344', 'sms');

// Set the optional VAT identification number
$mySigner->setVATIdentificationNumber('12345678');

// Finally, persist the new object
Signer::persist($mySigner);
```

The supported values for SSN Type can be found here: https://app.penneo.com/api/v1/signers/ssn-types.

## Retrieving the signing request
Every time you create a new signer, a signing request is also generated for the new signer. After persisting the new signer object, you can retrieve the signing request like so:

```php
// Retrieve the signing request object
$mySigningRequest = $mySigner->getSigningRequest();
```
