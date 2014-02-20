<?php
namespace Penneo\SDK;

use Penneo\SDK\Entity;

class Validation extends Entity
{
	protected static $propertyMapping = array(
		'create' => array('name','email','emailText'),
		'update' => array('name','email','emailText')
	);
	protected static $relativeUrl = 'validations';

	protected $name;
	protected $email;
	protected $emailText;
	protected $status;

	public function getPdf()
	{
		$data = parent::getAssets($this, 'pdf');
		return base64_decode($data[0]);
	}
	
	public function getLink()
	{
		$data = parent::getAssets($this, 'link');
		return $data[0];
	}
	
	public function send()
	{
		return parent::callAction($this, 'send');
	}

	public function getName()
	{
		return $this->name;
	}
	
	public function setName($name)
	{
		$this->name = $name;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	public function setEmail($email)
	{
		$this->email = $email;
	}
	
	public function getStatus()
	{
		return $this->status;
	}
}
