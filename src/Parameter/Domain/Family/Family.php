<?php

namespace Parameter\Domain\Family;

use Closure;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Core_Model_Query;
use Core_Strategy_Ordered;
use Core_Tools;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Parameter\Domain\Category;
use Unit\UnitAPI;

/**
 * Famille de paramètres.
 *
 * @author simon.rieu
 * @author bertrand.ferry
 * @author maxime.fourt
 * @author ronan.gorain
 * @author matthieu.napoli
 */
class Family extends Core_Model_Entity
{
    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    const QUERY_REF = 'ref';

    /**
     * @var int
     */
    protected $id;

    /**
     * Identifiant textuel
     * @var string
     */
    protected $ref;

    /**
     * Nom de la famille
     * @var string
     */
    protected $label;

    /**
     * @var Category|null
     */
    protected $category;

    /**
     * Cellules de la famille
     * @var Collection|Cell[]
     */
    protected $cells;

    /**
     * Dimensions de la famille
     * @var Collection|Dimension[]
     */
    protected $dimensions;

    /**
     * Référence de l'unité du composant
     * @var string
     */
    protected $refUnit;

    /**
     * Cache
     * @var UnitAPI
     */
    protected $unit;

    /**
     * Documentation
     * @var string
     */
    protected $documentation;

    public function __construct($ref, $label)
    {
        $this->setRef($ref);
        $this->label = $label;

        $this->dimensions = new ArrayCollection();
        $this->cells = new ArrayCollection();
    }

    /**
     * Retourne toutes les cellules de la famille
     * @return Cell[]
     */
    public function getCells()
    {
        return $this->cells->toArray();
    }

    /**
     * Retourne une cellule via ses membres
     * @param Member[] $members Coordonnées de la cellule
     * @throws Core_Exception_NotFound Cellule introuvable
     * @throws Core_Exception_InvalidArgument Nombre de membres incorrect
     * @return Cell
     */
    public function getCell($members)
    {
        // Vérifie le nombre de dimensions
        if (count($members) != count($this->getDimensions())) {
            throw new Core_Exception_InvalidArgument(
                "The number of members given doesn't match the number of dimensions in the family"
            );
        }
        $criteria = Criteria::create();
        $hashKey = Cell::buildMembersHashKey($members);
        $criteria->where(Criteria::expr()->eq('membersHashKey', $hashKey));
        /** @var Collection $matchingCells */
        $matchingCells = $this->cells->matching($criteria);
        if (count($matchingCells) >= 1) {
            return $matchingCells->first();
        }
        throw new Core_Exception_NotFound("No cell was found matching members '$hashKey'");
    }

    /**
     * @param string $ref Identifiant textuel
     */
    public function setRef($ref)
    {
        Core_Tools::checkRef($ref);
        $this->ref = $ref;
    }

    /**
     * @return string Identifiant textuel
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $label Nom de la famille
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string Nom de la famille
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Retourne une dimension en la recherchant par son mot-clé
     * @param string $dimensionRef
     * @return Dimension
     * @throws Core_Exception_NotFound La dimension est introuvable
     */
    public function getDimension($dimensionRef)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('ref', $dimensionRef));
        /** @var Collection $results */
        $results = $this->dimensions->matching($criteria);
        if (count($results) > 0) {
            return $results->first();
        }
        throw new Core_Exception_NotFound("La dimension $dimensionRef est introuvable dans la famille {$this->ref}");
    }

    /**
     * Retourne la liste des dimensions de la famille
     * @return Collection|Dimension[]
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Ajoute une dimension à la famille
     * @param Dimension $dimension
     */
    public function addDimension(Dimension $dimension)
    {
        if (!$this->hasDimension($dimension)) {
            $this->dimensions->add($dimension);
            // Reconstruit les cellules
            $this->buildCells();
        }
    }

    /**
     * @param Dimension $dimension
     * @return boolean
     */
    public function hasDimension(Dimension $dimension)
    {
        return $this->dimensions->contains($dimension);
    }

    /**
     * Supprime une dimension de la famille
     * @param Dimension $dimension
     */
    public function removeDimension(Dimension $dimension)
    {
        if ($this->hasDimension($dimension)) {
            $this->dimensions->removeElement($dimension);
            // Reconstruit les cellules
            $this->buildCells();
        }
    }

    /**
     * @return Category|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        if ($this->category !== $category) {
            $this->deletePosition();
            if ($this->category) {
                $this->category->removeFamily($this);
            }

            $this->category = $category;

            $category->addFamily($this);
            $this->setPosition();
        }
    }

    /**
     * Reconstruit les cellules de la famille (supprime et recrée)
     *
     * À appeler lorsque les dimensions de la famille ont changées
     */
    public function buildCells()
    {
        foreach ($this->cells as $cell) {
            $this->cells->removeElement($cell);
            $cell->delete();
        }
        // Obligé pour l'instant de faire comme ça
        \Core\ContainerSingleton::getEntityManager()->flush();
        if (count($this->dimensions) == 0) {
            return;
        }
        // Pour chaque "coordonnée" (ensemble de membre), on crée une cellule
        $this->browseDimensions(
            $this->dimensions,
            function ($members) {
                $this->cells->add(new Cell($this, $members));
            }
        );
    }

    /**
     * Crée les cellules découlant de l'ajout d'un membre
     *
     * @param Member $member
     */
    public function addCellsForNewMember(Member $member)
    {
        // Fixe la coordonées pour le membre donné
        $currentCoordinates = new ArrayCollection();
        $currentCoordinates->add($member);

        // Exclut la dimension du membre de la liste des dimensions à parcourir
        $filteredDimensions = $this->dimensions->filter(
            function (Dimension $dimension) use ($member) {
                return $dimension !== $member->getDimension();
            }
        );

        // Pour chaque "coordonnée" (ensemble de membre), on crée une cellule
        $this->browseDimensions(
            $filteredDimensions,
            function ($members) {
                $this->cells->add(new Cell($this, $members));
            },
            $currentCoordinates
        );
    }

    /**
     * Parcourt les dimensions données et exécute la callback pour chaque combinaison
     * de membres possible (== coordonnées)
     *
     * @param Collection $dimensionsToBrowse
     * @param closure    $callbackForEachCoordinates Callback prenant en paramètre
     *                                               une liste de membres
     * @param Collection $currentCoordinates
     */
    private function browseDimensions(
        Collection $dimensionsToBrowse,
        Closure $callbackForEachCoordinates,
        Collection $currentCoordinates = null
    ) {
        if ($currentCoordinates === null) {
            $currentCoordinates = new ArrayCollection();
        }

        // Condition d'arrêt de la récursivité : on est à une coordonnée définie
        if (count($dimensionsToBrowse) == 0) {
            // Appelle la callback
            $callbackForEachCoordinates($currentCoordinates);
            return;
        }

        // On est pas encore à une coordonnées définie, il faut continuer à parcourir les dimensions
        $currentDimension = $dimensionsToBrowse->first();

        // Enlève celle en train d'être parcourue
        $newDimensionsToBrowse = clone $dimensionsToBrowse;
        $newDimensionsToBrowse->removeElement($currentDimension);

        foreach ($currentDimension->getMembers() as $currentMember) {
            // Crée une nouvelle liste des membres en ajoutant celui sur lequel on est
            $newCurrentCoordinates = clone $currentCoordinates;
            $newCurrentCoordinates->add($currentMember);
            // Appel récursif
            $this->browseDimensions(
                $newDimensionsToBrowse,
                $callbackForEachCoordinates,
                $newCurrentCoordinates
            );
        }

    }

    /**
     * Retourne une famille par son référent textuel
     * @param string $ref
     * @throws \Core_Exception_NotFound
     * @return Family
     */
    public static function loadByRef($ref)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_REF, $ref);
        $list = self::loadList($query);
        if (count($list) == 0) {
            throw new Core_Exception_NotFound("No family was found with the ref '$ref'");
        }
        return current($list);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param UnitAPI $unit
     * @throws Core_Exception_UndefinedAttribute
     * @throws Core_Exception_InvalidArgument
     */
    public function setUnit(UnitAPI $unit)
    {
        $this->refUnit = $unit->getRef();
        $this->unit = $unit;
    }

    /**
     * @return UnitAPI
     * @throws Core_Exception_UndefinedAttribute
     */
    public function getUnit()
    {
        if ($this->refUnit === null) {
            throw new Core_Exception_UndefinedAttribute("The component unit has not been defined");
        }
        // Lazy loading
        if ($this->unit === null) {
            $this->unit = new UnitAPI($this->refUnit);
        }
        return $this->unit;
    }

    /**
     * Retourne l'unité de la valeur de l'élément (!= unité de l'élément)
     * @return UnitAPI
     * @throws Core_Exception_UndefinedAttribute
     */
    public function getValueUnit()
    {
        return $this->getUnit();
    }

    /**
     * @param string $documentation
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
    }

    /**
     * @return string
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Fonction appelée avant un persist de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        try {
            $this->checkHasPosition();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->setPosition();
        }
    }

    /**
     * Fonction appelée avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelée après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }

    /**
     * Renvoie les valeurs du contexte pour la position
     * @return array
     */
    protected function getContext()
    {
        return [
            'category' => $this->category,
        ];
    }
}
