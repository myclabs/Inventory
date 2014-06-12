<?php

namespace AF\Domain\Component\Select;

use AF\Domain\Input\Select\SelectSingleInput;
use AF\Domain\Algorithm\Selection\TextKey\InputSelectionAlgo;
use AF\Domain\InputSet\InputSet;
use Core_Exception_NotFound;
use AF\Domain\Component\Select;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SelectSingle extends Select
{
    /**
     * Constante associée à l'attribut 'style'.
     * Correspond à une liste déroulante à choix unique.
     * @var int
     */
    const TYPE_LIST = 1;

    /**
     * Constante associée à l'attribut 'style'.
     * Correspond à un bouton radio à choix unique.
     * @var int
     */
    const TYPE_RADIO = 2;

    /**
     * Identifiant of the default option selectioned.
     * @var SelectOption|null
     */
    protected $defaultValue;

    /**
     * Indicate if the field is a scroll list or a radioList.
     * @var int
     */
    protected $type = self::TYPE_LIST;


    /**
     * {@inheritdoc}
     */
    public function initializeNewInput(InputSet $inputSet)
    {
        $input = $inputSet->getInputForComponent($this);

        if ($input === null) {
            $input = new SelectSingleInput($inputSet, $this);
            $inputSet->setInputForComponent($this, $input);
        }

        $input->setValue($this->defaultValue);
    }

    /**
     * {@inheritdoc}
     */
    public function setRef($ref)
    {
        $oldRef = $this->ref;
        parent::setRef($ref);
        // Modifie également le ref de l'algo associé
        try {
            $af = $this->getAf();
            if ($af) {
                $algo = $af->getAlgoByRef($oldRef);
                if ($algo instanceof InputSelectionAlgo) {
                    $algo->setInputRef($ref);
                    $algo->setRef($ref);
                    $algo->save();
                }
            }
        } catch (Core_Exception_NotFound $e) {
        }
    }

    /**
     * @return SelectOption|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param SelectOption|null $defaultValue
     */
    public function setDefaultValue(SelectOption $defaultValue = null)
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * Get the style attribute.
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the style attribute.
     * @param int $style
     */
    public function setType($style)
    {
        $this->type = $style;
    }
}
