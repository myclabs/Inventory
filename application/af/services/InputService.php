<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use ArrayComparator\ArrayComparator;

/**
 * Service responsable de la gestion des saisie des AF
 * @package AF
 */
class AF_Service_InputService extends Core_Singleton
{

    /**
     * Edit an AF input
     * @param AF_Model_InputSet_Primary $inputSet  InputSet to edit
     * @param AF_Model_InputSet_Primary $newValues
     */
    public function editInputSet(AF_Model_InputSet_Primary $inputSet, AF_Model_InputSet_Primary $newValues)
    {
        $this->updateInputSet($inputSet, $inputSet->getInputs(), $newValues->getInputs());
    }

    /**
     * @param AF_Model_InputSet_Primary $inputSet
     * @param AF_Model_InputSet_Primary $newValues
     */
    private function updateInputSet(AF_Model_InputSet_Primary $inputSet, AF_Model_InputSet_Primary $newValues)
    {
        $comparator = new ArrayComparator($inputSet->getInputs(), $newValues->getInputs());

        $comparator->setItemIdentityComparator(
            function (AF_Model_Input $input1, AF_Model_Input $input2) {
                return $input1->getComponent() === $input2->getComponent();
            }
        );

        // Détermine les différences
        $comparator->setItemComparator(
            function (AF_Model_Input $input1, AF_Model_Input $input2) use ($inputSet) {
                // SubAF
                if ($input1 instanceof AF_Model_Input_SubAF) {
                    // TODO
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

        $comparator->compare();
    }

}
