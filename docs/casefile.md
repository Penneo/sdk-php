# Case files
The case file object is a container used to bundle documents and signers. Every signing process starts with a case file.

## Creating a new case file
Creating a case file is dead simple: 

```php
// Create a new case file object
$myCaseFile = new CaseFile();

// Set the case file title
$myCaseFile->setTitle('My brand new case file');

// Finally, persist the object
CaseFile::persist($myCaseFile);
```

## Sending a case file out for signing
When the case file contains the relevant documents and signers, it has to be "send out for signing" before the signing process can begin. This is accomplished by calling the __send()__ method on the case file object.
You can delay the sending by setting the sendAt time using the __setSendAt()__ method on the case file object. This method takes a DateTime object as parameter.

If you want to distribute the signing links yourself, use the __activate()__ method instead to activate the case file signing links.

Once the case file has been sent or activated, documents and signers can no longer be added or removed.

## Setting an expiry time on a case file
A case file can be set to expire using the __expireAt()__ method on the object. When a case file is expired, the signers can no longer sign the case file documents. The __expireAt()__ method takes a DateTime object as parameter.

## Retrieve existing case files
There is several ways to retrieve case files from Penneo. Available methods for retrieving case files are:

* __CaseFile::find($id)__
Find one specific case file by its ID.
* __CaseFile::findAll()__
Find all case files accessible by the authenticated user.
* __CaseFile::findBy(array $criteria, array $orderBy, $limit, $offset)__
Find all case files matching _$criteria_ ordered by _$orderBy_. If _$limit_ is set, only _$limit_ results are returned. If _$offset_ is set, the _$offset_ first results are skipped.
Criteria can either be _title_ or _metaData_.
* __CaseFile::findOneBy(array $criteria, array $orderBy)__
Same as _findBy_ setting _$limit_ = 1 and _$offset_ = null
* __CaseFile::findByTitle($title, array $orderBy, $limit, $offset)__
Same as _findBy_ using title as criteria.
* __CaseFile::findOneByTitle($title, array $orderBy)__
Same as _findOneBy_ using title as criteria.
* __CaseFile::findByMetaData($metaData, array $orderBy, $limit, $offset)__
Same as _findBy_ using metaData as criteria.
* __CaseFile::findOneByMetaData($metaData, array $orderBy)__
Same as _findOneBy_ using metaData as criteria.

Below is a couple of examples:

```php
// Retrieve all case files
$myCaseFiles = CaseFile:findAll();

// Retrieve a specific case file (by id)
$myCaseFile = CaseFile::find(271184);

// Retrieve all case files that contains the word "the" in their title and sort descending on creation date
$myCaseFiles = CaseFile::findByTitle(
    'the',
    array('created' => 'desc')
);

// Retrieve case files from offset 10 until 110 ordered by title in ascending order
$myCaseFiles = CaseFile::findBy(
	array(),
	array('title' => 'asc'),
	10,
	100
);
```

## Deleting a case file
A case file can be completely deleted from Penneos document store as long as it is in the _new_ state. As soon as it is send out, a delete request will only cause its status to be changed to _deleted_. A case file is deleted like so:

```php
// Delete case file
CaseFile::delete($myCaseFile);
```

## Retrieving linked objects
A case file contains both signer and document objects. These objects can be retrieved using the following methods:

* __getDocuments()__
Returns the documents linked to the case file as an array of document objects.
* __getSigners()__
Returns the signers linked to the case file as an array of signer objects.
* __findSigner($id)__
Find and return a specific signer by _$id_.

## State variables
A series state variables are used to describe the case file state over the course of its life time. The methods for retrieving the state variables are described below:

* __getStatus()__
Returns the status of the case file as a string. Possible status values are:
 * _new_: The case file hasn't been sent out for signing yet
 * _pending_: The case file is out for signing
 * _rejected_: One of the signers has rejected to sign
 * _deleted_: The case file has been out for signing but have since been deleted
 * _signed_: The case file is signed, but the signed documents are not generated
 * _completed_: The signing process is completed
* __getCreatedAt()__
Returns the date and time the case file was created as a _DateTime_ object
* __getSignIteration()__
Returns the current sign iteration. This is only relevant if the signing process is not parallel. In that case, the signing process is broken into iterations.
