<?php

namespace AF\Domain\Component;

use AF\Domain\InputSet\InputSet;
use AF\Domain\AFConfigurationError;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Gestion des groupements de champs.
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 */
class Group extends Component
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
     * @var Collection|Component[]
     */
    protected $subComponents;

    /**
     * Flag indiquant si le groupe est repliable.
     * Initialement le groupe est repliable.
     * @var integer
     */
    protected $foldaway = self::FOLDAWAY;


    public function __construct()
    {
        parent::__construct();
        $this->subComponents = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function initializeNewInput(InputSet $inputSet)
    {
        foreach ($this->subComponents as $component) {
            $component->initializeNewInput($inputSet);
        }
    }

    /**
     * Retourne les sous-composants du groupe
     * @return Component[]
     */
    public function getSubComponents()
    {
        return $this->subComponents;
    }

    /**
     * Retourne tous les sous-composants du groupe en parcourant l'arbre récursivement
     * @return Component[]
     */
    public function getSubComponentsRecursive()
    {
        $subComponents = [];

        foreach ($this->subComponents as $subComponent) {
            $subComponents[] = $subComponent;
            if ($subComponent instanceof Group) {
                $subComponents = array_merge($subComponents, $subComponent->getSubComponentsRecursive());
            }
        }

        return $subComponents;
    }

    /**
     * Ajoute un sous-composant dans le groupe
     * @param Component $component
     */
    public function addSubComponent(Component $component)
    {
        if ($this->subComponents->contains($component)) {
            return;
        }

        $this->subComponents->add($component);
        $component->setGroup($this);
    }

    /**
     * Supprime un sous-composant du groupe
     * @param Component $component
     */
    public function removeSubComponent(Component $component)
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
            $errors[] = new AFConfigurationError(__('AF', 'configControl', 'emptyGroup', [
                'REF' => $this->ref
            ]), false, $this->getAf());
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
    public function getNbRequiredFields(InputSet $inputSet = null)
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
