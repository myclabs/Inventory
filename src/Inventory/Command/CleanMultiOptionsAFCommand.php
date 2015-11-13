<?php

namespace Inventory\Command;

use AF\Domain\AF;
use AF\Domain\Component\Select\SelectMulti;
use AF\Domain\Input\Select\SelectMultiInput;
use Doctrine\ORM\EntityManager;
use Orga\Domain\Cell;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Nettoie les options des champs de sélection multiples
 * qui ne sont plus présentes dans le select multi
 */
class CleanMultiOptionsAFCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('AF:clean-multi-options')
            ->setDescription('Corrige les selections multiple avec option_verre')
            ->addOption('clean', 'c', InputOption::VALUE_NONE, 'Clean the options');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // pour tous les AFs
        //  pour tous les input sets de l'AF
        //      pour tous les elements multi de l'AF
        //          on récupère les valeurs saisies pour le multi dans l'input set
        //          pour toutes les options du multi
        //              si l'option est dans les valeurs saisies -> on garde l'option
        //          on met à jour l'input
        $clean = $input->getOption('clean');
        $AFs = AF::loadList();
        $optionsNotFound = [];
        /** @var AF $af */
        foreach ($AFs as $af) {
            $output->writeln('<info>Cleaning AF '.$af->getLibrary()->getLabel()->get('fr').' / '.$af->getLabel()->get('fr').' (id: '.$af->getId().')</info>');
            foreach ($af->getInputSets() as $inputSet) {
                $cell = Cell::loadByAFInputSetPrimary($inputSet);
                /** @var SelectMulti $multi */
                foreach ($af->getElementsByType(SelectMulti::class) as $multi) {
                    /** @var SelectMultiInput $input */
                    $input = $inputSet->getInputForComponent($multi);
                    $values = $input->getValue();
                    $options = [];
                    $optionsInMulti = [];
                    foreach ($multi->getOptions() as $option) {
                        if (in_array($option->getRef(), $values)) {
                            $options[] = $option;
                        }
                        $optionsInMulti[] = $option->getRef();
                    }
                    foreach ($values as $value) {
                        if (!in_array($value, $optionsInMulti)) {
                            $output->writeln('  <comment>Option not found in multi (id: '.$multi->getId().') for cell '.$cell->getLabel()->get('fr').' (cell id: '.$cell->getId().' - inputSet id: '.$inputSet->getId().'): '.$value.'</comment>');
                            $optionNotFound = 'Library: '.$multi->getAf()->getLibrary()->getLabel()->get('fr').' (id: '.$multi->getAf()->getLibrary()->getId().')'
                                              .' AF: '.$multi->getAf()->getLabel()->get('fr').' (id: '.$multi->getAf()->getId().')'
                                              .' multi: '.$multi->getLabel()->get('fr').' (ref: '.$multi->getRef().')'
                                              .' option ref: '.$value;
                            if (!in_array($optionNotFound, $optionsNotFound)) {
                                $optionsNotFound[] = $optionNotFound;
                            }
                        }
                    }
                    if ($clean) $input->setValue($options);
                }
            }
        }
        if ($clean) $this->entityManager->flush();
        $output->writeln('<info>Options not found summary:</info>');
        array_walk($optionsNotFound, function ($option) use ($output) {
            $output->writeln($option);
        });
        $output->writeln('<comment>Patch applied</comment>');
    }
}