<?php

namespace Inventory\Command\PopulateDB\Base;

use Classification\Domain\IndicatorAxis;
use Classification\Domain\Context;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Indicator;
use Classification\Domain\AxisMember;
use Classification\Domain\IndicatorLibrary;
use Symfony\Component\Console\Output\OutputInterface;
use Unit\UnitAPI;

/**
 * Remplissage de la base de données avec des données de test
 */
abstract class AbstractPopulateClassif
{
    // Création des axes.
    //  + createAxis : -
    // Params : ref, label
    // OptionalParams : Axis parent=null

    // Création des membres.
    //  + createMember : -
    // Params : Axis, ref, label
    // OptionalParams : [Member] parents=[]

    // Création des indicateurs.
    //  + createIndicator : -
    // Params : ref, label, unitRef
    // OptionalParams : ratioUnitRef=unitRef

    // Création des contextes.
    //  + createContext : -
    // Params : ref, label

    // Création des contexte-indicateurs.
    //  + createContextIndicator : -
    // Params : Context, Indicator
    // OptionalParams : [Axis]=[]

    abstract public function run(OutputInterface $output);

    /**
     * @param string $ref
     * @param string $label
     * @param \Classification\Domain\IndicatorAxis|null $narrower
     * @return \Classification\Domain\IndicatorAxis
     */
    protected function createAxis($ref, $label, IndicatorAxis $narrower = null)
    {
        $axis = new IndicatorAxis();
        $axis->setRef($ref);
        $axis->setLabel($label);
        if ($narrower !== null) {
            $axis->setDirectNarrower($narrower);
        }
        $axis->save();
        return $axis;
    }

    /**
     * @param \Classification\Domain\IndicatorAxis $axis
     * @param string $ref
     * @param string $label
     * @param array $parents
     * @return \Classification\Domain\AxisMember
     */
    protected function createMember(IndicatorAxis $axis, $ref, $label, array $parents = [])
    {
        $member = new AxisMember();
        $member->setAxis($axis);
        $member->setRef($ref);
        $member->setLabel($label);
        foreach ($parents as $directParent) {
            $member->addDirectParent($directParent);
        }
        $member->save();
        return $member;
    }

    /**
     * @param IndicatorLibrary $library
     * @param string           $ref
     * @param string           $label
     * @param string           $unitRef
     * @param string|null      $ratioUnitRef
     * @return Indicator
     */
    protected function createIndicator(IndicatorLibrary $library, $ref, $label, $unitRef, $ratioUnitRef = null)
    {
        $ratioUnit = $ratioUnitRef ? new UnitAPI($ratioUnitRef) : null;

        $indicator = new Indicator($library, $ref, $label, new UnitAPI($unitRef), $ratioUnit);
        $indicator->save();

        $library->addIndicator($indicator);

        return $indicator;
    }

    /**
     * @param string $ref
     * @param string $label
     * @return \Classification\Domain\Context
     */
    protected function createContext($ref, $label)
    {
        $context = new Context();
        $context->setRef($ref);
        $context->setLabel($label);
        $context->save();
        return $context;
    }

    /**
     * @param Context $context
     * @param Indicator $indicator
     * @param \Classification\Domain\IndicatorAxis[] $axes
     * @return \Classification\Domain\ContextIndicator
     */
    protected function createContextIndicator(
        Context $context,
        Indicator $indicator,
        array $axes = []
    ) {
        $contextIndicator = new ContextIndicator();
        $contextIndicator->setContext($context);
        $contextIndicator->setIndicator($indicator);
        foreach ($axes as $axis) {
            $contextIndicator->addAxis($axis);
        }
        $contextIndicator->save();
        return $contextIndicator;
    }
}
