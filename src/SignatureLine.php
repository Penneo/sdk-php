<?php
namespace Penneo\SDK;

use Penneo\SDK\Entity;

class SignatureLine extends Entity
{
	protected static $propertyMapping = array(
		'create' => array('role','signerId' => 'signer->getId'),
		'update' => array('role')
	);
	protected static $relativeUrl = 'signaturelines';

	protected $signer;
	protected $role;
	protected $status;
	protected $signTime;

	public function getSigner()
	{
		return $this->signer;
	}
	
	public function getRole()
	{
		return $this->role;
	}
	
	public function getStatus()
	{
		return $this->status;
	}
	
	public function getSignTime()
	{
		return $this->signTime;
	}
}
