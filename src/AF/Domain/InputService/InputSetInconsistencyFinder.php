<?php

namespace AF\Domain\InputService;

use AF\Domain\Input\NumericFieldInput;
use AF\Domain\Input\SubAF\RepeatedSubAFInput;
use AF\Domain\Input\SubAF\NotRepeatedSubAFInput;
use AF\Domain\InputSet\InputSet;
use AF\Domain\Input\Input;
use AF\Domain\InputSet\SubInputSet;
use ArrayComparator\ArrayComparator;

/**
 * Helper permettant de comparer ses valeurs à celles d'un autre InputSet pour détécter les incohérences.
 *
 * @author valentin.claras
 * @author matthieu.napoli
 */
class InputSetInconsistencyFinder extends ArrayComparator
{
    /**
     * @var InputSet
     */
    private $inputSet;

    /**
     * @var InputSet
     */
    private $referenceValues;

    /**
     * @var float
     */
    private $varianceSought;

    /**
     * @var int
     */
    private $numberOfInconsistencies;

    /**
     * @param InputSet $inputSet InputSet à comparer
     * @param InputSet $referenceValues Autre InputSet contenant les valeurs de référence
     * @param float $varianceSought
     */
    public function __construct(InputSet $inputSet, InputSet $referenceValues = null, $varianceSought = 2.0)
    {
        parent::__construct();

        // Handlers
        $this->whenEqual([$this, 'whenEqualHandler']);
        $this->whenDifferent([$this, 'whenDifferentHandler']);
        $this->whenMissingRight([$this, 'whenMissingRightHandler']);

        $this->inputSet = $inputSet;
        $this->referenceValues = $referenceValues;
        $this->varianceSought = $varianceSought;
    }

    /**
     * Vérifie les valeurs du premier InputSet et note celles qui sont incohérentes.
     */
    public function run()
    {
        $this->numberOfInconsistencies = 0;

        // Copie les saisies
        if ($this->referenceValues !== null) {
            $this->compare($this->inputSet->getInputs(), $this->referenceValues->getInputs());
        } else {
            $this->compare($this->inputSet->getInputs(), []);
        }

        return $this->numberOfInconsistencies;
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
            $subUpdater = new InputSetInconsistencyFinder(
                $input1->getValue(),
                $input2->getValue(),
                $this->varianceSought
            );
            $this->numberOfInconsistencies += $subUpdater->run();
        }
        if ($input1 instanceof RepeatedSubAFInput && $input2 instanceof RepeatedSubAFInput) {
            // Lance une comparaison des listes de sous-InputSet
            $this->compareInputSubAFRepeated($input1, $input2);
        }

        if ($input1 instanceof NumericFieldInput) {
            /** @var NumericFieldInput $input1 */
            /** @var NumericFieldInput $input2 */
            if ((get_class($input1) === get_class($input2))
                && ($input1->getRefComponent() === $input2->getRefComponent())
                && ($input1->isDisabled() === $input2->isDisabled())
                && ($input1->isHidden() === $input2->isHidden())
            ) {
                $currentValue = (float) $input1->getValue()->getDigitalValue();
                $previousValue = (float) $input2->getValue()->convertTo($input1->getValue()->getUnit())->getDigitalValue();

                if (($currentValue < ($previousValue / $this->varianceSought))
                    || ($currentValue > ($previousValue * $this->varianceSought))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Handler appelé lorsque des éléments différents sont trouvés entre les 2 input set
     * @param Input $input1
     * @param Input $input2
     */
    protected function whenDifferentHandler(Input $input1, Input $input2)
    {
        if ($input1 instanceof NumericFieldInput) {
            $input1->setInconsistentValue(true);
            $this->numberOfInconsistencies++;
        }
    }

    /**
     * Handler appelé lorsque des éléments identiques sont trouvés entre les 2 input set
     * @param Input $input1
     * @param Input $input2
     */
    protected function whenEqualHandler(Input $input1, Input $input2)
    {
        if ($input1 instanceof NumericFieldInput) {
            $input1->setInconsistentValue(false);
        }
    }

    /**
     * Handler appelé lorsqu'un élément de l'input set a été supprimé dans la nouvelle saisie
     * @param Input $input1
     */
    protected function whenMissingRightHandler(Input $input1)
    {
        if ($input1 instanceof NumericFieldInput) {
            $input1->setInconsistentValue(false);
        }
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
                // pour forcer l'appel à whenDifferent et donc la récursivité.
                return false;
            }
        );
        $comparator->whenDifferent(
            function (SubInputSet $inputSet1, SubInputSet $inputSet2) {
                // Compare les champs des SubInputSet.
                $subUpdater = new InputSetInconsistencyFinder(
                    $inputSet1,
                    $inputSet2,
                    $this->varianceSought
                );
                $this->numberOfInconsistencies += $subUpdater->run();
            }
        );
        $comparator->compare($input1->getValue(), $input2->getValue());
    }
}
