<?php

namespace AF\Domain\InputService;

use AF\Domain\Input\TextFieldInput;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\Input\CheckboxInput;
use AF\Domain\Input\Select\SelectSingleInput;
use AF\Domain\Input\Select\SelectMultiInput;
use AF\Domain\Input\SubAF\RepeatedSubAFInput;
use AF\Domain\Input\SubAF\NotRepeatedSubAFInput;
use AF\Domain\InputSet\InputSet;
use AF\Domain\Input\Input;
use AF\Domain\InputSet\SubInputSet;
use ArrayComparator\ArrayComparator;

/**
 * Helper mettant à jour un InputSet à partir d'un autre InputSet.
 *
 * @author matthieu.napoli
 */
class InputSetUpdater extends ArrayComparator
{
    /**
     * @var InputSet
     */
    private $inputSet;

    /**
     * @var InputSet
     */
    private $newValues;

    /**
     * @param InputSet $inputSet  InputSet à modifier
     * @param InputSet $newValues Autre InputSet contenant les nouvelles valeurs
     */
    public function __construct(InputSet $inputSet, InputSet $newValues)
    {
        parent::__construct();

        // Handlers
        $this->whenDifferent([$this, 'whenDifferentHandler']);
        $this->whenMissingRight([$this, 'whenMissingRightHandler']);
        $this->whenMissingLeft([$this, 'whenMissingLeftHandler']);

        $this->inputSet = $inputSet;
        $this->newValues = $newValues;
    }

    /**
     * Update the first InputSet
     */
    public function run()
    {
        // Copie les saisies
        $this->compare($this->inputSet->getInputs(), $this->newValues->getInputs());

        // Copie les "ContextValue"
        $this->inputSet->setContextValues($this->newValues->getContextValues());
    }

    /**
     * {@inheritdoc}
     * Compares 2 items and returns if they have the same identity (if they represent the same item)
     * @param mixed $key1
     * @param mixed $key2
     * @param Input $input1
     * @param Input $input2
     * @return boolean
     */
    protected function areSame($key1, $key2, $input1, $input2)
    {
        return $input1->getComponent() === $input2->getComponent();
    }

    /**
     * {@inheritdoc}
     * Compares 2 items and returns if there are differences
     * @param Input $input1
     * @param Input $input2
     * @return boolean
     */
    protected function areEqual($input1, $input2)
    {
        // SubAF
        if ($input1 instanceof NotRepeatedSubAFInput
            && $input2 instanceof NotRepeatedSubAFInput
        ) {
            // Lance une mise à jour du sous-inputSet
            $subUpdater = new InputSetUpdater($input1->getValue(), $input2->getValue());
            $subUpdater->run();
        }
        if ($input1 instanceof RepeatedSubAFInput && $input2 instanceof RepeatedSubAFInput) {
            // Lance une comparaison des listes de sous-InputSet
            $this->compareInputSubAFRepeated($input1, $input2);
        }

        return $input1->equals($input2);
    }

    /**
     * Handler appelé lorsque des éléments différents sont trouvés entre les 2 input set
     * @param Input $input1
     * @param Input $input2
     */
    protected function whenDifferentHandler(Input $input1, Input $input2)
    {
        // Si les saisies ne sont pas du même type (le type du champ a changé entre les 2 saisies)
        if (get_class($input1) !== get_class($input2)) {
            // Prend la nouvelle saisie pour remplacer l'actuelle
            $this->inputSet->removeInput($input1);
            $input1->delete();
            $this->inputSet->setInputForComponent($input2->getComponent(), $input2);
            $input2->setInputSet($this->inputSet);
            return;
        }

        // Si les saisies sont du même type
        if ($input1 instanceof NumericFieldInput) {
            /** @var NumericFieldInput $input2 */
            $input1->setValue($input2->getValue());
            $input1->setInconsistentValue($input2->hasInconsistentValue());
        }
        if ($input1 instanceof CheckboxInput) {
            /** @var CheckboxInput $input2 */
            $input1->setValue($input2->getValue());
        }
        if ($input1 instanceof SelectSingleInput) {
            /** @var SelectSingleInput $input2 */
            $input1->setValueFrom($input2);
        }
        if ($input1 instanceof SelectMultiInput) {
            /** @var SelectMultiInput $input2 */
            $input1->setValueFrom($input2);
        }
        if ($input1 instanceof TextFieldInput) {
            /** @var TextFieldInput $input2 */
            $input1->setValue($input2->getValue());
        }
        $input1->setDisabled($input2->isDisabled());
        $input1->setHidden($input2->isHidden());
    }

    /**
     * Handler appelé lorsqu'un élément de l'input set a été supprimé dans la nouvelle saisie
     * @param Input $input1
     */
    protected function whenMissingRightHandler(Input $input1)
    {
        $this->inputSet->removeInput($input1);
        $input1->delete();
    }

    /**
     * Handler appelé lorsqu'un nouvel élément est présent dans la nouvelle saisie
     * @param Input $input2
     */
    protected function whenMissingLeftHandler(Input $input2)
    {
        $this->inputSet->setInputForComponent($input2->getComponent(), $input2);
        $input2->setInputSet($this->inputSet);
    }

    /**
     * Compare et synchronise des saisies de sous-AF répétés
     * @param RepeatedSubAFInput $input1
     * @param RepeatedSubAFInput $input2
     */
    private function compareInputSubAFRepeated(
        RepeatedSubAFInput $input1,
        RepeatedSubAFInput $input2
    ) {
        $comparator = new ArrayComparator();
        $comparator->setItemComparator(
            function () {
                // Tous les items avec le même index sont considérés différents
                // pour forcer l'appel à whenDifferent et donc la récursivité
                return false;
            }
        );
        $comparator->whenDifferent(
            function (SubInputSet $inputSet1, SubInputSet $inputSet2) {
                // Un traitement particulier pour les libellés libres des saisies répétées
                if ($inputSet1->getFreeLabel() !== $inputSet2->getFreeLabel()) {
                    $inputSet1->setFreeLabel($inputSet2->getFreeLabel());
                }

                // Met à jour l'inputSet sauvegardé en prenant en compte les valeurs de la nouvelle saisie
                $subUpdater = new InputSetUpdater($inputSet1, $inputSet2);
                $subUpdater->run();
            }
        );
        $comparator->whenMissingRight(
            function (SubInputSet $inputSet1) use ($input1) {
                $input1->removeSubSet($inputSet1);
            }
        );
        $comparator->whenMissingLeft(
            function (SubInputSet $inputSet2) use ($input1) {
                $input1->addSubSet($inputSet2);
            }
        );
        $comparator->compare($input1->getValue(), $input2->getValue());
    }
}
