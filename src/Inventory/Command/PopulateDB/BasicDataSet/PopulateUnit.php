<?php

namespace Inventory\Command\PopulateDB\BasicDataSet;

use Inventory\Command\PopulateDB\BasicDataSet\Unit\PopulateDiscreteUnit;
use Inventory\Command\PopulateDB\BasicDataSet\Unit\PopulateExtendedUnit;
use Inventory\Command\PopulateDB\BasicDataSet\Unit\PopulateExtension;
use Inventory\Command\PopulateDB\BasicDataSet\Unit\PopulatePhysicalQuantity;
use Inventory\Command\PopulateDB\BasicDataSet\Unit\PopulateStandardUnit;
use Inventory\Command\PopulateDB\BasicDataSet\Unit\PopulateUnitSystem;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author matthieu.napoli
 */
class PopulateUnit
{
    /**
     * @Inject
     * @var PopulateUnitSystem
     */
    private $populateUnitSystem;

    /**
     * @Inject
     * @var PopulatePhysicalQuantity
     */
    private $populatePhysicalQuantity;

    /**
     * @Inject
     * @var PopulateStandardUnit
     */
    private $populateStandardUnit;

    /**
     * @Inject
     * @var PopulateDiscreteUnit
     */
    private $populateDiscreteUnit;

    /**
     * @Inject
     * @var PopulateExtension
     */
    private $populateExtension;

    /**
     * @Inject
     * @var PopulateExtendedUnit
     */
    private $populateExtendedUnit;

    public function run(OutputInterface $output)
    {
        $output->writeln('  <info>Populating Units</info>');

        $this->populateUnitSystem->run();
        $this->populatePhysicalQuantity->run();
        $this->populateStandardUnit->run();
        $this->populatePhysicalQuantity->update();
        $this->populateDiscreteUnit->run();
        $this->populateExtension->run();
        $this->populateExtendedUnit->run();
    }
}
