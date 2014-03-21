<?php
namespace Penneo\SDK;

class CaseFile extends Entity
{
	protected static $propertyMapping = array(
		'create' => array('title','metaData'),
		'update' => array('title','metaData')
	);
	protected static $relativeUrl = 'casefiles';

	protected $title;
	protected $metaData;
	protected $status;
	protected $created;
	protected $signIteration;

	public function getDocuments()
	{
		return parent::getLinkedEntities($this, 'Penneo\SDK\Document');
	}

	public function getSigners()
	{
		return parent::getLinkedEntities($this, 'Penneo\SDK\Signer');
	}
	
	public function findSigner($id)
	{
		return parent::findLinkedEntity($this, 'Penneo\SDK\Signer', $id);
	}

	public function send()
	{
		return parent::callAction($this, 'send');
	}

	public function getTitle()
	{
		return $this->title;
	}
	
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
	public function getMetaData()
	{
		return $this->metaData;
	}
	
	public function setMetaData($meta)
	{
		$this->metaData = $meta;
	}
	
	public function getStatus()
	{
		switch ($this->status) {
			case 0:
				return 'new';
			case 1:
				return 'pending';
			case 2:
				return 'rejected';
			case 3:
				return 'deleted';
			case 4:
				return 'signed';
			case 5:
				return 'completed';
		}
	
		return 'deleted';
	}
	
	public function getCreatedAt()
	{
		return new \Datetime('@'.$this->created);
	}
	
	public function getSignIteration()
	{
		return $this->signIteration;
	}
}
