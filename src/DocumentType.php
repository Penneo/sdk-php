<?php
namespace Penneo\SDK;

class DocumentType extends Entity
{
	protected static $relativeUrl = 'documenttype';

	protected $name;

	public function getName()
	{
		return $this->name;
	}
}
