<?php
namespace Penneo\SDK;

use Penneo\SDK\Entity;

class Validation extends Entity
{
	protected static $propertyMapping = array(
		'create' => array('title','name','email','emailSubject','emailText'),
		'update' => array('title','name','email','emailSubject','emailText')
	);
	protected static $relativeUrl = 'validations';

	protected $title;
	protected $name;
	protected $email;
	protected $emailSubject;
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

	public function getTitle()
	{
		return $this->title;
	}
	
	public function setTitle($title)
	{
		$this->title = $title;
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
	
	public function getEmailSubject()
	{
		return $this->emailSubject;
	}
	
	public function setEmailSubject($emailSubject)
	{
		$this->emailSubject = $emailSubject;
	}
	
	public function getEmailText()
	{
		return $this->emailText;
	}
	
	public function setEmailText($emailText)
	{
		$this->emailText = $emailText;
	}
	
	public function getStatus()
	{
		switch ($this->status) {
			case 0:
				return 'new';
			case 1:
				return 'pending';
			case 2:
				return 'undeliverable';
			case 3:
				return 'deleted';
			case 4:
				return 'ready';
			case 5:
				return 'completed';
		}
	
		return 'new';
	}
}
