<?php

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
    protected $key;

    /**
     * @var string
     */
    protected $defaultValue;

    /**
     * Execute
     * @param Algo_Model_InputSet $inputSet
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_InvalidArgument
     * @return string
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        $value = $inputSet->getContextValue($this->key);

        if ($value === null) {
            return $this->defaultValue;
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
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
