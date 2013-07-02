<?php
/**
 * @author     matthieu.napoli
 * @author     hugo.charbonnier
 * @author     thibaud.rolland
 * @package    AF
 * @subpackage Form
 */

/**
 * Classe abstraite AF_Model_Form_Numeric.
 * Gestion des champs de type Numeric (champs de saisie).
 * @package    AF
 * @subpackage Form
 */
class AF_Model_Component_Numeric extends AF_Model_Component_Field
{

    /**
     * L'unité associée à la valeur numérique.
     * @var Unit_API
     */
    protected $unit;

    /**
     * Flag indiquant si le champs de saisie doit être associé à un champs de saisie d'incertitude.
     * @var boolean
     */
    protected $withUncertainty = true;

    /**
     * Valeur par défaut du champ.
     * @var Calc_Value
     */
    protected $defaultValue;

    /**
     * Indique si on doit afficher ou non un rappel de la valeur par défaut.
     * @var bool
     */
    protected $defaultValueReminder = false;

    /**
     * Indique si le champ est requis ou non.
     * @var bool
     */
    protected $required = true;


    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultValue = new Calc_Value();
    }

    /**
     * {@inheritdoc}
     */
    public function getUIElement(AF_GenerationHelper $generationHelper)
    {
        $uiElement = new UI_Form_Element_Pattern_Value($this->ref, $this->withUncertainty);
        $uiElement->setLabel($this->label);
        $uiElement->getElement()->help = $this->help;
        $uiElement->setRequired($this->getRequired());
        if ($generationHelper->isReadOnly()) {
            $uiElement->getElement()->setReadOnly();
        }
        // Remplit avec la valeur saisie
        $input = null;
        if ($generationHelper->getInputSet()) {
            /** @var $input AF_Model_Input_Numeric */
            $input = $generationHelper->getInputSet()->getInputForComponent($this);
        }
        if ($input) {
            $uiElement->getElement()->disabled = $input->isDisabled();
            $uiElement->getElement()->hidden = $input->isHidden();
            // Valeur
            $uiElement->setValue([$input->getValue()->getDigitalValue(), $input->getValue()->getRelativeUncertainty()]);
        } else {
            $uiElement->getElement()->disabled = !$this->enabled;
            $uiElement->getElement()->hidden = !$this->visible;
            // Valeur
            $uiElement->setValue([$this->defaultValue->getDigitalValue(), $this->defaultValue->getRelativeUncertainty()]);
        }
        // Unité
        if ($this->unit !== null) {
            $uiElement->getElement()->addSuffix($this->unit->getSymbol());
        }
        // Actions
        foreach ($this->actions as $action) {
            $uiElement->getElement()->addAction($generationHelper->getUIAction($action));
        }
        // Rappel de la valeur par défaut
        if ($this->getDefaultValueReminder()) {
            $locale = Core_Locale::loadDefault();
            $uiElement->setDescription(sprintf("Valeur par défaut : %s %s ± %d%%",
                                               $locale->formatNumber((float) $this->defaultValue->getDigitalValue()),
                                               $this->unit->getSymbol(),
                                               (float) $this->defaultValue->getRelativeUncertainty()));
        }
        return $uiElement;
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();
        // On vérifie que l'unité associée au champs numérique est valide.
        $unit = $this->unit;
        try {
            $unit->getNormalizedUnit();
        } catch (Core_Exception_NotFound $e) {
            $errors[] = new AF_ConfigError("L'unité '{$unit->getRef()}' associée au champ '$this->ref' n'existe pas.",
                                           true, $this->getAf());
        }
        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRequiredFields(AF_Model_InputSet $inputSet = null)
    {
        if ($inputSet) {
            $input = $inputSet->getInputForComponent($this);
            // Si la saisie est cachée ou non obligatoire : 0 champs requis
            if ($input && ($input->isHidden() || !$this->getRequired())) {
                return 0;
            }
        }
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function setRef($ref)
    {
        $oldRef = $this->ref;
        parent::setRef($ref);
        // Modifie également le ref de l'algo associé et l'association entre eux
        try {
            $af = $this->getAf();
            if ($af) {
                $algo = $af->getAlgoByRef($oldRef);
                if ($algo instanceof Algo_Model_Numeric_Input) {
                    $algo->setInputRef($ref);
                    $algo->setRef($ref);
                    $algo->save();
                }
            }
        } catch (Core_Exception_NotFound $e) {
        }
    }

    /**
     * @return Unit_API
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param Unit_API $unit
     */
    public function setUnit(Unit_API $unit)
    {
        $this->unit = $unit;
        // Modifie également l'unité de l'algo associé et l'association entre eux
        try {
            $af = $this->getAf();
            if ($af) {
                $algo = $af->getAlgoByRef($this->getRef());
                if ($algo instanceof Algo_Model_Numeric_Input) {
                    $algo->setUnit($unit);
                    $algo->save();
                }
            }
        } catch (Core_Exception_NotFound $e) {
        }
    }

    /**
     * @return bool
     */
    public function getWithUncertainty()
    {
        return $this->withUncertainty;
    }

    /**
     * @param bool $withUncertainty
     */
    public function setWithUncertainty($withUncertainty)
    {
        $this->withUncertainty = (bool) $withUncertainty;
    }

    /**
     * @param Calc_Value $value
     */
    public function setDefaultValue(Calc_Value $value)
    {
        $this->defaultValue = $value;
    }

    /**
     * @return Calc_Value
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Get the defaultValueReminder attribute.
     * @return bool
     */
    public function getDefaultValueReminder()
    {
        return $this->defaultValueReminder;
    }

    /**
     * @param bool $defaultValueReminder
     */
    public function setDefaultValueReminder($defaultValueReminder)
    {
        $this->defaultValueReminder = (bool) $defaultValueReminder;
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

}
