<?php

namespace Penneo\SDK;

class CustomerBranding extends Entity
{
    protected static $relativeUrl = 'branding';

    protected $backgroundColor;
    protected $highlightColor;
    protected $textColor;
    protected $siteUrl;
    /** @var Image|null */
    protected $logo;
    protected $imageId;

    protected $customer;

    public function __construct(?Customer $customer = null)
    {
        $this->customer = $customer;
    }

    /**
     * @return Customer|null
     */
    public function getParent()
    {
        return $this->customer;
    }

    /**
     * Set backgroundColor
     *
     * @param string $backgroundColor
     * @return CustomerBranding
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * Get backgroundColor
     *
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * Set highlightColor
     *
     * @param string $highlightColor
     * @return CustomerBranding
     */
    public function setHighlightColor($highlightColor)
    {
        $this->highlightColor = $highlightColor;

        return $this;
    }

    /**
     * Get highlightColor
     *
     * @return string
     */
    public function getHighlightColor()
    {
        return $this->highlightColor;
    }

    /**
     * Set textColor
     *
     * @param string $textColor
     * @return CustomerBranding
     */
    public function setTextColor($textColor)
    {
        $this->textColor = $textColor;

        return $this;
    }

    /**
     * Get textColor
     *
     * @return string
     */
    public function getTextColor()
    {
        return $this->textColor;
    }

    /**
     * Set siteUrl
     *
     * @param string $siteUrl
     * @return CustomerBranding
     */
    public function setSiteUrl($siteUrl)
    {
        $this->siteUrl = $siteUrl;

        return $this;
    }

    /**
     * Get siteUrl
     *
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

    public function getImageId()
    {
        return $this->imageId;
    }

    /**
     * Absolute URL of the linked logo image, if {@see getImageId()} is set and the logo can be resolved.
     */
    public function getLogoUrl(): ?string
    {
        if ($this->logo === null) {
            $customer = $this->customer;
            if ($customer === null) {
                return null;
            }
            $this->logo = self::findLinkedEntity($customer, Image::class, $this->imageId);
        }

        if (!$this->logo instanceof Image) {
            return null;
        }

        return $this->logo->getUrl();
    }
}
