<?php
/**
 * @author  simon.rieu
 * @author  bertrand.ferry
 * @author  maxime.fourt
 * @author  matthieu.napoli
 * @package Techno
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\PersistentCollection;

/**
 * Classe Family
 * @package Techno
 */
abstract class Techno_Model_Family extends Techno_Model_Component
{

    use Core_Strategy_Ordered;

    const QUERY_REF = 'ref';

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
     * @var Techno_Model_Category|null
     */
    protected $category;

    /**
     * Cellules de la famille
     * @var Collection|PersistentCollection|Techno_Model_Family_Cell[]
     */
    protected $cells;

    /**
     * Dimensions de la famille
     * @var Collection|Techno_Model_Family_Dimension[]
     */
    protected $dimensions;

    /**
     * Liste des tags communs
     * @var Collection
     */
    protected $cellsCommonTags;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->dimensions = new ArrayCollection();
        $this->cells = new ArrayCollection();
        $this->cellsCommonTags = new ArrayCollection();
    }

    /**
     * Retourne toutes les cellules de la famille
     * @return Techno_Model_Family_Cell[]
     */
    public function getCells()
    {
        return $this->cells->toArray();
    }

    /**
     * Retourne une cellule via ses membres
     * @param Techno_Model_Family_Member[] $members Coordonnées de la cellule
     * @throws Core_Exception_NotFound Cellule introuvable
     * @throws Core_Exception_InvalidArgument Nombre de membres incorrect
     * @return Techno_Model_Family_Cell
     */
    public function getCell($members)
    {
        // Vérifie le nombre de dimensions
        if (count($members) != count($this->getDimensions())) {
            throw new Core_Exception_InvalidArgument(
                "The number of members given doesn't match the number of dimensions in the family");
        }
        $criteria = Criteria::create();
        $hashKey = Techno_Model_Family_Cell::buildMembersHashKey($members);
        $criteria->where(Criteria::expr()->eq('membersHashKey', $hashKey));
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
     * Retourne un membre de la dimension en le recherchant par son mot-clé
     * @param Techno_Model_Meaning $meaning
     * @return Techno_Model_Family_Dimension
     * @throws Core_Exception_NotFound La dimension est introuvable
     */
    public function getDimensionByMeaning(Techno_Model_Meaning $meaning)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('meaning', $meaning));
        $results = $this->dimensions->matching($criteria);
        if (count($results) > 0) {
            return $results->first();
        }
        throw new Core_Exception_NotFound("La dimension $meaning est introuvable dans la famille {$this->ref}");
    }

    /**
     * Retourne la liste des dimensions de la famille
     * @return Collection|Techno_Model_Family_Dimension[]
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Ajoute une dimension à la famille
     * @param Techno_Model_Family_Dimension $dimension
     */
    public function addDimension(Techno_Model_Family_Dimension $dimension)
    {
        if (!$this->hasDimension($dimension)) {
            $this->dimensions->add($dimension);
            // Reconstruit les cellules
            $this->buildCells();
        }
    }

    /**
     * @param Techno_Model_Family_Dimension $dimension
     * @return boolean
     */
    public function hasDimension(Techno_Model_Family_Dimension $dimension)
    {
        return $this->dimensions->contains($dimension);
    }

    /**
     * Supprime une dimension de la famille
     * @param Techno_Model_Family_Dimension $dimension
     */
    public function removeDimension(Techno_Model_Family_Dimension $dimension)
    {
        if ($this->hasDimension($dimension)) {
            $this->dimensions->removeElement($dimension);
            // Reconstruit les cellules
            $this->buildCells();
        }
    }

    /**
     * Retourne la liste des tags communs des cellules de la famille
     * @return Collection|Techno_Model_Tag[]
     */
    public function getCellsCommonTags()
    {
        return $this->cellsCommonTags;
    }

    /**
     * Ajoute un élément à la liste des tags communs des cellules de la famille
     * @param Techno_Model_Tag $tag
     */
    public function addCellsCommonTag(Techno_Model_Tag $tag)
    {
        $this->cellsCommonTags->add($tag);
    }

    /**
     * Retourne true si le tags appartient à la liste des tags communs des cellules de la famille
     * @param Techno_Model_Tag $tag
     * @return boolean
     */
    public function hasCellsCommonTag(Techno_Model_Tag $tag)
    {
        return $this->cellsCommonTags->contains($tag);
    }

    /**
     * Supprime l'élément de la liste des tags communs des cellules de la famille
     * @param Techno_Model_Tag $tag
     */
    public function removeCellsCommonTag(Techno_Model_Tag $tag)
    {
        $this->cellsCommonTags->removeElement($tag);
    }

    /**
     * @return Techno_Model_Category|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Techno_Model_Category $category
     */
    public function setCategory(Techno_Model_Category $category)
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
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        if (count($this->dimensions) == 0) {
            return;
        }
        // Pour chaque "coordonnée" (ensemble de membre), on crée une cellule
        $this->browseDimensions(
            $this->dimensions,
            function ($members) {
                $this->cells->add(new Techno_Model_Family_Cell($this, $members));
            }
        );
    }

    /**
     * Crée les cellules découlant de l'ajout d'un membre
     *
     * @param Techno_Model_Family_Member $member
     */
    public function addCellsForNewMember(Techno_Model_Family_Member $member)
    {
        // Fixe la coordonées pour le membre donné
        $currentCoordinates = new ArrayCollection();
        $currentCoordinates->add($member);

        // Exclut la dimension du membre de la liste des dimensions à parcourir
        $filteredDimensions = $this->dimensions->filter(
            function (Techno_Model_Family_Dimension $dimension) use ($member) {
                return $dimension !== $member->getDimension();
            }
        );

        // Pour chaque "coordonnée" (ensemble de membre), on crée une cellule
        $this->browseDimensions(
            $filteredDimensions,
            function ($members) {
                $this->cells->add(new Techno_Model_Family_Cell($this, $members));
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
    private function browseDimensions(Collection $dimensionsToBrowse, Closure $callbackForEachCoordinates,
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
            $this->browseDimensions($newDimensionsToBrowse,
                                    $callbackForEachCoordinates,
                                    $newCurrentCoordinates);
        }

    }

    /**
     * @return bool True si des cellules de la famille ont des éléments choisis
     */
    public function hasChosenElements()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->gt('chosenElement', 0));
        $cellsWithChosenElement = $this->cells->matching($criteria);
        if ($cellsWithChosenElement->isEmpty()) {
            return false;
        }
        return true;
    }

    /**
     * Retourne une famille par son référent textuel
     * @param string $ref
     * @return Techno_Model_Family
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
     * Fonction appelé avant un persist de l'objet (défini dans le mapper).
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
     * Fonction appelé avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelé avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelé après un load de l'objet (défini dans le mapper).
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
