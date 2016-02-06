<?php

namespace Penneo\SDK\Tests;

use Penneo\SDK\User;

use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class UserContext extends AbstractContext
{
    protected $loggedInUser;
    protected $userData = [];

    /**
     * @Given user exist:
     */
    public function userExist(TableNode $table)
    {
        foreach ($table->getHash() as $hash) {
            $this->userData[$hash['id']] = json_encode($hash);
        }
    }

    /**
     * @Given logged in user is :userId
     */
    public function loggedInUserIs($userId)
    {
        $this->loggedInUser = $userId;
    }

    /**
     * @When I retrieve the logged in user
     */
    public function iRetrieveLoggedInUser()
    {
        $this->prepareGetResponse($this->userData[$this->loggedInUser]);

        $this->setEntity(User::getActiveUser());
        
        $this->flushServer();
    }
}
