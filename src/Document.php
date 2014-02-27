<?php
namespace Penneo\SDK;

class Document extends Entity
{
	protected static $propertyMapping = array(
		'create' => array(
			'caseFileId' => 'caseFile->getId',
			'title',
			'metaData',
			'options',
			'type',
			'@pdfFile'
		),
		'update' => array('title','metaData','options')
	);
	protected static $relativeUrl = 'documents';

	protected $documentId;
	protected $title;
	protected $metaData;
	protected $options;
	protected $created;
	protected $modified;
	protected $completed;
	protected $status;
	protected $pdfFile;

	protected $caseFile;
	protected $type = 'attachment';


	public function setCaseFile(CaseFile $caseFile)
	{
		$this->caseFile = $caseFile;
	}

	public function getSignatureLines()
	{
		return parent::getLinkedEntities($this, 'Penneo\SDK\SignatureLine');
	}
	
	public function findSignatureLine($id)
	{
		return parent::findLinkedEntity($this, 'Penneo\SDK\SignatureLine', $id);
	}

	public function getPdf()
	{
		$data = parent::getAssets($this, 'pdf');
		return base64_decode($data[0]);
	}

	public function makeSignable()
	{
		$this->type = 'signable';
	}

	public function setPdfFile($pdfFile)
	{
		$this->pdfFile = $pdfFile;
	}

	public function getDocumentId()
	{
		return $this->documentId;
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

	public function getCreatedAt()
	{
		return new \Datetime('@'.$this->created);
	}

	public function getModifiedAt()
	{
		return new \Datetime('@'.$this->modified);
	}

	public function getCompletedAt()
	{
		return new \Datetime('@'.$this->completed);
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getOptions()
	{
		return $this->options;
	}
}
