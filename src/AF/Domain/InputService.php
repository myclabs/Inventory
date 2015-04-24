<?php

namespace AF\Domain;

use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\InputService\InputSetUpdater;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Service responsable de la gestion des saisies des AF.
 *
 * @author matthieu.napoli
 */
class InputService
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EventDispatcher $eventDispatcher, LoggerInterface $logger)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * Crée une nouvelle saisie en la remplissant avec les valeurs par défaut.
     *
     * @param AF $af
     * @return PrimaryInputSet
     */
    public function createDefaultInputSet(AF $af)
    {
        $inputSet = new PrimaryInputSet($af);

        $af->initializeNewInput($inputSet);

        return $inputSet;
    }

    /**
     * Modifie une saisie et recalcule les résultats si la saisie est complète
     *
     * @param PrimaryInputSet $inputSet  InputSet à modifier
     * @param PrimaryInputSet $newValues Nouvelles valeurs pour les saisies
     * @throws InvalidArgumentException Both InputSets should be for the same AF
     */
    public function editInputSet(PrimaryInputSet $inputSet, PrimaryInputSet $newValues)
    {
        if ($inputSet->getAF() !== $newValues->getAF()) {
            throw new InvalidArgumentException("Both InputSets should be for the same AF");
        }

        // Met à jour l'InputSet sauvegardé
        $updater = new InputSetUpdater($inputSet, $newValues);
        $updater->run();

        // Met à jour les résultats
        $this->updateResults($inputSet);
    }

    /**
     * Met à jour les résultats d'une saisie
     *
     * Si la saisie est incomplète, les résultats seront vidés.
     *
     * @param PrimaryInputSet $inputSet
     * @param AF $af Permet d'uiliser un AF différent de celui de la saisie
     * @param bool $updateFinish Indique s'il faut mettre à jour le status finish de la saisie
     *                           false dans le cas où on recalcule toutes les saisies
     */
    public function updateResults(PrimaryInputSet $inputSet, AF $af = null, $updateFinish = true)
    {
        if (!$af) {
            $af = $inputSet->getAF();
        }

        // MAJ le pourcentage de complétion
        $inputSet->updateCompletion();

        // La saisie vient d'être modifiée, donc on la force à "non terminée"
        if ($updateFinish) {
            $inputSet->markAsFinished(false);
        }

        // Si la saisie est incomplète
        if (!$inputSet->isInputComplete()) {
            $inputSet->clearOutputSet();
            $inputSet->markAsFinished(false);
            return;
        }

        // Calcule les résultats
        try {
            $af->execute($inputSet);
            $inputSet->setCalculationComplete(true);
            $inputSet->getOutputSet()->calculateTotals();
        } catch (CalculationException $e) {
            $message = __('AF', 'inputInput', 'completeInputSavedCalculationError');
            $inputSet->setCalculationComplete(false, $message . ' ' . $e->getMessage());
            $inputSet->clearOutputSet();
        } catch (Exception $e) {
            $id = $inputSet->getAF()->getId();
            $this->logger->warning("Error while calculating AF '$id' results", ['exception' => $e]);

            $inputSet->setCalculationComplete(false);
            $inputSet->clearOutputSet();
        }
    }
}
