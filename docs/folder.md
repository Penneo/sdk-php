# Folders
Case files can be assigned to folders in order to group them together. The folders available from the API corresponds to the folders seen in the Penneo WebApp.

## Creating a folder
A folder is simply identified by its title. The example below shows how to create a folder:

```php
// Create a new folder object
$myFolder = new Folder();

// Set the folder title
$myFolder->setTitle('New folder');

// Persist the new object
Folder::persist($mySigner);
```

## Manipulating folder contents
An empty folder is not much fun. The example below shows how to assign and unassign case files, and how to list the contents of a folder:

```php
// Assign a case file to a folder
$myFolder->addCaseFile($caseFile1);

// Get a list of all the case files in the folder
$caseFiles = $myFolder->getCaseFiles();
for ($caseFile in $caseFiles) {
	print($caseFile->getName();
}

// Paginate over case files in a folder
$page2 = $myFolder->getCaseFiles(2, 10);
$page3 = $myFolder->getCaseFiles(3, 10);

// Now, remove that same case file again
$myFolder->removeCaseFile($caseFile1);

```

## Retrieve existing folders
There is several ways to retrieve folders from Penneo. Available methods for retrieving folders are:

* __Folder::find($id)__
Find one specific folder by its ID.
* __Folder::findAll()__
Find all folders accessible by the authenticated user.
* __Folder::findBy(array $criteria, array $orderBy, $limit, $offset)__
Find all folders matching _$criteria_ ordered by _$orderBy_. If _$limit_ is set, only _$limit_ results are returned. If _$offset_ is set, the _$offset_ first results are skipped.
Criteria can be _title_.
* __Folder::findOneBy(array $criteria, array $orderBy)__
Same as _findBy_ setting _$limit_ = 1 and _$offset_ = null
* __Folder::findByTitle($title, array $orderBy, $limit, $offset)__
Same as _findBy_ using title as criteria.
* __Folder::findOneByTitle($title, array $orderBy)__
Same as _findOneBy_ using title as criteria.

Below is a couple of examples:

```php
// Retrieve all folders
$myFolder = Folder:findAll();

// Retrieve a specific folder (by id)
$myFolder = Folder::find(14284);

// Retrieve all folders that contains the word "the" in their title and sort descending on folder title
$myFolder = Folder::findByTitle(
	'the',
	array('title' => 'desc')
);

// Retrieve folders from offset 10 until 110 ordered by title in ascending order
$myFolder = Folder::findBy(
	array(),
	array('title' => 'asc'),
	10,
	100
);
```

## Deleting a folder
Folders can be deleted, even if the contain case files. This will only delete the folder and its mappings, __NOT__ the case files. To delete a folder do the following:

```php
// Delete a folder
Folder::delete($myFolder);
```
