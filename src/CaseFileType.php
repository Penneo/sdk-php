<?php
namespace Penneo\SDK;

class CaseFileType extends Entity
{
	protected static $relativeUrl = 'casefiletype';

	protected $name;

	public function getName()
	{
		return $this->name;
	}
}
