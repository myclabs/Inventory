<?php
/**
 * Classe Orga_Model_Granularity
 * @author valentin.claras
 * @author diana.dragusin
 * @package    Orga
 * @subpackage Model
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Objet métier Granularité : ensemble d'Axis formant des Cell pour chaque association de Member.
 * @package    Orga
 * @subpackage Model
 */
class Orga_Model_Granularity extends Core_Model_Entity
{
    use Core_Strategy_Ordered, Core_Event_ObservableTrait;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_POSITION = 'position';
    const QUERY_CUBE = 'cube';
    // Constantes d'événement.
    const EVENT_SAVE = 'orgaGranularitySave';
    const EVENT_DELETE = 'orgaGranularityDelete';


    /**
     * Identifiant unique de la Granularity.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Référence unique (au sein d'un cube) de la Granularity.
     *
     * @var string
     */
    protected  $ref = null;

    /**
     * Cube contenant la Granularity.
     *
     * @var Orga_Model_Cube
     */
    protected $cube = null;

    /**
     * Collection des Axis de la Granularity.
     *
     * @var Collection|Orga_Model_Axis[]
     */
    protected $axes = array();

    /**
     * Collection des Cell de la Granularity.
     *
     * @var Collection|Orga_Model_Cell[]
     */
    protected $cells = array();

    /**
     * Définit si la Granularity est navigable.
     *
     * @var Bool
     */
    protected $navigable = true;


    /**
     * Constructeur de la classe Granularity.
     */
    public function __construct()
    {
        $this->axes = new ArrayCollection();
        $this->cells = new ArrayCollection();

        $this->updateRef();
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     *
     * @return array
     */
    protected function getContext()
    {
        return array('cube' => $this->cube);
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
        $this->launchEvent(self::EVENT_SAVE);
        $this->generateCells();
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
        $this->launchEvent(self::EVENT_DELETE);
        $this->deletePosition();
        $this->getCube()->removeGranularity($this);
    }

    /**
     * Fonction appelé après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }

    /**
     * Charge une Granularity en fonction de sa référence et de son Cube.
     *
     * @param string $ref
     * @param Orga_Model_Cube $cube
     *
     * @return Orga_Model_Granularity
     */
    public static function loadByRefAndCube($ref, $cube)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref, 'cube' => $cube));
    }

    /**
     * Définit le Cube de la Granularity.
     *
     * @param Orga_Model_Cube $cube
     */
    public function setCube(Orga_Model_Cube $cube=null)
    {
        if ($this->cube !== $cube) {
            if ($this->cube !== null) {
                throw new Core_Exception_TooMany('Cube already set, a granularity cannot be move.');
            }
            $this->cube = $cube;
            $cube->addGranularity($this);
        }
    }

    /**
     * Renvoie le Cube de la Granularity.
     *
     * @return Orga_Model_Cube
     */
    public function getCube()
    {
        return $this->cube;
    }

    /**
     * Ajoute un Axis à la Granularity.
     *
     * @param Orga_Model_Axis $axis
     */
    public function addAxis(Orga_Model_Axis $axis)
    {
        if (!($this->hasAxis($axis))) {
            $this->axes->add($axis);
            $axis->addGranularity($this);
            $this->updateRef();
            $this->getCube()->orderGranularities();
            if ($this->getKey() !== array()) {
                $this->generateCells();
            }
        }
    }

    /**
     * Vérifie si la Granularity possède l'Axis donné.
     *
     * @param Orga_Model_Axis $axis
     *
     * @return boolean
     */
    public function hasAxis(Orga_Model_Axis $axis)
    {
        return $this->axes->contains($axis);
    }

    /**
     * Retire un Axis de ceux utilisés par la Granularity.
     *
     * @param Orga_Model_Axis $axis
     */
    public function removeAxis($axis)
    {
        if ($this->hasAxis($axis)) {
            $this->axes->removeElement($axis);
            $axis->removeGranularity($this);
            $this->updateRef();
            $this->getCube()->orderGranularities();
            if ($this->getKey() !== array()) {
                $this->generateCells();
            }
        }
    }

    /**
     * Vérifie si la Granularity possède au moins un Axis.
     *
     * @return bool
     */
    public function hasAxes()
    {
        return !$this->axes->isEmpty();
    }

    /**
     * Renvoie un tableau contenant tous les Axis de la Granularity.
     *
     * @return Orga_Model_Axis[]
     */
    public function getAxes()
    {
        return $this->axes->toArray();
    }

    /**
     * Définit la navigabilité de la Granularity.
     *
     * @param boolean $navigable
     */
    public function setNavigability($navigable)
    {
        $this->navigable = $navigable;
    }

    /**
     * Indique si la Granularity es navigable.
     *
     * @return boolean
     */
    public function isNavigable()
    {
        return $this->navigable;
    }

    /**
     * Ajoute une Cell à la Granularity.
     *
     * @param Orga_Model_Cell $cell
     */
    public function addCell(Orga_Model_Cell $cell)
    {
        if (!($this->hasCell($cell))) {
            $this->cells->add($cell);
            $cell->setGranularity($this);
        }
    }

    /**
     * Vérifie si la Granularity possède la Cell donnée.
     *
     * @param Orga_Model_Cell $cell
     *
     * @return boolean
     */
    public function hasCell(Orga_Model_Cell $cell)
    {
        return $this->cells->contains($cell);
    }

    /**
     * Retire la Cell donnée des Cell de la Granularity.
     *
     * @param Orga_Model_Cell $cell
     */
    public function removeCell(Orga_Model_Cell $cell)
    {
        if ($this->hasCell($cell)) {
            $this->cells->removeElement($cell);
            $cell->delete();
        }
    }

    /**
     * Vérifie que la Granularity possède au moins une Cell.
     *
     * @return bool
     */
    public function hasCells()
    {
        return !$this->cells->isEmpty();
    }

    /**
     * Renvoie un tableau des Cell de la Granularity.
     *
     * @return Orga_Model_Cell[]
     */
    public function getCells()
    {
        return $this->cells->toArray();
    }

    /**
     * Renvoie la cellule correspondant aux membres données.
     *
     * @param Orga_Model_Member[] $listMembers
     *
     * @return Orga_Model_Cell
     */
    public function getCellByMembers($listMembers)
    {
        $criteria = \Doctrine\Common\Collections\Criteria::create();
        $membersHashKey = Orga_Model_Cell::buildMembersHashKey($listMembers);
        $criteria->where($criteria->expr()->eq('membersHashKey', $membersHashKey));
        $matchingCells = $this->cells->matching($criteria);

        if ($matchingCells->isEmpty()) {
            throw new Core_Exception_NotFound('No "Orga_Model_Cell" matching attributes '.$membersHashKey);
        } else if (count($matchingCells) > 1) {
            throw new Core_Exception_TooMany('Too many "Orga_Model_Cell" matching attributes '.$membersHashKey);
        }

        return $matchingCells->first();
    }

    /**
     * Permet de mettre à jour la ref Granularity.
     */
    public function updateRef()
    {
        $refParts = array();
        foreach ($this->getAxes() as $axis) {
            $refParts[] = $axis->getRef();
        }
        $this->ref = $this->getRefFromAxesRef($refParts);
    }

    /**
     * Renvoi la ref d'une granularité à partir d'un ensemble de ref d'axes.
     *
     * @param array(string) $axesRef
     *
     * @return string
     */
    protected function getRefFromAxesRef($axesRef)
    {
        if (empty($axesRef)) {
            return 'global';
        } else {
            return implode('|', $axesRef);
        }
    }

    /**
     * Renvoie la ref de la Granularity, dépendant des ref de ces Axis.
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Renvoie le label de la Granularity, dépendant des label de ces Axis.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->axes->isEmpty()) {
            $label = __('Orga', 'granularity', 'labelGlobalGranularity');
        } else {
            $labelParts = array();
            foreach ($this->getAxes() as $axis) {
                $labelParts[] = $axis->getLabel();
            }
            $label = implode(' | ', $labelParts);
        }
        return $label;
    }

    /**
     * Indique si la Granularity courante est narrower (ou égale) de la Granularity donnée.
     *
     * @todo Créer isNarrowerOrEqual() et faire que cette méthode soit une comparaison stricte
     *
     * @param Orga_Model_Granularity $broaderGranularity
     *
     * @return boolean
     */
    public function isNarrowerThan($broaderGranularity)
    {
        foreach ($broaderGranularity->getAxes() as $broaderAxis) {
            foreach ($this->getAxes() as $axis) {
                if (($axis === $broaderAxis) || ($axis->isNarrowerThan($broaderAxis))) {
                    continue 2;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Indique si la Granularity courante est broader de la Granularity donnée.
     * Check if a given Granularity is broader than the current Granularity.
     *
     * @param Orga_Model_Granularity $narrowerGranularity
     *
     * @return boolean
     */
    public function isBroaderThan($narrowerGranularity)
    {
        return $narrowerGranularity->isNarrowerThan($this);
    }

    /**
     * Renvois ls Granularity narrower à la Granularity courante.
     *
     * @return Orga_Model_Granularity[]
     */
    public function getNarrowerGranularities()
    {
        $narrowerGranularities = array();

        foreach ($this->getCube()->getGranularities() as $granularity) {
            if (($granularity !== $this) && ($granularity->isNarrowerThan($this))) {
                $narrowerGranularities[] = $granularity;
            }
        }

        return $narrowerGranularities;
    }

    /**
     * Renvois ls Granularity broader à la Granularity courante.
     *
     * @return Orga_Model_Granularity[]
     */
    public function getBroaderGranularities()
    {
        $broaderGranularities = array();

        foreach ($this->getCube()->getGranularities() as $granularity) {
            if (($granularity !== $this) && ($this->isNarrowerThan($granularity))) {
                $broaderGranularities[] = $granularity;
            }
        }

        return array_reverse($broaderGranularities);
    }

    /**
     * Renvoi la plus grosse des granularités plus fines communes aux granularités courante et donnée.
     *
     * @param Orga_Model_Granularity $crossingGranularity
     *
     * @return Orga_Model_Granularity
     */
    public function getCrossedGranularity($crossingGranularity)
    {
        $currentAxes = $this->getAxes();
        $crossingAxes = $crossingGranularity->getAxes();

        foreach ($this->getAxes() as $currentIndex => $currentAxis) {
            foreach ($crossingGranularity->getAxes() as $crossingIndex => $crossingAxis) {
                if (($currentAxis->isNarrowerThan($crossingAxis)) || ($currentAxis === $crossingAxis)) {
                    unset($crossingAxes[$crossingIndex]);
                } else if ($currentAxis->isBroaderThan($crossingAxis)) {
                    unset($currentAxes[$currentIndex]);
                }
            }
        }

        $crossedAxesRefs = array_merge($currentAxes, $crossingAxes);
        @uasort($crossedAxesRefs, function ($a, $b) {
            return $a->getGlobalPosition() - $b->getGlobalPosition();
        });

        return Orga_Model_Granularity::loadByRefAndCube($this->getRefFromAxesRef($crossedAxesRefs), $this->cube);
    }

    /**
     * Renvoi la plus fine des granularités plus grosses communes aux granularités courante et donnée.
     *
     * @param Orga_Model_Granularity $crossingGranularity
     *
     * @return Orga_Model_Granularity
     */
    public function getEncompassingGranularity($crossingGranularity)
    {
        $encompassingAxes = array();

        foreach ($this->getAxes() as $currentIndex => $currentAxis) {
            if (!($currentAxis->isTransverse($crossingGranularity->getAxes()))) {
                foreach ($crossingGranularity->getAxes() as $crossingIndex => $crossingAxis) {
                    if ($currentAxis->isNarrowerThan($crossingAxis)) {
                        $encompassingAxes[$currentIndex] = $crossingAxis;
                    } else {
                        $encompassingAxes[$currentIndex] = $currentAxis;
                    }
                }
            }
        }

        @uasort($encompassingAxes, function ($a, $b) {
            return $a->getGlobalPosition() - $b->getGlobalPosition();
        });

        return Orga_Model_Granularity::loadByRefAndCube($this->getRefFromAxesRef($encompassingAxes), $this->cube);
    }

    /**
     * Génère les Cell de la Granularity en fonction des Member de ces Axis.
     */
    public function generateCells()
    {
        foreach ($this->cells as $cell) {
            $this->cells->removeElement($cell);
            foreach ($cell->getMembers() as $member) {
                $member->removeCell($cell);
            }
            $cell->delete();
        }
        $this->traverseAxesThenCreateCells();
    }

    /**
     * Crée les Cells correspondantes à un Member ajouté à un Axis..
     *
     * @param Orga_Model_Member $member
     */
    public function generateCellsFromNewMember(Orga_Model_Member $member)
    {
        $this->traverseAxesThenCreateCells(0, array($member), $member->getAxis());
    }

    /**
     * Parcours les Axis et crée les Cell..
     *
     * @param int $indexCurrentAxis
     * @param array $selectedMembers
     * @param Orga_Model_Axis $ignoredAxis
     */
    protected function traverseAxesThenCreateCells($indexCurrentAxis = 0, array $selectedMembers = array(),
                                                   $ignoredAxis = null
    ) {
        if ($indexCurrentAxis >= count($this->axes)) {
            $this->createCell($selectedMembers);
        } elseif ($this->axes[$indexCurrentAxis] === $ignoredAxis) {
            $this->traverseAxesThenCreateCells($indexCurrentAxis + 1, $selectedMembers, $ignoredAxis);
        } else {
            foreach ($this->axes[$indexCurrentAxis]->getMembers() as $currentAxisMember) {
                $nextSelectedMembers = array_merge($selectedMembers, array($currentAxisMember));
                $this->traverseAxesThenCreateCells($indexCurrentAxis + 1, $nextSelectedMembers, $ignoredAxis);
            }
        }
    }

    /**
     * Créer un Cell en fonction d'un tableau de Member.
     *
     * @param Orga_Model_Member[] $members
     */
    protected function createCell(array $members)
    {
        $cell = new Orga_Model_Cell();
        $cell->setGranularity($this);
        foreach ($members as $cellMember) {
            $cell->addMember($cellMember);
        }
    }

}
