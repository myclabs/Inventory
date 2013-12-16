<?php

/**
 * @author matthieu.napoli
 * @author cyril.perraud
 */
class Algo_Model_ParameterCoordinate_Fixed extends Algo_Model_ParameterCoordinate
{
    /**
     * @var string|null
     */
    protected $idMember;


    /**
     * {@inheritdoc}
     */
    public function getMember(Algo_Model_InputSet $inputSet = null)
    {
        if (! $this->idMember) {
            throw new Core_Exception_UndefinedAttribute("The member of the parameter coordinate is not defined");
        }
        return $this->idMember;
    }

    /**
     * @param string $memberId
     */
    public function setMember($memberId)
    {
        $this->idMember = $memberId;
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfiguration()
    {
        $errors = parent::checkConfiguration();

        if (!$this->idMember) {
            $configError = new Algo_ConfigError(__('Algo', 'configControl', 'noMember', [
                'REF_DIMENSION' => $this->getDimension(),
                'REF_ALGO' => $this->getAlgoParameter()->getRef()
            ]), true);
            $errors[] = $configError;
        } else {
            try {
                $this->getDimension()->getMember($this->getMember());
            } catch (Core_Exception_NotFound $e) {
                $refDimension = $this->getDimension()->getRef();
                $errors[] = new Algo_ConfigError(__('Algo', 'configControl', 'invalidMember', [
                    'REF_DIMENSION' => $refDimension,
                    'REF_ALGO' => $this->getAlgoParameter()->getRef(),
                    'REF_MEMBER' => $this->idMember
                ]), true);
            }
        }

        return $errors;
    }
}
