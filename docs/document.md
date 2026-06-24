# Documents
The document object represents (and contains) the document file. **Creating documents via the SDK currently supports PDF only** (signable document or annex). The API uses a generic `file` field; `setFile()` is the preferred way to supply the path, while `setPdfFile()` remains for compatibility.

A document can either be a signable document or an unsignable _annex_. Documents are always linked to a case file and can't exist on their own.

## Creating a document
Creating a document requires that you have a case file first since a document can't exist on its own. The case file must be passed to the _Document_ constructor and the document will be linked to the case file.
Per default, a document is an _annex_ that can't be signed. To make a document signable, the __makeSignable()__ method must be called.
The following example shows how to create a signable document linked to _$myCaseFile_:

```php
// Create a new document object
$myDocument = new Document($myCaseFile);

// Set the document title
$myDocument->setTitle('My brand new document');

// Add the PDF file (generic `file` field on the API)
$myDocument->setFile('/path/to/document.pdf');

// Make the document signable
$myDocument->makeSignable();

// Optionally set the display order (0-indexed; controls the order signers see documents)
$myDocument->setDocumentOrder(0);

// Finally, persist the object
Document::persist($myDocument);
```

> **Note:** `setPdfFile()` still works but is deprecated. Use `setFile()` for new code — same behaviour today (PDF), aligned with the API’s `file` parameter for future formats.

## Retrieve existing documents
There is several ways to retrieve document from Penneo. Available methods for retrieving documents are:

* __Document::find($id)__
Find one specific document by its ID.
* __Document::findAll()__
Find all documents accessible by the authenticated user.
* __Document::findBy(array $criteria, array $orderBy, $limit, $offset)__
Find all documents matching _$criteria_ ordered by _$orderBy_. If _$limit_ is set, only _$limit_ results are returned. If _$offset_ is set, the _$offset_ first results are skipped.
Criteria can either be _title_ or _metaData_.
* __Document::findOneBy(array $criteria, array $orderBy)__
Same as _findBy_ setting _$limit_ = 1 and _$offset_ = null
* __Document::findByTitle($title, array $orderBy, $limit, $offset)__
Same as _findBy_ using title as criteria.
* __Document::findOneByTitle($title, array $orderBy)__
Same as _findOneBy_ using title as criteria.
* __Document::findByMetaData($metaData, array $orderBy, $limit, $offset)__
Same as _findBy_ using metaData as criteria.
* __Document::findOneByMetaData($metaData, array $orderBy)__
Same as _findOneBy_ using metaData as criteria.

Below is a couple of examples:

```php
// Retrieve all documents
$myDocuments = Document:findAll();

// Retrieve a specific document (by id)
$myDocument = Document::find(7382393);

// Retrieve all documents that contains the word "the" in their title and sort descending by creation date
$myDocuments = Document::findByTitle(
	'the',
	array('created' => 'desc')
);

// Retrieve documents from offset 10 until 110 ordered by title in ascending order
$myDocuments = Document::findBy(
	array(),
	array('title' => 'asc'),
	10,
	100
);
```

## Downloading the document content
When the signing process is completed (when __getStatus()__ returns "completed"), the signed document can be downloaded by calling __getContent()__:

```php
// Download the signed document (binary)
$binary = $myDocument->getContent();
file_put_contents('signed-document.pdf', $binary);

// Download the unsigned version
$unsigned = $myDocument->getContent(false);

// Document format as returned by the API (typically "pdf" today)
$format = $myDocument->getFormat();
```

The `getContent()` method accepts one optional parameter:

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$signed` | bool | `true` | Return the signed version when available |

> **Note:** `getPdf()` still works but is deprecated. Use `getContent()` for new code — raw binary from `/content` without base64 JSON.

## Retrieving linked objects
A signable document contains signature lines. These objects can be retrieved using the following methods:

* __getSignatureLines()__
Returns the signature lines linked to the document as an array of signature line objects.
* __findSignatureLine($id)__
Find and return a specific signature line by _$id_.

## State variables
A series state variables are used to describe the document state over the course of its life time. The methods for retrieving the state variables are described below:

* __getStatus()__
Returns the status of the document as a string. Possible status values are:
 * _new_: The document hasn't been sent out for signing yet
 * _pending_: The document is out for signing
 * _rejected_: One of the signers has rejected to sign
 * _deleted_: The document has been out for signing but have since been deleted
 * _signed_: The document is signed, but the signed document is not generated yet
 * _completed_: The signing process is completed
* __getCreatedAt()__
Returns the date and time when the document was created as a _DateTime_ object.
* __getModifiedAt()__
Returns the date and time when the document was last modified as a _DateTime_ object.
* __getCompletedAt()__
Returns the date and time when the document signing process was finalized as a _DateTime_ object.
* __getDocumentId()__
Returns the unique ID that is stamped on every page in the document for identification purposes.
* __getDocumentOrder()__
Returns the display order of the document (integer, 0-indexed). Controls the order in which signers see documents within a case file.
* __setDocumentOrder($order)__
Sets the display order. Can be called before `persist()` (create) or before `Document::persist($doc)` on an existing document (update).
* __getFormat()__
Returns the document format as a string (typically `"pdf"`). Returns `null` for locally created documents that have not been persisted yet.
* __getOptions()__
Returns the option values assigned to the document.
