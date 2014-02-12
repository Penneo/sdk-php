<?php
namespace Penneo\SDK;

use Penneo\SDK\ApiConnector;

class CaseFile
{
	protected $id;
	
	public function __construct($title, $metaData=null)
	{
		$this->id = ApiConnector::createCaseFile($title, $metaData);
		if (!$this->id) throw new \Exception('Penneo: Could not create the case file');
	}

	public function getId()
	{
		return $this->id;
	}
}