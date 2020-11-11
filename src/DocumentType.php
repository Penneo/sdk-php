<?php

namespace Penneo\SDK;

class DocumentType extends Entity
{
    protected static $relativeUrl = 'documenttype';

    protected $name;
    protected $signerTypes;
    protected $lowerLimit;
    protected $upperLimit;
    protected $options;

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return SignerType[]
     */
    public function getSignerTypes()
    {
        return $this->signerTypes;
    }

    public function getLowerLimit()
    {
        return $this->lowerLimit;
    }

    public function getUpperLimit()
    {
        return $this->upperLimit;
    }

    public function getOptions()
    {
        return json_decode($this->options);
    }
}
