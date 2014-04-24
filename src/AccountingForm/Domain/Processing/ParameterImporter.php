<?php

namespace AccountingForm\Domain\Processing;

use AccountingForm\Domain\ArrayValueSet;
use AccountingForm\Domain\Processing\ParameterImporter\DynamicParameterCoordinate;
use AccountingForm\Domain\Processing\ParameterImporter\FixedParameterCoordinate;
use AccountingForm\Domain\Processing\ParameterImporter\ParameterCoordinate;
use AccountingForm\Domain\ValueSet;
use AF\Domain\Algorithm\AlgoConfigurationError;
use Core_Exception_NotFound;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Parameter\Application\Service\ParameterService;
use Parameter\Domain\Family\Dimension;
use Parameter\Domain\Family\Family;
use Parameter\Domain\Family\FamilyReference;
use Parameter\Domain\Family\Member;

/**
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 */
class ParameterImporter implements ProcessingStep
{
    /**
     * @var ParameterService
     */
    private $parameterService;

    /**
     * @var string
     */
    protected $keyName;

    /**
     * @var FamilyReference
     */
    protected $familyReference;

    /**
     * @var ParameterCoordinate[]|Collection
     */
    protected $coordinates;

    public function __construct(ParameterService $parameterService, $keyName)
    {
        $this->parameterService = $parameterService;
        $this->keyName = $keyName;

        $this->algos = new ArrayCollection();
        $this->coordinates = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ValueSet $values)
    {
        $coordinates = [];
        foreach ($this->coordinates as $parameterCoordinate) {
            $dimensionRef = $parameterCoordinate->getDimensionRef();
            $coordinates[$dimensionRef] = $parameterCoordinate->getMember($values);
        }

        $value = $this->parameterService->getFamilyValueByCoordinates($this->getFamily(), $coordinates);

        if (!$value) {
            throw new ProcessingException(sprintf(
                'No value was found in family %s for coordinates %s',
                $this->familyReference->getFamilyRef(),
                implode(', ', $coordinates)
            ));
        }

        $output = new ArrayValueSet();
        $output->set($this->keyName, $value);

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $errors = [];

        // Vérifie que la famille liée est bien trouvable
        try {
            $family = $this->parameterService->getFamily($this->familyReference);
        } catch (Core_Exception_NotFound $e) {
            $errors[] = new AlgoConfigurationError(
                __('Algo', 'configControl', 'invalidFamily', [
                    'REF_ALGO'   => $this->keyName,
                    'REF_FAMILY' => $this->familyReference->getFamilyRef()
                ])
            );
            return $errors;
        }

        // Vérifie la cohérence entre les dimensions de la famille et les coordonnées
        $dimensions = $family->getDimensions();
        $coordinates = $this->coordinates;
        foreach ($dimensions as $dimension) {
            $match = false;
            foreach ($coordinates as $coordinate) {
                if ($dimension === $coordinate->getDimension()) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                $errors[] = new AlgoConfigurationError(
                    __('Algo', 'configControl', 'missingCoordinate', [
                        'REF_ALGO'      => $this->keyName,
                        'REF_DIMENSION' => $dimension->getLabel()
                    ])
                );
            }
        }
        foreach ($coordinates as $coordinate) {
            try {
                $coordinate->getDimension();
            } catch (Core_Exception_NotFound $e) {
                $errors[] = new AlgoConfigurationError(
                    __('Algo', 'configControl', 'invalidDimension', [
                        'REF_ALGO'      => $this->keyName,
                        'REF_DIMENSION' => $coordinate->getDimensionRef()
                    ])
                );
            }
        }

        // Vérifie les index
        if (count($errors) == 0) {
            foreach ($this->coordinates as $coordinate) {
                $errors = array_merge($errors, $coordinate->validate());
            }
        }

        return $errors;
    }

    /**
     * @return Family
     */
    public function getFamily()
    {
        return $this->parameterService->getFamily($this->familyReference);
    }

    /**
     * @param Family $family
     */
    public function setFamily(Family $family)
    {
        $this->familyReference = $family->getFamilyReference();
        // Supprime les coordonnées
        $this->coordinates->clear();
    }

    public function bindDimensionToMember(Dimension $dimension, Member $member)
    {
        $coordinate = $this->getCoordinateForDimension($dimension->getRef());
        if ($coordinate) {
            $this->coordinates->removeElement($coordinate);
        }

        $this->coordinates[] = new FixedParameterCoordinate($dimension->getRef(), $member->getRef());
    }

    public function bindDimensionToExpression(Dimension $dimension, $expression)
    {
        $coordinate = $this->getCoordinateForDimension($dimension->getRef());
        if ($coordinate) {
            $this->coordinates->removeElement($coordinate);
        }

        $this->coordinates[] = new DynamicParameterCoordinate($dimension->getRef(), $expression);
    }

    /**
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * @param string $keyName
     */
    public function setKeyName($keyName)
    {
        $this->keyName = (string) $keyName;
    }

    /**
     * @param string $dimensionRef
     * @return ParameterCoordinate|null
     */
    private function getCoordinateForDimension($dimensionRef)
    {
        foreach ($this->coordinates as $coordinate) {
            if ($coordinate->getDimensionRef() === $dimensionRef) {
                return $coordinate;
            }
        }

        return null;
    }
}
