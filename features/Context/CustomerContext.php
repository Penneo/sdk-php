<?php

namespace Penneo\SDK\Tests;

use Penneo\SDK\Customer;

use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class CustomerContext extends AbstractContext
{
    protected $customers;
    protected $branding;

    protected $customerData = [];
    protected $emailSignatureData = [];
    protected $brandingData = [];
    protected $imageData = [];

    protected $logoUrl;

    /**
     * @Given customer exist:
     */
    public function customerExist(TableNode $table)
    {
        foreach ($table->getHash() as $hash) {
            $this->customerData[$hash['id']] = json_encode($hash);
        }
    }

    /**
     * @Given customer :id has email signature:
     */
    public function emailSignatureExist($id, TableNode $table)
    {
        foreach ($table->getHash() as $hash) {
            $this->emailSignatureData[$id] = json_encode($hash);
        }
    }

    /**
     * @Given customer :id has branding:
     */
    public function customerHasBranding($id, TableNode $table)
    {
        foreach ($table->getHash() as $hash) {
            $this->brandingData[$id] = json_encode($hash);
        }
    }

    /**
     * @Given image exist:
     */
    public function imageExist(TableNode $table)
    {
        foreach ($table->getHash() as $hash) {
            $this->imageData[$hash['id']] = json_encode($hash);
        }
    }

    /**
     * @When I retrieve customer :id
     */
    public function iRetrieveCustomer($id)
    {
        $this->prepareGetResponse($this->customerData[$id]);

        $this->customers[$id] = Customer::find($id);
        
        $this->setEntity($this->customers[$id]);

        $this->flushServer();
    }

    /**
     * @When I retrieve customer :id email signature
     */
    public function iRetrieveCustomerEmailSignature($id)
    {
        $customer = $this->customers[$id];

        $this->prepareGetResponse($this->emailSignatureData[$id]);

        $this->setEntity($customer->getEmailSignature());
        
        $this->flushServer();
    }

    /**
     * @When I retrieve customer :id branding
     */
    public function iRetrieveCustomerBranding($id)
    {
        $customer = $this->customers[$id];

        $this->prepareGetResponse($this->brandingData[$id]);

        $this->branding = $customer->getBranding();
        
        $this->setEntity($this->branding);

        $this->flushServer();
    }

    /**
     * @When I retrieve branding logo
     */
    public function iRetrieveBrandingLogo()
    {
        $imageId = $this->branding->getImageId();
        $this->prepareGetResponse($this->imageData[$imageId]);

        $this->logoUrl = $this->branding->getLogoUrl();
        
        $this->flushServer();
    }

    /**
     * @Then branding logo url should be :url
     */
    public function brandingLogoUrlShouldBe($url)
    {
        $this->assertEquals($url, $this->logoUrl);
    }
}
