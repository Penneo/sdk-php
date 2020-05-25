<?php
namespace Penneo\SDK;

class Customer extends Entity
{
    const STATUS_DEMO        =  0;
    const STATUS_PAYING      =  1;
    const STATUS_CANCELLED   =  2;
    const STATUS_UNKNOWN     = 99;

    protected static $relativeUrl = 'customers';

    protected $name;
    protected $address;
    protected $zip;
    protected $city;
    protected $active;
    protected $deactivateAt;
    protected $vatin;
    protected $status;
    protected $accessControl;

    protected $branding;
    protected $emailSignature;

    public function getBranding()
    {
        if ($this->branding === null) {
            // Try to retrieve the branding from the backend.
            $url = self::$relativeUrl.'/'.$this->getId().'/branding';
            $this->branding = self::getEntity('Penneo\SDK\CustomerBranding', $url, $this);
        }

        return $this->branding;
    }

    public function getEmailSignature()
    {
        if ($this->emailSignature === null) {
            // Try to retrieve the email signature from the backend.
            $url = self::$relativeUrl.'/'.$this->getId().'/email-signature';
            $this->emailSignature = self::getEntity('Penneo\SDK\EmailSignature', $url, $this);
        }

        return $this->emailSignature;
    }

    /**
     * @param EmailSignature $emailSignature
     */
    public function setEmailSignature(EmailSignature $emailSignature)
    {
        $this->emailSignature = $emailSignature;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Customer
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Customer
     */
    public function setAddress($address)
    {
        $this->address = $address;
    
        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set zip
     *
     * @param string $zip
     * @return Customer
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    
        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Customer
     */
    public function setCity($city)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Customer
     */
    public function setActive($active)
    {
        $this->active = $active;
    
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set deactivateAt
     *
     * @param \DateTime $deactivateAt
     * @return Customer
     */
    public function setDeactivateAt($deactivateAt)
    {
        $this->deactivateAt = $deactivateAt->getTimestamp();
    
        return $this;
    }

    /**
     * Get deactivateAt
     *
     * @return \DateTime
     */
    public function getDeactivateAt()
    {
        return new \DateTime('@'.$this->deactivateAt);
    }

    /**
     * Set vatin
     *
     * @param string $vatin
     * @return Customer
     */
    public function setVatin($vatin)
    {
        $this->vatin = $vatin;
    
        return $this;
    }

    /**
     * Get vatin
     *
     * @return string
     */
    public function getVatin()
    {
        return $this->vatin;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        switch ($this->status) {
            case self::STATUS_DEMO:
                return 'demo';
            case self::STATUS_PAYING:
                return 'paying';
            case self::STATUS_CANCELLED:
                return 'cancelled';
        }

        return 'unknown';
    }

    /**
     * Set accessControl
     *
     * @param boolean $accessControl
     * @return Customer
     */
    public function setAccessControl($accessControl)
    {
        $this->accessControl = $accessControl;

        return $this;
    }

    /**
     * Get accessControl
     *
     * @return boolean
     */
    public function getAccessControl()
    {
        return $this->accessControl;
    }
}
