<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use ArrayComparator\ArrayComparator;

/**
 * Service responsable de la gestion des saisies des AF
 *
 * @package AF
 */
class AF_Service_InputService extends Core_Singleton
{

    /**
     * Modifie une saisie et recalcule les résultats si la saisie est complète
     *
     * @param AF_Model_InputSet_Primary $inputSet  InputSet à modifier
     * @param AF_Model_InputSet_Primary $newValues Nouvelles valeurs pour les saisies
     * @throws InvalidArgumentException Both InputSets should be for the same AF
     */
    public function editInputSet(AF_Model_InputSet_Primary $inputSet, AF_Model_InputSet_Primary $newValues)
    {
        if ($inputSet->getAF() !== $newValues->getAF()) {
            throw new InvalidArgumentException("Both InputSets should be for the same AF");
        }

        $this->compareAndUpdateInputSet($inputSet, $newValues);

        // MAJ le pourcentage de complétion
        $inputSet->updateCompletion();

        // Met à jour les résultats
        $this->updateResults($inputSet);
    }

    /**
     * Met à jour les résultats d'une saisie
     *
     * Si la saisie est incomplète, les résultats seront vidés.
     *
     * @param AF_Model_InputSet_Primary $inputSet
     */
    public function updateResults(AF_Model_InputSet_Primary $inputSet)
    {
        // Si la saisie est complète
        if ($inputSet->isInputComplete()) {
            // Calcule les résultats
            $inputSet->getAF()->execute($inputSet);
            $inputSet->getOutputSet()->calculateTotals();
        }

        $inputSet->clearOutputSet();
    }

    /**
     * Compare 2 inputSet et met à jour le premier à partir des données du second
     *
     * @param AF_Model_InputSet $inputSet
     * @param AF_Model_InputSet $newValues
     */
    private function compareAndUpdateInputSet(AF_Model_InputSet $inputSet, AF_Model_InputSet $newValues)
    {
        $comparator = new ArrayComparator();

        $comparator->setItemIdentityComparator(
            function ($key1, $key2, AF_Model_Input $input1, AF_Model_Input $input2) {
                return $input1->getComponent() === $input2->getComponent();
            }
        );

        // Détermine les différences
        $comparator->setItemComparator(
            function (AF_Model_Input $input1, AF_Model_Input $input2) use ($inputSet) {
                // SubAF
                if ($input1 instanceof AF_Model_Input_SubAF_NotRepeated
                    && $input2 instanceof AF_Model_Input_SubAF_NotRepeated
                ) {
                    $this->compareAndUpdateInputSet($input1->getValue(), $input2->getValue());
                }
                if ($input1 instanceof AF_Model_Input_SubAF_Repeated
                    && $input2 instanceof AF_Model_Input_SubAF_Repeated
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
                        function (AF_Model_InputSet_Sub $inputSet1, AF_Model_InputSet_Sub $inputSet2) {
                            $this->compareAndUpdateInputSet($inputSet1, $inputSet2);
                        }
                    );
                    $comparator->whenMissingRight(
                        function (AF_Model_InputSet_Sub $inputSet1) use ($input1) {
                            $input1->removeSubSet($inputSet1);
                        }
                    );
                    $comparator->whenMissingLeft(
                        function (AF_Model_InputSet_Sub $inputSet2) use ($input1) {
                            $input1->addSubSet($inputSet2);
                        }
                    );
                    $comparator->compare($input1->getValue(), $input2->getValue());
                }

                return $input1->equals($input2);
            }
        );

        // Si une saisie a été modifiée
        $comparator->whenDifferent(
            function (AF_Model_Input $input1, AF_Model_Input $input2) use ($inputSet) {
                // Prend la nouvelle saisie pour remplacer l'actuelle
                $inputSet->removeInput($input1);
                $input1->delete();
                $inputSet->setInputForComponent($input2->getComponent(), $input2);
                $input2->setInputSet($inputSet);
            }
        );

        // Si une saisie n'existe plus
        $comparator->whenMissingRight(
            function (AF_Model_Input $input1) use ($inputSet) {
                $inputSet->removeInput($input1);
                $input1->delete();
            }
        );

        // Si un champ de saisie a été ajouté
        $comparator->whenMissingLeft(
            function (AF_Model_Input $input2) use ($inputSet) {
                $inputSet->setInputForComponent($input2->getComponent(), $input2);
                $input2->setInputSet($inputSet);
            }
        );

        $comparator->compare($inputSet->getInputs(), $newValues->getInputs());
    }

}
