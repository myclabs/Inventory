<?php

namespace AF\Domain\Component;

use AF\Domain\InputSet\InputSet;
use AF\Domain\AFConfigurationError;
use AF\Domain\AFGenerationHelper;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\Algorithm\Numeric\NumericInputAlgo;
use Calc_Value;
use Core_Exception_NotFound;
use Core_Locale;
use UI_Form_Element_Option;
use UI_Form_Element_Pattern_Percent;
use UI_Form_Element_Pattern_Value;
use UI_Form_Element_Select;
use Unit\UnitAPI;
use Zend_Form_Element;

/**
 * Gestion des champs de type Numeric (champs de saisie).
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 */
class NumericField extends Field
{
    /**
     * L'unité associée à la valeur numérique.
     * @var UnitAPI
     */
    protected $unit;

    /**
     * Est-ce que l'utilisateur peut choisir l'unité.
     * @var bool
     */
    protected $unitSelection = true;

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


    public function __construct()
    {
        parent::__construct();
        $this->defaultValue = new Calc_Value();
    }

    /**
     * {@inheritdoc}
     */
    public function getUIElement(AFGenerationHelper $generationHelper)
    {
        $locale = Core_Locale::loadDefault();

        $uiElement = new UI_Form_Element_Pattern_Value($this->ref, false);
        $uiElement->setLabel($this->label);
        $uiElement->getElement()->help = $this->help;
        $uiElement->setRequired($this->getRequired());
        if ($generationHelper->isReadOnly()) {
            $uiElement->getElement()->setReadOnly();
        }
        // Remplit avec la valeur saisie
        $input = null;
        if ($generationHelper->getInputSet()) {
            /** @var $input NumericFieldInput */
            $input = $generationHelper->getInputSet()->getInputForComponent($this);
        }
        if ($input) {
            $uiElement->getElement()->disabled = $input->isDisabled();
            $uiElement->getElement()->hidden = $input->isHidden();
            $value = $input->getValue();
            $selectedUnit = $input->getValue()->getUnit();
            // Si l'unité du champ n'est plus compatible avec l'ancienne saisie
            if (!$selectedUnit->isEquivalent($this->unit->getRef())) {
                // Ignore l'ancienne saisie
                $value = $this->defaultValue;
                $selectedUnit = $this->unit;
            }
            // Valeur
            if ($value) {
                // Si on ne peut plus choisir l'unité, et qu'une valeur a été saisie dans une unité différente
                if ((!$this->hasUnitSelection()) && ($value->getUnit() != $this->unit)) {
                    // On convertit dans l'unité par défaut
                    $value = $value->convertTo($this->unit);
                }
                $uiElement->setValue($locale->formatNumberForInput($value->getDigitalValue()));
            }
            // Unité
            $uiElement->getElement()->addElement($this->getUnitComponent($this->unit, $selectedUnit));
            // Incertitude
            if ($this->withUncertainty) {
                $uiUncertaintyElement = new UI_Form_Element_Pattern_Percent('percent' . $this->ref);
                if ($value) {
                    $uiUncertaintyElement->setValue($locale->formatNumberForInput($value->getRelativeUncertainty()));
                }
                $uiElement->getElement()->addElement($uiUncertaintyElement);
            }
            // Historique de la valeur
            $uiElement->getElement()->addElement($this->getHistoryComponent($input));
        } else {
            $uiElement->getElement()->disabled = !$this->enabled;
            $uiElement->getElement()->hidden = !$this->visible;
            // Valeur
            $uiElement->setValue($locale->formatNumberForInput($this->defaultValue->getDigitalValue()));
            // Unité
            if ($this->unit !== null) {
                $uiElement->getElement()->addElement($this->getUnitComponent($this->unit, $this->unit));
            }
            // Incertitude
            if ($this->withUncertainty) {
                $uiUncertaintyElement = new UI_Form_Element_Pattern_Percent('percent' . $this->ref);
                $uiUncertaintyElement->setValue(
                    $locale->formatNumberForInput($this->defaultValue->getRelativeUncertainty())
                );
                $uiElement->getElement()->addElement($uiUncertaintyElement);
            }
        }
        // Actions
        foreach ($this->actions as $action) {
            $uiElement->getElement()->addAction($generationHelper->getUIAction($action));
        }
        // Rappel de la valeur par défaut
        if ($this->getDefaultValueReminder()) {
            $locale = Core_Locale::loadDefault();
            $uiElement->setDescription(sprintf(
                "Valeur par défaut : %s %s ± %d%%",
                $locale->formatNumber((float) $this->defaultValue->getDigitalValue()),
                $this->unit->getSymbol(),
                (float) $this->defaultValue->getRelativeUncertainty()
            ));
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
        if (! $this->unit->exists()) {
            $errors[] = new AFConfigurationError(
                "L'unité '{$this->unit->getRef()}' associée au champ '$this->ref' n'existe pas.",
                true,
                $this->getAf()
            );
        }
        return $errors;
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
                if ($algo instanceof NumericInputAlgo) {
                    $algo->setInputRef($ref);
                    $algo->setRef($ref);
                    $algo->save();
                }
            }
        } catch (Core_Exception_NotFound $e) {
        }
    }

    /**
     * @return UnitAPI
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param UnitAPI $unit
     */
    public function setUnit(UnitAPI $unit)
    {
        $this->unit = $unit;
        // Modifie également l'unité de l'algo associé et l'association entre eux
        try {
            $af = $this->getAf();
            if ($af) {
                $algo = $af->getAlgoByRef($this->getRef());
                if ($algo instanceof NumericInputAlgo) {
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

    /**
     * Retourne le composant UI pour le choix de l'unité de la saisie
     * @param UnitAPI $baseUnit
     * @param UnitAPI $selectedUnit
     * @return Zend_Form_Element
     */
    protected function getUnitComponent(UnitAPI $baseUnit, UnitAPI $selectedUnit)
    {
        $unitComponent = new UI_Form_Element_Select($this->ref . '_unit');

        // Ajoute l'unité de base
        $option = new UI_Form_Element_Option(
            $this->ref . '_unit_' . $baseUnit->getRef(),
            $baseUnit->getRef(),
            $baseUnit->getSymbol()
        );
        $unitComponent->addOption($option);

        // Ajoute les unités compatibles
        foreach ($baseUnit->getCompatibleUnits() as $compatibleUnit) {
            $option = new UI_Form_Element_Option(
                $this->ref . '_unit_' . $compatibleUnit->getRef(),
                $compatibleUnit->getRef(),
                $compatibleUnit->getSymbol()
            );
            $unitComponent->addOption($option);
        }

        // Sélection
        if ($this->hasUnitSelection()) {
            $unitComponent->setValue($selectedUnit->getRef());
        } else {
            $unitComponent->setValue($baseUnit->getRef());
            $unitComponent->getElement()->disabled = true;
        }

        return $unitComponent;
    }

    /**
     * @return boolean
     */
    public function hasUnitSelection()
    {
        return is_null($this->unitSelection) ? true : $this->unitSelection;
    }

    /**
     * @param boolean $unitChoice
     */
    public function setUnitSelection($unitChoice)
    {
        $this->unitSelection = (boolean) $unitChoice;
    }
}
