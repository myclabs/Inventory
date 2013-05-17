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
class AF_Model_Action_SetValue_Select_Multi extends AF_Model_Action_SetValue
{

    /**
     * Array of options
     * @var AF_Model_Component_Select_Option[]|Collection
     */
    protected $options;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getUIAction(AF_GenerationHelper $generationHelper)
    {
        $uiAction = new UI_Form_Action_SetValue($this->id);
        if (!empty($this->condition)) {
            $uiAction->condition = $generationHelper->getUICondition($this->condition);
        }
        $optionsRef = $this->options->map(function (AF_Model_Component_Select_Option $option) {
            return $option->getRef();
        });
        /** @var $optionsRef Collection */
        $uiAction->value = $optionsRef->toArray();
        return $uiAction;
    }

    /**
     * Get the options.
     * @return AF_Model_Component_Select_Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param AF_Model_Component_Select_Option $option
     * @return bool
     */
    public function hasOption(AF_Model_Component_Select_Option $option)
    {
        return $this->options->contains($option);
    }

    /**
     * @param AF_Model_Component_Select_Option $option
     */
    public function addOption(AF_Model_Component_Select_Option $option)
    {
        if (!$this->hasOption($option)) {
            $this->options->add($option);
        }
    }

    /**
     * @param AF_Model_Component_Select_Option $option
     */
    public function removeOption(AF_Model_Component_Select_Option $option)
    {
        if ($this->hasOption($option)) {
            $this->options->removeElement($option);
        }
    }

}
