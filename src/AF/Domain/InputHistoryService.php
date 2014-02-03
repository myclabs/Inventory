<?php

namespace AF\Domain;

use AF\Domain\Component\Select\SelectMulti;
use AF\Domain\Component\Select\SelectSingle;
use AF\Domain\Input\Input;
use AF\Domain\Input\TextFieldInput;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\Input\CheckboxInput;
use AF\Domain\Input\Select\SelectSingleInput;
use AF\Domain\Input\Select\SelectMultiInput;
use AF\Domain\InputHistoryService\Entry;
use Calc_UnitValue;
use Core_Exception_NotFound;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use User\Domain\User;

/**
 * Service responsable de l'historique des saisies des AF.
 *
 * @author matthieu.napoli
 */
class InputHistoryService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Input $input
     * @return Entry[]
     */
    public function getInputHistory(Input $input)
    {
        $entries = [];

        /** @var LogEntryRepository $repository */
        $repository = $this->entityManager->getRepository(\Gedmo\Loggable\Entity\LogEntry::class);
        /** @var LogEntry[] $logEntries */
        $logEntries = $repository->getLogEntries($input);

        foreach ($logEntries as $logEntry) {
            $data = $logEntry->getData();
            $value = $data['value'];

            // Filtre les valeurs numériques
            if ($input instanceof NumericFieldInput && (!$value instanceof Calc_UnitValue)) {
                continue;
            }
            // Filtre les valeurs texte
            if ($input instanceof TextFieldInput && !is_string($value)) {
                continue;
            }
            // Filtre les checkbox
            if ($input instanceof CheckboxInput && !is_bool($value)) {
                continue;
            }
            // Filtre les sélections simples
            if ($input instanceof SelectSingleInput && !(is_string($value) || is_null($value))) {
                continue;
            }
            // Filtre les sélections multiples
            if ($input instanceof SelectMultiInput && (!$value instanceof Collection)) {
                continue;
            }

            // Valeur des sélections simples
            if ($input instanceof SelectSingleInput) {
                /** @var SelectSingle $component */
                $component = $input->getComponent();
                if ($value) {
                    try {
                        $value = $component->getOptionByRef($value);
                    } catch (Core_Exception_NotFound $e) {
                        continue;
                    }
                }
            }

            // Valeur des sélections multiples
            if ($input instanceof SelectMultiInput) {
                $newValue = [];

                /** @var SelectMulti $component */
                $component = $input->getComponent();
                foreach ($value as $refOption) {
                    try {
                        $newValue[] = $component->getOptionByRef($refOption);
                    } catch (Core_Exception_NotFound $e) {
                        continue;
                    }
                }

                $value = $newValue;
            }

            // Author
            if ($logEntry->getUsername()) {
                $author = User::load($logEntry->getUsername());
            } else {
                $author = null;
            }

            $entries[] = new Entry($input, $logEntry->getLoggedAt(), $value, $author);
        }

        return $entries;
    }
}
