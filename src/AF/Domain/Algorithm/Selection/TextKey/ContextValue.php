<?php
use AF\Domain\Algorithm\InputSet;

/**
 * Algo de sélection recherchant une valeur par une clé textuelle.
 *
 * Utilisé pour les "injections" de valeurs arbitraires dans la saisie d'AF (par Orga).
 *
 * @author  matthieu.napoli
 */
class Algo_Model_Selection_TextKey_ContextValue extends Algo_Model_Selection_TextKey
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $defaultValue;

    /**
     * Execute
     * @param InputSet $inputSet
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_InvalidArgument
     * @return string
     */
    public function execute(InputSet $inputSet)
    {
        $value = $inputSet->getContextValue($this->name);

        if ($value === null) {
            return $this->defaultValue;
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }
}
