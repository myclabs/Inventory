<?php
/**
 * @author matthieu.napoli
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;

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
     * @param AF_Model_Input $input
     * @return AF_Service_InputHistoryService_Entry[]
     */
    public function getInputHistory(AF_Model_Input $input)
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
            if ($input instanceof AF_Model_Input_Numeric && (! $value instanceof Calc_UnitValue)) {
                continue;
            }
            // Filtre les valeurs texte
            if ($input instanceof AF_Model_Input_Text && !is_string($value)) {
                continue;
            }
            // Filtre les checkbox
            if ($input instanceof AF_Model_Input_Checkbox && !is_bool($value)) {
                continue;
            }
            // Filtre les sélections simples
            if ($input instanceof AF_Model_Input_Select_Single && !is_string($value)) {
                continue;
            }
            // Filtre les sélections multiples
            if ($input instanceof AF_Model_Input_Select_Multi && (! $value instanceof Collection)) {
                continue;
            }

            // Valeur des sélections simples
            if ($input instanceof AF_Model_Input_Select_Single) {
                /** @var AF_Model_Component_Select_Single $component */
                $component = $input->getComponent();
                try {
                    $value = $component->getOptionByRef($value);
                } catch (Core_Exception_NotFound $e) {
                    continue;
                }
            }

            // Valeur des sélections multiples
            if ($input instanceof AF_Model_Input_Select_Multi) {
                $newValue = [];

                /** @var AF_Model_Component_Select_Multi $component */
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

            $entries[] = new AF_Service_InputHistoryService_Entry($input, $logEntry->getLoggedAt(), $value);
        }

        return $entries;
    }
}
