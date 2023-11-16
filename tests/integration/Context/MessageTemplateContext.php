<?php

namespace Penneo\SDK\Tests\Integration;

use Behat\Gherkin\Node\TableNode;
use Penneo\SDK\MessageTemplate;

/**
 * Defines application features from the specific context.
 */
class MessageTemplateContext extends AbstractContext
{
    protected $newTemplate;
    protected $newTemplateData;
    protected $templates = [];
    protected $templateData = [];

    /**
     * @Given message templates exist:
     */
    public function messageTemplatesExist(TableNode $table)
    {
        foreach ($table->getHash() as $hash) {
            $this->templateData[$hash['id']] = json_encode($hash);
        }
    }

    /**
     * @Given I have a message template:
     */
    public function iHaveMessageTemplate(TableNode $table)
    {
        $this->newTemplateData = $table->getHash()[0];
        $this->newTemplate = new MessageTemplate();
        $this->newTemplate->__fromArray($this->newTemplateData);

        // Set a new id for the entity if it is persisted
        $this->newTemplateData['id'] = rand(10, 50);
    }

    /**
     * @When I retrieve message template :id
     */
    public function iRetrieveMessageTemplate($id)
    {
        $this->prepareGetResponse($this->templateData[$id]);

        $this->templates[$id] = MessageTemplate::find($id);
        $this->setEntity($this->templates[$id]);

        $this->flushServer();
    }

    /**
     * @When /^I persist (?:|the )message template\s?(\d+)?$/i
     */
    public function iPersistMessageTemplate($id = null)
    {
        if ($id === null) {
            $this->preparePostResponse(json_encode($this->newTemplateData));
            $template = $this->newTemplate;
        } else {
            $this->preparePutResponse();
            $template = $this->templates[$id];
        }

        MessageTemplate::persist($template);

        $this->flushServer();
    }

    /**
     * @When I delete the message template :id
     */
    public function iDeleteMessageTemplate($id)
    {
        $template = $this->templates[$id];
        $this->prepareDeleteResponse();

        $this->setEntity($template);
        MessageTemplate::delete($template);

        $this->flushServer();
    }
}
