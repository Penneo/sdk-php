<?php

namespace Penneo\SDK;

class MessageTemplate extends Entity
{
    protected static $propertyMapping = array(
        'create' => array(
            'title',
            'subject',
            'message'
        ),
        'update' => array(
            'title',
            'subject',
            'message'
        )
    );
    protected static $relativeUrl = 'casefile/message/templates';

    protected $title;
    protected $subject;
    protected $message;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message): void
    {
        $this->message = $message;
    }
}
