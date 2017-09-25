<?php
namespace Penneo\SDK;

class CopyRecipient extends Entity
{
    protected static $propertyMapping = array(
        'create' => array(
            'name',
            'email',
        ),
        'update' => array(
            'name',
            'email',
        )
    );
    protected static $relativeUrl = 'recipients';

    protected $name;
    protected $email;
    protected $caseFile;

    public function __construct($caseFile)
    {
        $this->caseFile = $caseFile;
    }

    public function getParent()
    {
        return $this->caseFile;
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
}
