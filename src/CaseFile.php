<?php
namespace Penneo\SDK;

class CaseFile extends Entity
{
	protected static $propertyMapping = array(
		'create' => array('title','metaData','sendAt','expireAt','visibilityMode'),
		'update' => array('title','metaData')
	);
	protected static $relativeUrl = 'casefiles';

	protected $title;
	protected $metaData;
	protected $sendAt;
	protected $expireAt;
	protected $visibilityMode;
	protected $status;
	protected $created;
	protected $signIteration;

	public function __construct()
	{
		// Set default visibility mode
		$this->visibilityMode = 0;
	}

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

	public function activate()
	{
		return parent::callAction($this, 'activate');
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
	
	public function getSendAt()
	{
		return new \DateTime('@'.$this->sendAt);
	}
	
	public function setSendAt(\DateTime $sendAt)
	{
		$this->sendAt = $sendAt->getTimestamp();
	}

	public function getExpireAt()
	{
		return new \DateTime('@'.$this->expireAt);
	}
	
	public function setExpireAt(\DateTime $expireAt)
	{
		$this->expireAt = $expireAt->getTimestamp();
	}

	public function getVisibilityMode()
	{
		return $this->visibilityMode;
	}
	
	public function setVisibilityMode($visibilityMode)
	{
		$this->visibilityMode = $visibilityMode;
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
		return new \DateTime('@'.$this->created);
	}
	
	public function getSignIteration()
	{
		return $this->signIteration;
	}
}
