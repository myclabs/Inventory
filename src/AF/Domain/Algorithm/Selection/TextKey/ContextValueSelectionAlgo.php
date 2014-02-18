<?php

namespace AF\Domain\Algorithm\Selection\TextKey;

use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Selection\TextKeySelectionAlgo;

/**
 * Algo de sélection recherchant une valeur par une clé textuelle.
 *
 * Utilisé pour les "injections" de valeurs arbitraires dans la saisie d'AF (par Orga).
 *
 * @author matthieu.napoli
 */
class ContextValueSelectionAlgo extends TextKeySelectionAlgo
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
     * {@inheritdoc}
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
