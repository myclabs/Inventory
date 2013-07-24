<?php
/**
 * @author     matthieu.napoli
 * @author     hugo.charbonnier
 * @author     thibaud.rolland
 * @package    AF
 * @subpackage Form
 */
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Gestion des groupements de champs
 * @package    AF
 * @subpackage Form
 */
class AF_Model_Component_Group extends AF_Model_Component
{

    /**
     * Constante associée à l'attribut 'unfoldaway'
     * Correspond à un groupe non repliable.
     * @var integer
     */
    const UNFOLDAWAY = 0;

    /**
     * Constante associée à l'attribut 'foldaway'.
     * Correspond à un groupe repliable mais initialement non replié.
     * @var integer
     */
    const FOLDAWAY = 1;

    /**
     * Constante associée à l'attribut 'foldaway'.
     * Correspond à un groupe repliable et initialement replié.
     * @var integer
     */
    const FOLDED = 2;

    /**
     * Ref utilisé pour les groupes racines
     */
    const ROOT_GROUP_REF = "root_group";

    /**
     * @var Collection|AF_Model_Component[]
     */
    protected $subComponents;

    /**
     * Flag indiquant si le groupe est repliable.
     * Initialement le groupe est repliable.
     * @var integer
     */
    protected $foldaway = self::FOLDAWAY;


    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct();
        $this->subComponents = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     * @throws Core_Exception_User The group is empty
     */
    public function getUIElement(AF_GenerationHelper $generationHelper)
    {
        $isRootGroup = false;
        $uiElement = new UI_Form_Element_Group($this->ref);
        $uiElement->setLabel($this->label);
        $uiElement->getElement()->help = $this->help;
        $uiElement->getElement()->hidden = !$this->visible;
        switch ($this->foldaway) {
            case self::FOLDAWAY:
                $uiElement->foldaway = true;
                break;
            case self::FOLDED:
                $uiElement->folded = true;
                break;
        }
        // Si c'est le rootGroup on n'affiche pas le label, et il n'est pas repliable
        if ($this->af->getRootGroup() === $this) {
            $isRootGroup = true;
            $uiElement->foldaway = false;
            $uiElement->folded = false;
            $uiElement->removeDecorator('Group');
        }
        // Récupère la saisie correspondant à cet élément
        if ($generationHelper->getInputSet()) {
            /** @var $input AF_Model_Input_Group */
            $input = $generationHelper->getInputSet()->getInputForComponent($this);
            if ($input) {
                $uiElement->getElement()->hidden = $input->isHidden();
                $uiElement->getElement()->disabled = $input->isDisabled();
            }
        }
        // Sous-éléments
        $subComponents = $this->getSubComponents();
        foreach ($subComponents as $component) {
            $subElement = $generationHelper->getUIElement($component);
            if (!$isRootGroup) {
                $subElement->getElement()->prefixRef($this->ref);
            }
            $uiElement->addElement($subElement);
        }
        // Actions
        foreach ($this->actions as $action) {
            $uiElement->getElement()->addAction($generationHelper->getUIAction($action));
        }
        return $uiElement;
    }

    /**
     * Retourne les sous-composants du groupe
     * @return AF_Model_Component[]
     */
    public function getSubComponents()
    {
        return $this->subComponents;
    }

    /**
     * Retourne tous les sous-composants du groupe en parcourant l'arbre récursivement
     * @return AF_Model_Component[]
     */
    public function getSubComponentsRecursive()
    {
        $subComponents = [];

        foreach ($this->subComponents as $subComponent) {
            $subComponents[] = $subComponent;
            if ($subComponent instanceof AF_Model_Component_Group) {
                $subComponents = array_merge($subComponents, $subComponent->getSubComponentsRecursive());
            }
        }

        return $subComponents;
    }

    /**
     * Ajoute un sous-composant dans le groupe
     * @param AF_Model_Component $component
     */
    public function addSubComponent(AF_Model_Component $component)
    {
        if ($this->subComponents->contains($component)) {
            return;
        }

        $this->subComponents->add($component);
        $component->setGroup($this);
    }

    /**
     * Supprime un sous-composant du groupe
     * @param AF_Model_Component $component
     */
    public function removeSubComponent(AF_Model_Component $component)
    {
        if (!$this->subComponents->contains($component)) {
            return;
        }

        $this->subComponents->removeElement($component);
        $component->setGroup(null);
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();
        $subComponents = $this->getSubComponents();
        // Au moins un élément
        if (count($subComponents) == 0) {
            $errors[] = new AF_ConfigError(__('AF', 'configControl', 'emptyGroup', ['REF' => $this->ref]),
                                           false, $this->getAf());
        }
        // Valide les sous-éléments
        foreach ($subComponents as $subComponent) {
            $errors = array_merge($errors, $subComponent->checkConfig());
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
            // Si le groupe est caché
            if ($input && $input->isHidden()) {
                return 0;
            }
        }
        $nbRequiredFields = 0;
        foreach ($this->getSubComponents() as $component) {
            $nbRequiredFields += $component->getNbRequiredFields($inputSet);
        }
        return $nbRequiredFields;
    }

    /**
     * @return int
     */
    public function getFoldaway()
    {
        return $this->foldaway;
    }

    /**
     * @param int $foldaway
     */
    public function setFoldaway($foldaway)
    {
        $this->foldaway = (int) $foldaway;
    }

}
