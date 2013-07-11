<?php
/**
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Form
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @package    AF
 * @subpackage Form
 */
class AF_Model_Component_Select_Multi extends AF_Model_Component_Select
{

    /**
     * Constante associée à l'attribut 'type'.
     * Correspond à des cases à cocher à choix multiples.
     * @var integer
     */
    const TYPE_MULTICHECKBOX = 1;

    /**
     * Constante associée à l'attribut 'type'.
     * Correspond à une liste de séléction multiple.
     * @var integer
     */
    const TYPE_MULTISELECT = 2;

    /**
     * @var AF_Model_Component_Select_Option[]|Collection
     */
    protected $defaultValues;

    /**
     * Indicate if the field is a multi choice list or a list of checkBox.
     * @var int
     */
    protected $type = self::TYPE_MULTICHECKBOX;


    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultValues = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getUIElement(AF_GenerationHelper $generationHelper)
    {
        switch ($this->type) {
            case self::TYPE_MULTICHECKBOX :
                $uiElement = new UI_Form_Element_MultiCheckbox($this->ref);
                break;
            case self::TYPE_MULTISELECT :
                $uiElement = new UI_Form_Element_MultiSelect($this->ref);
                break;
            default:
                throw new Core_Exception_UndefinedAttribute("The type must be defined and valid");
        }
        $uiElement->setLabel($this->label);
        $uiElement->getElement()->help = $this->help;
        $uiElement->setRequired($this->getRequired());
        // Liste des options
        foreach ($this->options as $option) {
            $uiElement->addOption($generationHelper->getUIOption($option));
        }
        if ($generationHelper->isReadOnly()) {
            $uiElement->getElement()->setReadOnly();
        }
        // Remplit avec les options saisies
        $input = null;
        if ($generationHelper->getInputSet()) {
            /** @var $input AF_Model_Input_Select_Multi */
            $input = $generationHelper->getInputSet()->getInputForComponent($this);
        }
        if ($input) {
            $uiElement->getElement()->disabled = $input->isDisabled();
            $uiElement->getElement()->hidden = $input->isHidden();
            $uiElement->setValue($input->getValue());
            // Historique de la valeur
            $uiElement->getElement()->addElement($this->getHistoryComponent($input));
        } else {
            $uiElement->getElement()->disabled = !$this->enabled;
            $uiElement->getElement()->hidden = !$this->visible;
            // Valeurs par défaut
            $defaultOptionsRef = [];
            foreach ($this->defaultValues as $defaultValue) {
                $defaultOptionsRef[] = $defaultValue->getRef();
            }
            $uiElement->setValue($defaultOptionsRef);
        }
        // Actions
        foreach ($this->actions as $action) {
            $uiElement->getElement()->addAction($generationHelper->getUIAction($action));
        }
        return $uiElement;
    }

    /**
     * Retourne les options sélectionnées par défaut
     * @return AF_Model_Component_Select_Option[]
     */
    public function getDefaultValues()
    {
        return $this->defaultValues;
    }

    /**
     * @param AF_Model_Component_Select_Option $option
     * @return bool
     */
    public function hasDefaultValue(AF_Model_Component_Select_Option $option)
    {
        return $this->defaultValues->contains($option);
    }

    /**
     * @param AF_Model_Component_Select_Option $option
     */
    public function addDefaultValue(AF_Model_Component_Select_Option $option)
    {
        if (!$this->hasDefaultValue($option)) {
            $this->defaultValues->add($option);
        }
    }

    /**
     * @param AF_Model_Component_Select_Option $option
     */
    public function removeDefaultValue(AF_Model_Component_Select_Option $option)
    {
        if ($this->defaultValues->contains($option)) {
            $this->defaultValues->removeElement($option);
        }
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
        $this->type = (int) $type;
    }

}
