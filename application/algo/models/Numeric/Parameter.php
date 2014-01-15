<?php

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Techno\Application\Service\TechnoService;
use Techno\Domain\Family\Family;
use Unit\UnitAPI;

/**
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 */
class Algo_Model_Numeric_Parameter extends Algo_Model_Numeric
{
    /**
     * @var string
     */
    protected $familyRef;

    /**
     * @var Algo_Model_ParameterCoordinate[]|Collection
     */
    protected $parameterCoordinates;

    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct();
        $this->algos = new ArrayCollection();
        $this->parameterCoordinates = new ArrayCollection();
    }

    /**
     * Exécution de l'algorithme
     * @param Algo_Model_InputSet $inputSet
     * @throws Algo_Model_ExecutionException No value was found for the parameter and the coordinates
     * @return Calc_UnitValue
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        $coordinates = [];
        foreach ($this->getParameterCoordinates() as $parameterCoordinate) {
            $dimensionRef = $parameterCoordinate->getDimensionRef();
            $coordinates[$dimensionRef] = $parameterCoordinate->getMember($inputSet);
        }

        /** @var TechnoService $technoService */
        $technoService = \Core\ContainerSingleton::getContainer()->get(TechnoService::class);

        $value = $technoService->getFamilyValueByCoordinates($this->getFamily(), $coordinates);

        if (!$value) {
            throw new Algo_Model_ExecutionException(sprintf(
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

        /** @var TechnoService $technoService */
        $technoService = \Core\ContainerSingleton::getContainer()->get(TechnoService::class);

        // Vérifie que la famille liée est bien trouvable
        try {
            $family = $technoService->getFamily($this->familyRef);
        } catch (Core_Exception_NotFound $e) {
            $configError = new Algo_ConfigError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'invalidFamily', [
                'REF_ALGO' => $this->ref,
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
            if (! $match) {
                $configError = new Algo_ConfigError();
                $configError->isFatal(true);
                $configError->setMessage(__('Algo', 'configControl', 'missingCoordinate', [
                    'REF_ALGO' => $this->ref,
                    'REF_DIMENSION' => $dimension->getLabel()
                ]), true);
                $errors[] = $configError;
            }
        }
        foreach ($coordinates as $coordinate) {
            try {
                $coordinate->getDimension();
            } catch (Core_Exception_NotFound $e) {
                $configError = new Algo_ConfigError();
                $configError->isFatal(true);
                $configError->setMessage(__('Algo', 'configControl', 'invalidDimension', [
                    'REF_ALGO' => $this->ref,
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
        /** @var TechnoService $technoService */
        $technoService = \Core\ContainerSingleton::getContainer()->get(TechnoService::class);

        return $technoService->getFamily($this->familyRef);
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
     * @return Algo_Model_ParameterCoordinate[]
     */
    public function getParameterCoordinates()
    {
        return $this->parameterCoordinates;
    }

    /**
     * @param Algo_Model_ParameterCoordinate $parameterCoordinates
     */
    public function addParameterCoordinates(Algo_Model_ParameterCoordinate $parameterCoordinates)
    {
        if (!$this->hasParameterCoordinates($parameterCoordinates)) {
            $this->parameterCoordinates->add($parameterCoordinates);
            $parameterCoordinates->setAlgoParameter($this);
        }
    }

    /**
     * @param Algo_Model_ParameterCoordinate $parameterCoordinates
     */
    public function removeParameterCoordinates(Algo_Model_ParameterCoordinate $parameterCoordinates)
    {
        if ($this->hasParameterCoordinates($parameterCoordinates)) {
            $this->parameterCoordinates->remove($parameterCoordinates);
        }
    }

    /**
     * @param Algo_Model_ParameterCoordinate $parameterCoordinates
     * @return bool
     */
    public function hasParameterCoordinates(Algo_Model_ParameterCoordinate $parameterCoordinates)
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
