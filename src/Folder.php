<?php
namespace Penneo\SDK;

class Folder extends Entity
{
	protected static $propertyMapping = array(
		'create' => array('title'),
		'update' => array('title')
	);
	protected static $relativeUrl = 'folders';

	protected $title;

	public function getCaseFiles()
	{
		return parent::getLinkedEntities($this, 'Penneo\SDK\CaseFile');
	}

	public function addCaseFile(CaseFile $caseFile)
	{
		return parent::linkEntity($this, $caseFile);
	}

	public function removeCaseFile(CaseFile $caseFile)
	{
		return parent::unlinkEntity($this, $caseFile);
	}

	public function getTitle()
	{
		return $this->title;
	}
	
	public function setTitle($title)
	{
		$this->title = $title;
	}
}
