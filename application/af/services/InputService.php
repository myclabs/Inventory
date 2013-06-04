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
     * Modifie une saisie
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

        $this->updateInputSet($inputSet, $newValues);
    }

    /**
     * @param AF_Model_InputSet $inputSet
     * @param AF_Model_InputSet $newValues
     */
    private function updateInputSet(AF_Model_InputSet $inputSet, AF_Model_InputSet $newValues)
    {
        $comparator = new ArrayComparator();

        $comparator->setItemIdentityComparator(
            function (AF_Model_Input $input1, AF_Model_Input $input2) {
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
                    $this->updateInputSet($input1->getValue(), $input2->getValue());
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
                            $this->updateInputSet($inputSet1, $inputSet2);
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
