<?php

namespace Penneo\SDK;

class CustomerBranding extends Entity
{
    protected static $relativeUrl = 'branding';

    protected $backgroundColor;
    protected $highlightColor;
    protected $textColor;
    protected $siteUrl;
    protected $logo;
    protected $imageId;

    protected $customer;

    public function __construct(?Customer $customer = null)
    {
        $this->customer = $customer;
    }

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

    public function getLogoUrl()
    {
        if ($this->logo === null) {
            // Fetch the logo url.
            $this->logo = self::findLinkedEntity($this->customer, Image::class, $this->imageId);
        }

        return $this->logo->getUrl();
    }
}
