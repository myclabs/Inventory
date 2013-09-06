<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Service responsable de la gestion des saisies des AF
 *
 * @package AF
 */
class AF_Service_InputService
{

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

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

        // Met à jour les résultats
        $this->updateResults($inputSet);
    }

    /**
     * Met à jour les résultats d'une saisie
     *
     * Si la saisie est incomplète, les résultats seront vidés.
     *
     * @param AF_Model_InputSet_Primary $inputSet
     * @param AF_Model_AF               $af Permet d'uiliser un AF différent de celui de la saisie
     */
    public function updateResults(AF_Model_InputSet_Primary $inputSet, AF_Model_AF $af = null)
    {
        if (! $af) {
            $af = $inputSet->getAF();
        }

        // MAJ le pourcentage de complétion
        $inputSet->updateCompletion();

        // Si la saisie est complète
        if ($inputSet->isInputComplete()) {
            // Calcule les résultats
            try {
                $af->execute($inputSet);
                $inputSet->setCalculationComplete(true);
                $inputSet->getOutputSet()->calculateTotals();
            } catch (Exception $e) {
                $ref = $inputSet->getAF()->getRef();
                Core_Error_Log::getInstance()->warning("Error while calculating AF '$ref' results", ['exception' => $e]);
                Core_Error_Log::getInstance()->logException($e);

                $inputSet->setCalculationComplete(false);
                $inputSet->clearOutputSet();
            }
        } else {
            $inputSet->clearOutputSet();
        }
    }

}
