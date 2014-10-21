<?php
namespace Penneo\SDK;

class SignatureLine extends Entity
{
	protected static $propertyMapping = array(
		'create' => array('role','conditions','signOrder'),
		'update' => array('role','conditions','signOrder')
	);
	protected static $relativeUrl = 'signaturelines';

	protected $document;
	protected $signer = null;
	protected $role;
	protected $conditions;
	protected $signOrder = 0;
	protected $signedAt;
	
	protected $signerId = null;

	public function __construct(Document $document)
	{
		$this->document = $document;
	}

	public function getParent()
	{
		return $this->document;
	}

	public function getSigner()
	{
		if ($this->signer == null) {
			if ($this->signerId !== null) {
				// Retrieve signer from signer id
				$this->signer = $this->document->getCaseFile()->findSigner($this->signerId);
			} else {
				// Retrieve signer from API
				$signers = parent::getLinkedEntities($this, 'Penneo\SDK\Signer');
				$this->signer = $signers[0];
			}
		}

		return $this->signer;
	}
	
	public function setSigner(Signer $signer)
	{
		$this->signer = $signer;
		return parent::linkEntity($this, $signer);
	}
	
	public function getRole()
	{
		return $this->role;
	}
	
	public function setRole($role)
	{
		$this->role = $role;
	}
	
	public function getConditions()
	{
		return $this->conditions;
	}
	
	public function setConditions($conditions)
	{
		$this->conditions = $conditions;
	}
	
	public function getSignOrder()
	{
		return $this->signOrder;
	}
	
	public function setSignOrder($signOrder)
	{
		$this->signOrder = $signOrder;
	}
	
	public function getSignedAt()
	{
		if ($this->signedAt) {
			return new \DateTime('@'.$this->signedAt);
		}
		return null;
	}
}
