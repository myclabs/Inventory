<?php
/**
 * @author matthieu.napoli
 */

/**
 * Input history entry
 */
class AF_Service_InputHistoryService_Entry
{
    /**
     * @var AF_Model_Input
     */
    private $input;

    /**
     * @var DateTime
     */
    private $loggedAt;

    /**
     * @var string|bool|Calc_UnitValue|AF_Model_Component_Select_Option|AF_Model_Component_Select_Option[]
     */
    private $value;

    /**
     * @var User_Model_User
     */
    private $author;

    public function __construct(AF_Model_Input $input, DateTime $loggedAt, $value, User_Model_User $author)
    {
        $this->input = $input;
        $this->loggedAt = $loggedAt;
        $this->value = $value;
        $this->author = $author;
    }

    /**
     * @return AF_Model_Input
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
     * @return string|bool|Calc_UnitValue|AF_Model_Component_Select_Option|AF_Model_Component_Select_Option[]
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return User_Model_User
     */
    public function getAuthor()
    {
        return $this->author;
    }
}
