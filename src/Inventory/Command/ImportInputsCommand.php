<?php

namespace Inventory\Command;

use AF\Domain\Component\Checkbox;
use AF\Domain\Component\Component;
use AF\Domain\Component\Group;
use AF\Domain\Component\NumericField;
use AF\Domain\Component\Select\SelectMulti;
use AF\Domain\Component\Select\SelectSingle;
use AF\Domain\Component\SubAF\NotRepeatedSubAF;
use AF\Domain\Component\SubAF\RepeatedSubAF;
use AF\Domain\Component\TextField;
use AF\Domain\Input\CheckboxInput;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\Input\Select\SelectMultiInput;
use AF\Domain\Input\Select\SelectSingleInput;
use AF\Domain\Input\TextFieldInput;
use AF\Domain\InputService;
use AF\Domain\InputSet\PrimaryInputSet;
use Calc_UnitValue;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Doctrine\ORM\EntityManager;
use Inventory\Command\PopulateDB\BasicDataSet;
use Inventory\Command\PopulateDB\TestDataSet;
use Inventory\Command\PopulateDB\TestDWDataSet;
use League\Csv\Reader;
use Orga\Domain\Axis;
use Orga\Domain\Granularity;
use Orga\Domain\Member;
use Orga\Domain\Service\ETL\ETLDataService;
use Orga\Domain\Workspace;
use SplFileObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Unit\UnitAPI;

/**
 * Importe un fichier CSV d'input.
 *
 * @author benjamin.bertin
 */
class ImportInputsCommand extends Command
{
    /**
     * @Inject
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @Inject
     * @var ETLDataService
     */
    protected $etlDataService;

    /**
     * @Inject
     * @var InputService
     */
    protected $inputService;

    protected function configure()
    {
        $this->setName('importInputs')
            ->setDescription('Importe des inputs depuis un fichier CSV')
            ->addArgument('workspaceId', InputArgument::REQUIRED, "L'id du workspace")
            ->addArgument('granularity', InputArgument::REQUIRED, 'La granularité des inputs, '
                                                                 .'les refs des axes sont séparés par un #')
            ->addArgument('csvFile', InputArgument::REQUIRED, 'Le fichier CSV des inputs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $axesInput = $input->getArgument('granularity');
        $axesRefs = explode('#', $axesInput);
        $workspaceId = $input->getArgument('workspaceId');

        // workspace lookup
        /* @var Workspace $workspace */
        $workspace = $this->entityManager->find(Workspace::class, $workspaceId);
        if (null === $workspace) {
            throw new Core_Exception_InvalidArgument("Le workspace $workspaceId n'existe pas");
        }

        // axes lookup
        /* @var Axis[] $axes */
        $axes = [];
        foreach ($axesRefs as $axisRef) {
            $axis = $this->entityManager->getRepository(Axis::class)->findOneBy(['ref' => $axisRef, 'workspace' => $workspaceId]);
            if (null === $axis) {
                throw new Core_Exception_InvalidArgument("L'axe $axisRef n'existe pas");
            }
            $axes[$axisRef] = $axis;
        }

        // granularity lookup corresponding to the axes
        $granularity = null;
        foreach ($workspace->getInputGranularities() as $inputGranularity) {
            if (sizeof($inputGranularity->getAxes()) != sizeof($axes)) {
                continue;
            }
            foreach($axes as $axis) {
                if (!$inputGranularity->hasAxis($axis)) {
                    continue;
                }
            }
            $granularity = $inputGranularity;
        }
        if (null === $granularity) {
            throw new Core_Exception_NotFound('La granularité de saisie correspondant aux axes '.$axesInput.' est introuvable');
        }
        $output->writeln("<comment>Granularité ".$granularity->getLabel()->get('fr')."</comment>");

        $reader = Reader::createFromFileObject(new SplFileObject($input->getArgument('csvFile')));

        $csvFields = ['field', 'value', 'uncertainty', 'unit'];
        $csvFields = array_merge($axesRefs, $csvFields);
        $data = [];
        foreach ($reader->fetchAssoc($csvFields) as $row) {
            $cellKey = '';
            $members = [];
            foreach ($axes as $axisRef => $axis) {
                if (null === $row[$axisRef])
                    continue(2);
                $cellKey .= $row[$axisRef].'#';
                $members[] = $this->entityManager->getRepository(Member::class)->findOneBy(
                    ['ref' => $row[$axisRef], 'axis' => $axis]);
            }
            if (!array_key_exists($cellKey, $data)) {
                $data[$cellKey] = [
                    'members' => $members,
                    'values' => []
                ];
            }
            $data[$cellKey]['values'][] = [
                'field' => $row['field'],
                'value' => $row['value'],
                'uncertainty' => $row['uncertainty'],
                'unit' => $row['unit']
            ];
        }
        //$output->writeln(var_dump($data));

        $this->entityManager->beginTransaction();
        // @todo import the data from the CSV file using the package http://csv.thephpleague.com/
        foreach ($data as $cellKey => $cellData) {
            $output->writeln('    <info>Inserting in cell '.$cellKey.'</info>');
            $this->setInput($granularity, $cellData['members'], $cellData['values']);
        }
        $this->entityManager->flush();
        $this->entityManager->commit();
    }

    /**
     * @param \Orga\Domain\Granularity $granularity
     * @param Member[] $members
     * @param array $inputsData
     * @param bool $finished
     * @throws Core_Exception_InvalidArgument
     * @throws Core_Exception_NotFound
     * @throws \Core_Exception_Duplicate
     * @throws \Core_Exception_TooMany
     * @throws \Core_Exception_UndefinedAttribute
     * @internal param array $values
     */
    protected function setInput(Granularity $granularity, array $members, array $inputsData, $finished = false)
    {
        $inputCell = $granularity->getCellByMembers($members);
        $inputConfigGranularity = $granularity->getInputConfigGranularity();
        if ($granularity === $inputConfigGranularity) {
            $aF = $inputCell->getSubCellsGroupForInputGranularity($granularity)->getAF();
        } else {
            $aF = $inputCell->getParentCellForGranularity($inputConfigGranularity)->getSubCellsGroupForInputGranularity(
                $granularity
            )->getAF();
        }

        $inputSetPrimary = new PrimaryInputSet($aF);

        foreach ($inputsData as $row) {
            $component = Component::loadByRef($row['field'], $aF);
            if (($component instanceof NotRepeatedSubAF)
                || ($component instanceof RepeatedSubAF)
                || ($component instanceof Group)) {
                continue;
            }

            if ($component instanceof NumericField) {
                // Champ numérique
                $inputType = NumericFieldInput::class;
                $value = new Calc_UnitValue(new UnitAPI($row['unit']), $row['value'], $row['uncertainty']);
            } elseif ($component instanceof TextField) {
                // Champ texte
                $inputType = TextFieldInput::class;
            } elseif ($component instanceof Checkbox) {
                // Champ checkbox
                $inputType = CheckboxInput::class;
            } elseif ($component instanceof SelectSingle) {
                // Champ de sélection simple
                $inputType = SelectSingleInput::class;
                $value = $component->getOptionByRef($row['value']);
            } elseif ($component instanceof SelectMulti) {
                // Champ de sélection multiple
                $inputType = SelectMultiInput::class;
            }

            /** @var Input $input */
            $input = new $inputType($inputSetPrimary, $component);
            $inputSetPrimary->setInputForComponent($component, $input);
            $input->setValue($value);
        }

        $this->inputService->updateResults($inputSetPrimary);
        $inputSetPrimary->markAsFinished($finished);
        $inputSetPrimary->save();

        $inputCell->setAFInputSetPrimary($inputSetPrimary);
        $inputCell->updateInputStatus();
        $this->etlDataService->populateDWCubesWithCellInputResults($inputCell);
    }

}
