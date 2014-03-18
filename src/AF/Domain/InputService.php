<?php

namespace AF\Domain;

use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\InputService\InputSetUpdater;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Techno\Domain\Family\MemberNotFoundException;

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
     * @param AF              $af Permet d'uiliser un AF différent de celui de la saisie
     */
    public function updateResults(PrimaryInputSet $inputSet, AF $af = null)
    {
        if (!$af) {
            $af = $inputSet->getAF();
        }

        // MAJ le pourcentage de complétion
        $inputSet->updateCompletion();

        // La saisie vient d'être modifiée, donc on la force à "non terminée"
        $inputSet->markAsFinished(false);

        // Si la saisie est incomplète
        if (!$inputSet->isInputComplete()) {
            $inputSet->clearOutputSet();
            return;
        }

        // Calcule les résultats
        try {
            $af->execute($inputSet);
            $inputSet->setCalculationComplete(true);
            $inputSet->getOutputSet()->calculateTotals();
        } catch (MemberNotFoundException $e) {
            $message = __('AF', 'inputInput', 'completeInputSavedCalculationErrorUnknownTechnoMember', [
                'FAMILY'    => $e->getFamily(),
                'DIMENSION' => $e->getDimension(),
                'MEMBER'    => $e->getMember(),
            ]);
            $inputSet->setCalculationComplete(false, $message);
            $inputSet->clearOutputSet();
        } catch (Exception $e) {
            $ref = $inputSet->getAF()->getRef();
            $this->logger->warning("Error while calculating AF '$ref' results", ['exception' => $e]);

            $inputSet->setCalculationComplete(false);
            $inputSet->clearOutputSet();
        }
    }
}
