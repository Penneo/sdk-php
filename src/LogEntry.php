<?php
namespace Penneo\SDK;

class LogEntry extends Entity
{
    protected static $relativeUrl = 'log';
    protected $eventTime;
    protected $eventType;

    public function getEventType()
    {
        return $this->eventType;
    }
    
    public function getEventTime()
    {
        return new \DateTime('@'.$this->eventTime);
    }
}
