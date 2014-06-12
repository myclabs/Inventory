<?php

namespace AF\Domain\Component;

use AF\Domain\InputSet\InputSet;
use AF\Domain\Input\TextFieldInput;

/**
 * Gestion des champs de type texte.
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 */
class TextField extends Field
{
    const TYPE_SHORT = 1;
    const TYPE_LONG = 2;

    protected $type = self::TYPE_SHORT;

    /**
     * Indique si le champ est requis ou non.
     * @var bool
     */
    protected $required = true;


    /**
     * @param int $type
     */
    public function __construct($type)
    {
        parent::__construct();
        $this->setType($type);
    }

    /**
     * {@inheritdoc}
     */
    public function initializeNewInput(InputSet $inputSet)
    {
        $input = $inputSet->getInputForComponent($this);

        if ($input === null) {
            $input = new TextFieldInput($inputSet, $this);
            $inputSet->setInputForComponent($this, $input);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRequiredFields(InputSet $inputSet = null)
    {
        if (!$this->getRequired()) {
            return 0;
        }

        if ($inputSet) {
            $input = $inputSet->getInputForComponent($this);
            // Si la saisie est cachée : 0 champs requis
            if ($input && $input->isHidden()) {
                return 0;
            }
        }

        return 1;
    }

    /**
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
