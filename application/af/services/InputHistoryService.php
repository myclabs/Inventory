<?php
/**
 * @author matthieu.napoli
 */

use AF\Domain\AF\Component\Select\SelectSingle;
use AF\Domain\AF\Component\Select\SelectMulti;
use AF\Domain\AF\Input\Input;
use AF\Domain\AF\Input\TextFieldInput;
use AF\Domain\AF\Input\NumericFieldInput;
use AF\Domain\AF\Input\CheckboxInput;
use AF\Domain\AF\Input\Select\SelectSingleInput;
use AF\Domain\AF\Input\Select\SelectMultiInput;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use User\Domain\User;

/**
 * Service responsable de l'historique des saisies des AF
 */
class AF_Service_InputHistoryService
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
     * @return AF_Service_InputHistoryService_Entry[]
     */
    public function getInputHistory(Input $input)
    {
        $entries = [];

        /** @var LogEntryRepository $repository */
        $repository = $this->entityManager->getRepository('Gedmo\Loggable\Entity\LogEntry');
        /** @var LogEntry[] $logEntries */
        $logEntries = $repository->getLogEntries($input);

        foreach ($logEntries as $logEntry) {
            $data = $logEntry->getData();
            $value = $data['value'];

            // Filtre les valeurs numériques
            if ($input instanceof NumericFieldInput && (! $value instanceof Calc_UnitValue)) {
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
            if ($input instanceof SelectMultiInput && (! $value instanceof Collection)) {
                continue;
            }

            // Valeur des sélections simples
            if ($input instanceof SelectSingleInput) {
                /** @var SelectSingleInput $component */
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

                /** @var \AF\Domain\AF\Component\Select\SelectMulti $component */
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

            $entries[] = new AF_Service_InputHistoryService_Entry($input, $logEntry->getLoggedAt(), $value, $author);
        }

        return $entries;
    }
}
