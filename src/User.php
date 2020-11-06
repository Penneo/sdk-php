<?php
namespace Penneo\SDK;

class User extends Entity
{
    protected static $relativeUrl = 'users';

    protected $fullName;
    protected $email;

    public static function getActiveUser()
    {
        $url = 'user';
        return self::getEntity('Penneo\SDK\User', $url, null);
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = trim($fullName);
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return strtolower(trim($this->email));
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = strtolower(trim($email));

        return $this;
    }
}
