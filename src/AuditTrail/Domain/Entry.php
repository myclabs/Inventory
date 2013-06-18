<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Domain;

use AuditTrail\Domain\Context\Context;
use DateTime;

/**
 * Audit trail entry
 */
class Entry
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $eventName;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param string  $eventName
     * @param Context $context
     */
    public function __construct($eventName, Context $context)
    {
        $this->date = new DateTime();
        $this->eventName = $eventName;
        $this->context = $context;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }
}
