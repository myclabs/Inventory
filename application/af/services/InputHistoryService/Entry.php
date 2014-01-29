<?php
use AF\Domain\Component\Select\SelectOption;
use AF\Domain\Input\Input;
use User\Domain\User;

/**
 * @author matthieu.napoli
 */

/**
 * Input history entry
 */
class AF_Service_InputHistoryService_Entry
{
    /**
     * @var \AF\Domain\Input\Input
     */
    private $input;

    /**
     * @var DateTime
     */
    private $loggedAt;

    /**
     * @var string|bool|Calc_UnitValue|\AF\Domain\Component\Select\SelectOption|\AF\Domain\Component\Select\SelectOption[]
     */
    private $value;

    /**
     * @var User|null
     */
    private $author;

    public function __construct(Input $input, DateTime $loggedAt, $value, User $author = null)
    {
        $this->input = $input;
        $this->loggedAt = $loggedAt;
        $this->value = $value;
        $this->author = $author;
    }

    /**
     * @return \AF\Domain\Input\Input
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return DateTime
     */
    public function getLoggedAt()
    {
        return $this->loggedAt;
    }

    /**
     * @return string|bool|Calc_UnitValue|\AF\Domain\Component\Select\SelectOption|\AF\Domain\Component\Select\SelectOption[]
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return User|null
     */
    public function getAuthor()
    {
        return $this->author;
    }
}
