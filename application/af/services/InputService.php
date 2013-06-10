<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

/**
 * Service responsable de la gestion des saisies des AF
 *
 * @package AF
 */
class AF_Service_InputService
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

        // Met à jour l'InputSet sauvegardé
        $updater = new AF_Service_InputService_InputSetUpdater($inputSet, $newValues);
        $updater->run();

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

}
