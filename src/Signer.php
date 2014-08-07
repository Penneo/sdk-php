<?php
namespace Penneo\SDK;

class Signer extends Entity
{
	protected static $propertyMapping = array(
		'create' => array('name','socialSecurityNumberPlain','vatin', 'onBehalfOf'),
		'update' => array('name','socialSecurityNumberPlain','vatin', 'onBehalfOf')
	);
	protected static $relativeUrl = 'signers';

	protected $name;
	protected $socialSecurityNumberPlain;
	protected $onBehalfOf;
	protected $vatin;
	
	protected $caseFile;

	public function __construct($parent)
	{
		$this->caseFile = null;
		if ($parent instanceof CaseFile) {
			$this->caseFile = $parent;
		} elseif ($parent instanceof SignatureLine) {
			$this->caseFile = $parent->getParent()->getCaseFile();
		}
	}

	public function getParent()
	{
		return $this->caseFile;
	}

	public function getSigningRequest()
	{
		$requests = parent::getLinkedEntities($this, 'Penneo\SDK\SigningRequest');
		return $requests[0];
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getSocialSecurityNumber()
	{
		return $this->socialSecurityNumberPlain;
	}

	public function setSocialSecurityNumber($ssn)
	{
		$this->socialSecurityNumberPlain = $ssn;
	}

	public function getVATIdentificationNumber()
	{
		return $this->vatin;
	}

	public function setVATIdentificationNumber($vatin)
	{
		$this->vatin = $vatin;
	}

	public function getOnBehalfOf()
	{
		return $this->onBehalfOf;
	}

	public function setOnBehalfOf($onBehalfOf)
	{
		$this->onBehalfOf = $onBehalfOf;
	}
}
