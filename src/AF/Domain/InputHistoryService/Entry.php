<?php

namespace AF\Domain\InputHistoryService;

use AF\Domain\Component\Select\SelectOption;
use AF\Domain\Input\Input;
use Calc_UnitValue;
use DateTime;
use User\Domain\User;

/**
 * Input history entry.
 *
 * @author matthieu.napoli
 */
class Entry
{
    /**
     * @var Input
     */
    private $input;

    /**
     * @var DateTime
     */
    private $loggedAt;

    /**
     * @var string|bool|Calc_UnitValue|SelectOption|SelectOption[]
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
     * @return Input
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
     * @return string|bool|Calc_UnitValue|SelectOption|SelectOption[]
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
