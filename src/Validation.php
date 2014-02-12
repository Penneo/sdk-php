<?php
namespace Penneo\SDK;

use Penneo\SDK\ApiConnector;

class Validation
{
	protected $id;
	protected $name;
	protected $email;
	protected $message;
	
	public function __construct($name, $email = null, $message = null)
	{
		if (!$name) return false;
		$this->name = $name;
		$this->email = $email;
		$this->message = $message;
		
		$this->id = ApiConnector::createValidation($this->name, $this->email, $this->message);
		if (!$this->id) throw new Exception('Penneo: Could not create the validation');
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

	public function getMessage()
	{
		return $this->message;
	}

	}
