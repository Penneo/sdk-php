<?php
namespace Penneo\SDK;

use Penneo\SDK\Entity;

class Signer extends Entity
{
	protected static $propertyMapping = array(
		'create' => array('name','socialSecurityNumber', 'onBehalfOf'),
		'update' => array('name','socialSecurityNumber', 'onBehalfOf')
	);
	protected static $relativeUrl = 'signers';

	protected $name;
	protected $socialSecurityNumber;
	protected $onBehalfOf;

	public function getDocuments()
	{
		return parent::getLinked($this, 'Penneo\SDK\Document');
	}
}
