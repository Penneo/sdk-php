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
	protected $signer;
	protected $role;
	protected $conditions;
	protected $signOrder = 0;

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
}
