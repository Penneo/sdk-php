<?php
namespace Penneo\SDK;

class SignerType extends Entity
{
	protected static $relativeUrl = 'signertypes';
	protected $role;

	public function getName()
	{
		return $this->role;
	}
}
