<?php
/**
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Form
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @package    AF
 * @subpackage Form
 */
abstract class AF_Model_Component_Select extends AF_Model_Component_Field
{

    /**
     * Indique si on veut que le champ soit créé avec la gestion des erreurs.
     * @var bool
     */
    const WITH_ERROR = true;

    /**
     * Indique si on veut que le champ soit créé sans la gestion des erreurs.
     * @var bool
     */
    const WITHOUT_ERROR = false;

    /**
     * Options du champ select
     * @var AF_Model_Component_Select_Option[]|Collection
     */
    protected $options;

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
        $this->options = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRequiredFields(AF_Model_InputSet $inputSet = null)
    {
        if (! $this->getRequired()) {
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
     * Retourne les options du champ select
     * @return AF_Model_Component_Select_Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Retourne l'option qui correspond au ref donné
     * @param string $ref
     * @throws Core_Exception_NotFound
     * @return AF_Model_Component_Select_Option
     */
    public function getOptionByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('ref', $ref));
        /** @var $options Collection */
        $options = $this->options->matching($criteria);
        if (count($options) > 0) {
            return $options->first();
        }
        throw new Core_Exception_NotFound("No option was found with ref '$ref'");
    }

    /**
     * Add a single option to the select.
     * @param AF_Model_Component_Select_Option $option
     * @return void
     */
    public function addOption(AF_Model_Component_Select_Option $option)
    {
        if (!$this->hasOption($option)) {
            $this->options->add($option);
            $option->setSelect($this);
        }
    }

    /**
     * See if the multi has already the option
     * @param AF_Model_Component_Select_Option $option
     * @return bool
     */
    public function hasOption(AF_Model_Component_Select_Option $option)
    {
        return $this->options->contains($option);
    }

    /**
     * Remove one option from multi
     * @param AF_Model_Component_Select_Option $option
     */
    public function removeOption(AF_Model_Component_Select_Option $option)
    {
        if ($this->hasOption($option)) {
            $this->options->removeElement($option);
        }
    }

    /**
     * Get the required attribute.
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set the required attribute.
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = $required;

    }

    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();
        $options = $this->getOptions();
        // Au moins 2 options
        if (count($options) < 2) {
            $errors[] = new AF_ConfigError(__('AF', 'configControl', 'zeroOrOneOption', ['REF' => $this->ref]),
                                           false, $this->getAf());
        }
        return $errors;
    }

}
