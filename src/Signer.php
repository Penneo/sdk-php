<?php
namespace Penneo\SDK;

use Penneo\SDK\ApiConnector;

class Signer
{
	protected $id;
	protected $name;
	protected $email;
	protected $onBehalfOf;
	protected $socialSecurityNumber;
	
	public function __construct($name, $email=null, $onBehalfOf=null, $ssn=null)
	{
		if (!$name) return false;
		$this->name = $name;
		$this->email = $email;
		$this->onBehalfOf = $onBehalfOf;
		$this->socialSecurityNumber = $ssn;
		
		$this->id = ApiConnector::createSigner($this->name, $this->onBehalfOf, $this->socialSecurityNumber);
		if (!$this->id) throw new \Exception('Penneo: Could not create the signer');
	}

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getOnBehalfOf()
	{
		return $this->onBehalfOf;
	}

	public function getSocialSecurityNumber()
	{
		return $this->socialSecurityNumber;
	}
}
