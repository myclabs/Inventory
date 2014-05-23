<?php

namespace AF\Domain\InputSet;

use AF\Domain\AF;
use AF\Domain\Component\Component;
use AF\Domain\Input\SubAF\NotRepeatedSubAFInput;
use AF\Domain\Input\SubAF\RepeatedSubAFInput;
use AF\Domain\Input\Input;
use Core_Model_Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\PersistentCollection;
use Serializable;

/**
 * @author matthieu.napoli
 * @author thibaud.rolland
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
abstract class InputSet extends Core_Model_Entity implements \AF\Domain\Algorithm\InputSet, Serializable
{
    const QUERY_COMPLETION = 'completion';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var AF
     */
    protected $af;

    /**
     * @var Input[]|Collection|PersistentCollection
     */
    protected $inputs;

    /**
     * Pourcentage de complétion de la saisie
     * @var int|null
     */
    protected $completion;

    /**
     * Tableau de clés-valeurs définies par le contexte
     * @var array
     */
    protected $contextValues;


    /**
     * @param AF $af
     */
    public function __construct(AF $af)
    {
        $this->inputs = new ArrayCollection();
        $this->af = $af;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Input[]
     */
    public function getInputs()
    {
        return $this->inputs->toArray();
    }

    /**
     * Vide la saisie
     */
    public function clear()
    {
        $this->inputs->clear();
    }

    /**
     * Get the elements linked to the set by component
     * @param Component $component
     * @return Input|null
     */
    public function getInputForComponent(Component $component)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('refComponent', $component->getRef()));
        /** @var $inputs Collection */
        $inputs = $this->inputs->matching($criteria);
        if (count($inputs) > 0) {
            return $inputs->first();
        }
        return null;
    }

    /**
     * Définit la saisie pour un composant
     * @param Component $component
     * @param Input     $input
     */
    public function setInputForComponent(Component $component, Input $input)
    {
        // Remove any current Input that applies to this Component
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('refComponent', $component->getRef()));
        $currentInputs = $this->inputs->matching($criteria);
        foreach ($currentInputs as $currentInput) {
            $this->inputs->removeElement($currentInput);
        }
        // Add the Input to the InputSet
        $this->inputs->add($input);
        // Set the InputSet in the input
        $input->setInputSet($this);
    }

    /**
     * Supprime une saisie de composant
     * @param Input $input
     */
    public function removeInput(Input $input)
    {
        if ($this->inputs->contains($input)) {
            $this->inputs->removeElement($input);
        }
    }

    /**
     * Returns an input by its ref
     * @param string $ref
     * @return Input|null
     */
    public function getInputByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('refComponent', $ref));
        /** @var $inputs Collection */
        $inputs = $this->inputs->matching($criteria);
        if (count($inputs) > 0) {
            return $inputs->first();
        }
        return null;
    }

    /**
     * @param array $contextValues
     */
    public function setContextValues(array $contextValues)
    {
        $this->contextValues = $contextValues;

        // Copie dans les sous-inputset
        foreach ($this->getSubInputSets() as $subInputSet) {
            $subInputSet->setContextValues($contextValues);
        }
    }

    /**
     * @return array
     */
    public function getContextValues()
    {
        return $this->contextValues ? : [];
    }

    /**
     * Définit une valeur du contexte
     * @param string $name
     * @param mixed  $value
     */
    public function setContextValue($name, $value)
    {
        if ($this->contextValues === null) {
            $this->contextValues = [];
        }

        $this->contextValues[$name] = $value;

        // Copie dans les sous-inputset
        foreach ($this->getSubInputSets() as $subInputSet) {
            $subInputSet->setContextValue($name, $value);
        }
    }

    /**
     * Retourne une valeur définie par le contexte à partir de son nom
     * @param string $name
     * @return mixed|null
     */
    public function getContextValue($name)
    {
        if ($this->contextValues && array_key_exists($name, $this->contextValues)) {
            return $this->contextValues[$name];
        }
        return null;
    }

    /**
     * Met à jour le pourcentage de complétion de la saisie
     * @see getCompletion
     * @return void
     */
    public function updateCompletion()
    {
        $nbRequiredFields = $this->getAF()->getNbRequiredFields($this);
        $nbCompletedFields = 0;
        foreach ($this->inputs as $input) {
            $nbCompletedFields += $input->getNbRequiredFieldsCompleted();
        }
        if ($nbRequiredFields > 0) {
            $completion = $nbCompletedFields / $nbRequiredFields * 100;
        } else {
            // Par convention 100% si pas de champ obligatoire
            $completion = 100;
        }
        $this->completion = (int) $completion;
    }

    /**
     * Retourne le pourcentage de complétion de la saisie de l'AF
     *
     * Appeler updateCompletion pour recalculer la complétion
     * @see updateCompletion
     * @return int Pourcentage
     */
    public function getCompletion()
    {
        return $this->completion;
    }

    /**
     * Retourne true si la saisie de l'AF est complète
     *
     * Appeler updateCompletion pour recalculer la complétion
     * @see updateCompletion
     * @return boolean
     */
    public function isInputComplete()
    {
        return $this->getCompletion() == 100;
    }

    /**
     * @return AF
     */
    public function getAF()
    {
        return $this->af;
    }

    /**
     * @param AF $af
     */
    public function setAF(AF $af)
    {
        $this->af = $af;
    }

    /**
     * Retourne tous les sous-inputset (récursivement)
     * @return SubInputSet[]
     */
    protected function getSubInputSets()
    {
        $subInputSets = [];

        foreach ($this->getInputs() as $input) {
            if ($input instanceof NotRepeatedSubAFInput) {
                $subInputSet = $input->getValue();
                $subInputSets[] = $subInputSet;
                $subInputSets = array_merge($subInputSets, $subInputSet->getSubInputSets());
            }
            if ($input instanceof RepeatedSubAFInput) {
                foreach ($input->getValue() as $subInputSet) {
                    $subInputSets[] = $subInputSet;
                    $subInputSets = array_merge($subInputSets, $subInputSet->getSubInputSets());
                }
            }
        }

        return $subInputSets;
    }

    /**
     * Serialization en session
     */
    public function serialize()
    {
        $data = get_object_vars($this);

        unset($data['af']);
        $data['idAF'] = $this->af->getId();

        return serialize($data);
    }

    public function unserialize($data)
    {
        $data = unserialize($data);

        foreach ($data as $property => $value) {
            if ($property === 'idAF') {
                continue;
            }
            $this->$property = $value;
        }

        $entityManager = \Core\ContainerSingleton::getEntityManager();
        $this->af = $entityManager->find(AF::class, $data['idAF']);
    }
}
