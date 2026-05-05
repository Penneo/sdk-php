<?php

namespace Penneo\SDK;

class EmailSignature extends Entity
{
    protected static $relativeUrl = 'emailsignature';

    protected $html;

    protected $emailSignature;

    protected $customer;

    public function __construct(?Customer $customer = null)
    {
        $this->customer = $customer;
    }

    /**
     * Set html
     *
     * @param string $emailSignature
     * @return EmailSignature
     */
    public function setEmailSignature($emailSignature)
    {
        $this->emailSignature = $emailSignature;
        $this->setHtml($emailSignature);

        return $this;
    }

    public function getEmailSignature()
    {
        return $this->emailSignature;
    }

    /**
     * Set html
     *
     * @param string $html
     * @return EmailSignature
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get html
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }
}
