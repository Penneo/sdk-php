<?php

namespace Penneo\SDK;

class SignerType extends Entity
{
    protected static $relativeUrl = 'signertypes';
    protected $role;
    protected $upperLimit;
    protected $lowerLimit;
    protected $signOrder;
    protected $conditions;

    public function getName()
    {
        return $this->role;
    }

    public function getLowerLimit()
    {
        return $this->lowerLimit;
    }

    public function getUpperLimit()
    {
        return $this->upperLimit;
    }

    public function getSignOrder()
    {
        return $this->signOrder;
    }

    public function getConditions()
    {
        return $this->conditions;
    }
}
