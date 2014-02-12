<?php

namespace AF\Domain\Algorithm\Numeric;

use AF\Domain\Algorithm\AlgoConfigurationError;
use AF\Domain\Algorithm\ParameterCoordinate\ParameterCoordinate;
use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\ExecutionException;
use Calc_UnitValue;
use Core_Exception_NotFound;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Parameter\Application\Service\ParameterService;
use Parameter\Domain\Family\Family;
use Unit\UnitAPI;

/**
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 */
class NumericParameterAlgo extends NumericAlgo
{
    /**
     * @var string
     */
    protected $familyRef;

    /**
     * @var ParameterCoordinate[]|Collection
     */
    protected $parameterCoordinates;

    public function __construct()
    {
        parent::__construct();
        $this->algos = new ArrayCollection();
        $this->parameterCoordinates = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputSet $inputSet)
    {
        $coordinates = [];
        foreach ($this->getParameterCoordinates() as $parameterCoordinate) {
            $dimensionRef = $parameterCoordinate->getDimensionRef();
            $coordinates[$dimensionRef] = $parameterCoordinate->getMember($inputSet);
        }

        /** @var ParameterService $parameterService */
        $parameterService = \Core\ContainerSingleton::getContainer()->get(ParameterService::class);

        $value = $parameterService->getFamilyValueByCoordinates($this->getFamily(), $coordinates);

        if (!$value) {
            throw new ExecutionException(sprintf(
                'No value was found for parameter %s and coordinates %s in algorithm %s',
                $this->familyRef,
                implode(', ', $coordinates),
                $this->ref
            ));
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();

        /** @var ParameterService $parameterService */
        $parameterService = \Core\ContainerSingleton::getContainer()->get(ParameterService::class);

        // Vérifie que la famille liée est bien trouvable
        try {
            $family = $parameterService->getFamily($this->familyRef);
        } catch (Core_Exception_NotFound $e) {
            $configError = new AlgoConfigurationError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'invalidFamily', [
                'REF_ALGO'   => $this->ref,
                'REF_FAMILY' => $this->familyRef
            ]), true);
            $errors[] = $configError;
            return $errors;
        }

        // Vérifie la cohérence entre les dimensions de la famille et les coordonnées
        $dimensions = $family->getDimensions();
        $coordinates = $this->getParameterCoordinates();
        foreach ($dimensions as $dimension) {
            $match = false;
            foreach ($coordinates as $coordinate) {
                if ($dimension === $coordinate->getDimension()) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                $configError = new AlgoConfigurationError();
                $configError->isFatal(true);
                $configError->setMessage(__('Algo', 'configControl', 'missingCoordinate', [
                    'REF_ALGO'      => $this->ref,
                    'REF_DIMENSION' => $dimension->getLabel()
                ]), true);
                $errors[] = $configError;
            }
        }
        foreach ($coordinates as $coordinate) {
            try {
                $coordinate->getDimension();
            } catch (Core_Exception_NotFound $e) {
                $configError = new AlgoConfigurationError();
                $configError->isFatal(true);
                $configError->setMessage(__('Algo', 'configControl', 'invalidDimension', [
                    'REF_ALGO'      => $this->ref,
                    'REF_DIMENSION' => $coordinate->getDimensionRef()
                ]), true);
                $errors[] = $configError;
            }
        }

        // Vérifie les index
        if (count($errors) == 0) {
            foreach ($this->getParameterCoordinates() as $parameter) {
                $errors = array_merge($errors, $parameter->checkConfiguration());
            }
        }

        return $errors;
    }

    /**
     * @return Family
     */
    public function getFamily()
    {
        /** @var ParameterService $parameterService */
        $parameterService = \Core\ContainerSingleton::getContainer()->get(ParameterService::class);

        return $parameterService->getFamily($this->familyRef);
    }

    /**
     * @param Family $family
     */
    public function setFamily(Family $family)
    {
        $this->familyRef = $family->getRef();
        // Supprime les coordonnées pour l'ancienne famille
        $this->parameterCoordinates->clear();
    }

    /**
     * @return ParameterCoordinate[]
     */
    public function getParameterCoordinates()
    {
        return $this->parameterCoordinates;
    }

    /**
     * @param ParameterCoordinate $parameterCoordinates
     */
    public function addParameterCoordinates(ParameterCoordinate $parameterCoordinates)
    {
        if (!$this->hasParameterCoordinates($parameterCoordinates)) {
            $this->parameterCoordinates->add($parameterCoordinates);
            $parameterCoordinates->setAlgoParameter($this);
        }
    }

    /**
     * @param ParameterCoordinate $parameterCoordinates
     */
    public function removeParameterCoordinates(ParameterCoordinate $parameterCoordinates)
    {
        if ($this->hasParameterCoordinates($parameterCoordinates)) {
            $this->parameterCoordinates->remove($parameterCoordinates);
        }
    }

    /**
     * @param \AF\Domain\Algorithm\ParameterCoordinate\ParameterCoordinate $parameterCoordinates
     * @return bool
     */
    public function hasParameterCoordinates(ParameterCoordinate $parameterCoordinates)
    {
        return $this->parameterCoordinates->contains($parameterCoordinates);
    }

    /**
     * Méthode permettant de récupérer l'unité associée à un algorithme.
     * Cette méthode est en particulier utilisée lors du controle de la configuration des algos.
     *
     * @return UnitAPI
     */
    public function getUnit()
    {
        return $this->getFamily()->getValueUnit();
    }
}
