# Copy Recipients
Copy recipients are the people that don't sign the document but get a copy of
the signed documents when the case file is completed.

## Creating a copy recipient
A copy recipient is always linked to a case file and can't exist on its own. On
construction of a copy recipient object, a case file object must be passed to
the copy recipient constructor. The example below illustrates how to create a
new copy recipient on an existing case file object in _$myCaseFile_:

```php
// Create a new recipient object
$myRecipient = new CopyRecipient($myCaseFile);

// Set the recipient name
$myRecipient->setName('HR Manager');

// Set the recipient email
$myRecipient->setEmail('hr@acme.org');

// Finally, persist the new object
CopyRecipient::persist($myRecipient);
```
