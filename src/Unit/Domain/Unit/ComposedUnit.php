<?php

namespace Unit\Domain\Unit;

use Unit\Domain\Unit\Unit;
use Unit\Domain\Unit\ExtendedUnit;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\Unit\StandardUnit;
use Unit\Domain\Unit\DiscreteUnit;
use Core_Model_Query;
use Core_Exception_NotFound;
use Unit\IncompatibleUnitsException;

/**
 * Unité Composée
 *
 * Value Object
 *
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
class ComposedUnit
{
    /**
     * Référent textuel d'une unité composée.
     * @var string
     */
    protected $ref = null;

    /**
     * Tableau des unités qui compose l'unité composé associé à un exposant.
     *
     * Tableau de la forme array('unit' => $unit, 'exponent' => $exponent);
     * @var array
     */
    protected $components = [];

    /**
     * @param string|null $ref
     */
    public function __construct($ref = null)
    {
        $this->ref = $ref;
        $this->components = $this->getComponentsByRef($this->ref);
    }

    /**
     * Permet de récupérer les composants d'une unité composée.
     * @return array tableau de components
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * Récupère un tableau avec les composants et les exposants à partir du ref.
     * @param string $ref
     * @return array
     */
    private function getComponentsByRef($ref)
    {
        // Tableau qui contiendra les références des unités.
        $componentRefs = [];
        // On parse chacun des caractères de la référence de l'unité composée.
        //  Pour chaque symbole, soit on termine la ref en cours et on l'ajoute au tableau,
        //  soit on continue de la construire.
        $splitRef = '';
        foreach (str_split($ref, 1) as $symbol) {
            if ($symbol == '.' || $symbol == '^') {
                $componentRefs[] = $splitRef;
                $splitRef = '';
            }
            if ($symbol != '.') {
                $splitRef .= $symbol;
            }
        }
        if ($splitRef !== '') {
            $componentRefs[] = $splitRef;
        }

        // Tableau qui contiendra les symboles des unités chargés du model Unit.
        $componentUnits = [];
        // On parse chaque Ref composantes de l'unité composée.
        //  Pour chaque ref, on charge l'unité correspondante ou onr enseigne l'exposant.
        $unitArray = [];
        foreach ($componentRefs as $ref) {
            // Traitement des exposants.
            if (preg_match('#\^-?[0-9]+#', $ref)) {
                $unitArray['exponent'] = preg_replace('#\^#', '', $ref);
            }
            // Traitement des unités.
            if (preg_match('#[a-zA-Z]+#', $ref)) {
                if (isset($unitArray['unit'])) {
                    if (!(isset($unitArray['exponent']))) {
                        $unitArray['exponent'] = '1';
                    }
                    $componentUnits[] = $unitArray;
                    $unitArray = [];
                }
                $unitArray['unit'] = Unit::loadByRef($ref);
            }
        }
        if ($unitArray !== array()) {
            if (!(isset($unitArray['exponent']))) {
                $unitArray['exponent'] = '1';
            }
            $componentUnits[] = $unitArray;
        }

        return $componentUnits;
    }

    /**
     * Récupère le ref d'une unité composé
     * @return string $ref
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Permet de mettre à jour la valeur de l'attribut ref.
     *
     * Si le ref passé en paramètre est null, on test si le tableau des unités est remplit.
     *  S'il est rempli on s'en sert pour construire le ref. Sinon on met le ref null.
     *
     * @param null|string $ref Nouveau ref de l'unité
     */
    public function setRef($ref = null)
    {
        if ($ref != null) {
            $this->ref = $ref;
            $this->components = $this->getComponentsByRef($this->ref);
        } else {
            $ref = '';
            foreach ($this->components as $unitArray) {
                $ref .= $unitArray['unit']->getRef();
                if ($unitArray['exponent'] != 1) {
                    $ref .= '^' . $unitArray['exponent'];
                }
                $ref .= '.';
            }
            $ref = preg_replace('#[\.\s\.]*$#', '', $ref);
            if ($ref != '') {
                $this->ref = $ref;
            } else {
                // Si le tableau de composants est vide on est dans le cas d'une unité sans dimension
                $this->ref = 'un';
            }
        }
    }

    /**
     * Cette méthode renvoie le facteur de conversion "global" associé à une unité composée
     * @return float $conversionFactor Facteur de conversion global
     */
    public function getConversionFactor()
    {
        $conversionFactor = 1;
        foreach ($this->components as $unitArray) {
            if ($unitArray['unit'] instanceof StandardUnit) {
                $conversionFactor *= pow($unitArray['unit']->getMultiplier(), $unitArray['exponent']);
            } elseif ($unitArray['unit'] instanceof ExtendedUnit) {
                $conversionFactorUnitStandard = $unitArray['unit']->getStandardUnit()->getMultiplier();
                $conversionFactorUnitExtension = $unitArray['unit']->getExtension()->getMultiplier();
                $conversionFactUnit = $conversionFactorUnitStandard * $conversionFactorUnitExtension;
                $conversionFactor *= pow($conversionFactUnit, $unitArray['exponent']);
            }
        }
        return $conversionFactor;
    }

    /**
     * Récupère le symbole d'une unité.
     * @return string
     */
    public function getSymbol()
    {
        $leftPart = '';
        $rightPart = '';
        foreach ($this->components as $unitArray) {
            // Pour les exposants positifs on construit le numérateur du symbole de l'unité.
            if ($unitArray['exponent'] > 0) {
                $leftPart .= $unitArray['unit']->getSymbol();
                if ($unitArray['exponent'] > 1) {
                    $leftPart .= $unitArray['exponent'];
                }
                $leftPart .= '.';
            } // Pour les exposants négatifs on construite le dénominateur du symbole de l'unité.
            else {
                if ($unitArray['exponent'] < 0) {
                    $rightPart .= $unitArray['unit']->getSymbol();
                    if ($unitArray['exponent'] < -1) {
                        // pour un exposant négatif on prend la valeur absolue de celui ci.
                        $rightPart .= abs($unitArray['exponent']);
                    }
                    $rightPart .= '.';
                }
            }
        }
        // On supprime le dernier point de séparation à la fin de chaques parties du symbole.
        // Dans le cas ou une des parties est une chaine vide, cela renvoi une chaine vide.
        $leftPart = substr($leftPart, 0, -1);
        $rightPart = substr($rightPart, 0, -1);
        // Si on a une partie négative on sépare le numérateur et le dénominateur avec un trait de fraction
        if ($rightPart != '') {
            return $leftPart . '/' . $rightPart;
        } else {
            // Sinon on ne retourne que la partie positive.
            return $leftPart;
        }
    }

    /**
     * Cette méthode permet de vérifier que deux unités sont compatibles.
     * Cette méthode sera notamment appelée avant des calculs de sommes.
     * @param ComposedUnit $unit
     * @return bool
     */
    public function isEquivalent(ComposedUnit $unit)
    {
        if ($this->getNormalizedUnit() == $unit->getNormalizedUnit()) {
            return true;
        }

        return false;
    }

    /**
     * On converti une unité composée en produit d'unités de référence des grandeurs
     * physiques de base.
     * Cette méthode regroupe les exposants associés à chaque unité de référence
     * @throws \Core_Exception_NotFound
     * @return ComposedUnit $newUnit
     */
    public function getNormalizedUnit()
    {
        $standardUnits = [];
        $otherUnits = [];
        $normalizedUnit = new ComposedUnit();

        // On traite chacune des unités de l'unité composé pour récupérer
        // leur décomposition en grandeurs physiques de base auxquelles on
        // associe leurs unités de références ainsi que l'exposant
        foreach ($this->components as $unitArray) {
            $unit = Unit::loadByRef($unitArray['unit']->getRef());
            if ($unit instanceof StandardUnit) {
                $standardUnits[] = $unitArray;
            } else {
                if (($unit instanceof DiscreteUnit) || ($unit instanceof ExtendedUnit)) {
                    $otherUnits[] = array('unit' => $unit->getReferenceUnit(), 'exponent' => $unitArray['exponent']);
                }
            }
        }
        // On regroupe les exposant des grandeurs identiques.
        $standardUnits = $this->normalizeStandardUnits($standardUnits);
        $otherUnits = $this->normalizeOtherUnits($otherUnits);

        // On fusione les tableau d'unité si besoin
        if (empty($standardUnits) && empty($otherUnits)) {
            throw new Core_Exception_NotFound('The unit does not exist');
        } else {
            // On remplit la nouvelle unité composée avec ses composants qui sont
            //  des unités de références des grandeurs physiques de bases.
            foreach (array_merge($standardUnits, $otherUnits) as $unitArray) {
                if (($unitArray['exponent'] != 0) && ($unitArray['unit'] instanceof Unit)) {
                    $normalizedUnit->components[] = $unitArray;
                }
            }
            usort($normalizedUnit->components, array('Unit\Domain\Unit\ComposedUnit', 'orderComponents'));
            $normalizedUnit->setRef();
            return $normalizedUnit;
        }
    }

    /**
     * Méthode qui permet de récupérer l'unité normalisée d'une unité standard
     * utilisée pour getNormalizedUnit().
     * @param array $standardUnits
     * @return array
     */
    private function normalizeStandardUnits($standardUnits)
    {
        $referenceUnits = array();

        // On construit un tableau des unités de référence pour chaque grandeur physique de base.
        $queryBasePhysicalQuantity = new Core_Model_Query();
        $queryBasePhysicalQuantity->filter->addCondition(PhysicalQuantity::QUERY_ISBASE, true);
        /* @var $physicalQuantity PhysicalQuantity */
        foreach (PhysicalQuantity::loadList($queryBasePhysicalQuantity) as $physicalQuantity) {
            $referenceUnits[$physicalQuantity->getReferenceUnit()->getRef()] = array(
                'unit'     => $physicalQuantity->getReferenceUnit(),
                'exponent' => 0,
            );
        }
        // Pour chaque unité standard, on ajoute l'exposant correspondant ) la composante de base.
        foreach ($standardUnits as $unitArray) {
            foreach ($unitArray['unit']->getNormalizedUnit() as $normalizedArray) {
                foreach ($referenceUnits as $key => $referenceUnitArray) {
                    if ($normalizedArray['unit']->getRef() == $referenceUnitArray['unit']->getRef()) {
                        $referenceUnits[$key]['exponent'] += $normalizedArray['exponent'] * $unitArray['exponent'];
                    }
                }
            }
        }

        return $referenceUnits;
    }

    /**
     * Méthode qui permet de récupérer l'unité normalisée d'une unité discrete ou etendue
     * utilisée pour getNormalizedUnit().
     * @param array $otherUnits
     * @return array
     */
    private function normalizeOtherUnits($otherUnits)
    {
        $finalTab = array();

        foreach ($otherUnits as $unitArray) {
            $exists = false;
            foreach ($finalTab as $key => $existingUnitArray) {
                if ($unitArray['unit']->getRef() == $existingUnitArray['unit']->getRef()) {
                    $finalTab[$key]['exponent'] += $unitArray['exponent'];
                    $exists = true;
                }
            }
            if ($exists === false) {
                $finalTab[] = $unitArray;
            }
        }

        return $finalTab;
    }

    /**
     * Fonction permettant de comparer deux unitArray (Composants d'une unité).
     *  Cette fonction est appelée par usort.
     * @param array $unitArrayA
     * @param array $unitArrayB
     * @return int
     */
    private static function orderComponents($unitArrayA, $unitArrayB)
    {
        if (($unitArrayA['exponent'] * $unitArrayB['exponent']) < 0) {
            if ($unitArrayA['exponent'] > $unitArrayB['exponent']) {
                return -1;
            }
            if ($unitArrayA['exponent'] < $unitArrayB['exponent']) {
                return 1;
            }
        } else {
            $classA = get_class($unitArrayA['unit']);
            $classB = get_class($unitArrayB['unit']);

            if ($classA == $classB) {
                $idA = $unitArrayA['unit']->getKey();
                $idB = $unitArrayB['unit']->getKey();
                if ($idA['id'] < $idB['id']) {
                    return -1;
                }
                if ($idA['id'] > $idB['id']) {
                    return 1;
                }
                return 0;
            }

            if ($classA == 'Unit\Domain\DiscreteUnit') {
                return -1;
            }
            if ($classA == 'Unit\Domain\StandardUnit') {
                return 1;
            }
            if ($classB == 'Unit\Domain\StandardUnit') {
                return -1;
            }
            if ($classB == 'Unit\Domain\DiscreteUnit') {
                return 1;
            }
            return 0;
        }
    }

    /**
     * Méthode qui permet de retourner le produit des unités sous la forme
     *  d'une unité normalisée.
     * @param array $operandes : (API_Unit $unit, int signExponent)
     * @return ComposedUnit
     */
    public function multiply(array $operandes)
    {
        $this->components = array();

        foreach ($operandes as $unitArray) {
            $unit = new ComposedUnit($unitArray['unit']->getRef());
            foreach ($unit->getComponents() as $componentUnitArray) {
                if ($unitArray['signExponent'] == -1) {
                    $componentUnitArray['exponent'] *= -1;
                }
                $this->components[] = $componentUnitArray;
            }
        }

        return $this->getNormalizedUnit();
    }

    /**
     * Sert à ajouter des unités entre elles. Renvoi une unité composée
     * de l'unité de référence de la grandeur physique de base.
     * @param array $components
     * @throws IncompatibleUnitsException
     * @return string $unitResult
     */
    public function calculateSum($components)
    {
        $units = array();
        foreach ($components as $component) {
            $units[] = new ComposedUnit($component);
        }
        foreach ($units as $key => $unit) {
            if (isset($units[$key + 1])) {
                if (!($unit->isEquivalent($units[$key + 1]))) {
                    throw new IncompatibleUnitsException('Units for the sum are incompatible');
                }
            } else {
                $unitResult = $unit->getNormalizedUnit();
            }
        }
        return $unitResult;
    }

    /**
     * Permet d'inverser une unité en inversant les exposants de ses composants
     *  et en modifiant son ref en conséquence.
     */
    public function reverseUnit()
    {
        $newComponents = [];
        // Pour chaque composant de l'unité on inverse son exposant.
        foreach ($this->components as $unitArray) {
            $unitArray['exponent'] *= -1;
            $newComponents[] = $unitArray;
        }
        // On affecte le nouveau tableau de composants.
        $this->components = $newComponents;
        // Permet de modifier la ref de l'unité inversé grace au nouveau tableau de composants.
        $this->setRef();
    }

}