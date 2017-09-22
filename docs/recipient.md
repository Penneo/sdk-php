# Recipients
Recipients are the people that don't sign the document but get a copy of the
signed documents when the case file is completed.

## Creating a recipient
A recipient is always linked to a case file and can't exist on its own. On
construction of a recipient object, a recipient object must be passed to the
recipient constructor. The example below illustrates how to create a new recipient
on an existing case file object in _$myCaseFile_:

```php
// Create a new recipient object
$myRecipient = new Recipient($myCaseFile);

// Set the recipient name
$myRecipient->setName('HR Manager');

// Set the recipient email
$myRecipient->setEmail('hr@acme.org');

// Finally, persist the new object
Recipient::persist($myRecipient);
```
