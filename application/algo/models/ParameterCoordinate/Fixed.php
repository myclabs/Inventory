<?php
/**
 * @author  matthieu.napoli
 * @author  cyril.perraud
 * @package Algo
 */
use Keyword\Application\Service\KeywordDTO;
use Techno\Domain\Family\Member;

/**
 * @package Algo
 */
class Algo_Model_ParameterCoordinate_Fixed extends Algo_Model_ParameterCoordinate
{

    /**
     * @var string|null
     */
    protected $refMemberKeyword;


    /**
     * {@inheritdoc}
     */
    public function getMemberKeyword(Algo_Model_InputSet $inputSet = null)
    {
        if (!$this->refMemberKeyword) {
            throw new Core_Exception_UndefinedAttribute("The member of the parameter coordinate is not defined");
        }
        return new KeywordDTO($this->refMemberKeyword);
    }

    /**
     * {@inheritdoc}
     */
    public function getMemberKeywordRef()
    {
        return $this->refMemberKeyword;
    }

    /**
     * @param Member $member
     */
    public function setMember(Member $member)
    {
        $this->refMemberKeyword = $member->getKeyword()->getRef();
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfiguration()
    {
        $errors = parent::checkConfiguration();

        if (!$this->refMemberKeyword) {
            $refDimension = $this->getDimension()->getMeaning()->getRef();
            $configError = new Algo_ConfigError(__('Algo', 'configControl', 'noMember',
                                        [
                                        'REF_DIMENSION' => $refDimension,
                                        'REF_ALGO' => $this->getAlgoParameter()->getRef()
                                        ]),
                                        true);
            $errors[] = $configError;
        } else {
            try {
                $this->getDimension()->getMember($this->getMemberKeyword());
            } catch (Core_Exception_NotFound $e) {
                $refDimension = $this->getDimension()->getMeaning()->getRef();
                $errors[] = new Algo_ConfigError(__('Algo', 'configControl', 'invalidMember',
                                        [
                                        'REF_DIMENSION' => $refDimension,
                                        'REF_ALGO' => $this->getAlgoParameter()->getRef(),
                                        'REF_MEMBER' => $this->refMemberKeyword
                                        ]), true);
            }
        }

        return $errors;
    }

}
