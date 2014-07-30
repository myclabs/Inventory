<?php

namespace Orga\Domain;

use Core\Translation\TranslatedString;
use Core_Exception;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Exception_TooMany;
use Core_Exception_UndefinedAttribute;
use Core_Exception_User;
use Core_Model_Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Selectable;
use DW\Domain\Cube;
use Orga\Domain\Member;
use Orga\Domain\Service\OrgaDomainHelper;
use Orga\Domain\Workspace;
use Orga\Domain\Axis;
use Orga\Domain\Cell;
use Orga\Domain\SubCellsGroup;
use Orga\Domain\Service\ETL\ETLStructureService;

/**
 * Granularity
 *
 * @author valentin.claras
 */
class Granularity extends Core_Model_Entity
{
    // Constantes de tris et de filtres.
    const QUERY_TAG = 'tag';
    const QUERY_REF = 'ref';
    const QUERY_POSITION = 'position';
    const QUERY_WORKSPACE = 'workspace';

    // Séparateur des refs et labels des axes dans le label de la granularité.
    const  REF_SEPARATOR = '|';
    const  LABEL_SEPARATOR = ' | ';


    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $ref = null;

    /**
     * @var Workspace
     */
    protected $workspace = null;

    /**
     * @var Collection|Axis[]
     */
    protected $axes = array();

    /**
     * @var string
     */
    protected $tag = null;

    /**
     * @var int
     */
    protected $position = null;

    /**
     * @var Collection|Cell[]
     */
    protected $cells = array();

    /**
     * @var bool
     */
    protected $cellsControlRelevance = false;

    /**
     * @var bool
     */
    protected $cellsMonitorInventory = false;

    /**
     * @var Granularity
     */
    protected $inputConfigGranularity = null;

    /**
     * @var Collection|Granularity[]
     */
    protected $inputGranularities = null;

    /**
     * @var bool
     */
    protected $cellsGenerateDWCubes = false;

    /**
     * @var Cube
     */
    protected $dWCube = null;

    /**
     * @var bool
     */
    protected $cellsWithACL = false;

    /**
     * @var bool
     */
    protected $cellsWithInputDocs = false;


    /**
     * @param Workspace $workspace
     * @param Axis[] $axes
     * @throws Core_Exception_InvalidArgument
     */
    public function __construct(Workspace $workspace, array $axes = [])
    {
        $this->axes = new ArrayCollection();
        $this->cells = new ArrayCollection();
        $this->inputGranularities = new ArrayCollection();

        $this->workspace = $workspace;
        @usort($axes, ['Orga\Domain\Axis', 'firstOrderAxes']);
        foreach ($axes as $axis) {
            if ($workspace->hasAxis($axis) && !($this->hasAxis($axis))) {
                if (!$axis->isTransverse($this->axes->toArray())) {
                    throw new Core_Exception_InvalidArgument(
                        'Each given Axis must be transverse with each other axes'
                    );
                }
                $this->axes->add($axis);
                $axis->addGranularity($this);
            }
        }

        // Mise à jour de la ref et du tag.
        $this->updateRef();
        $this->updateTag();

        // Création des cellules.
        $this->traverseAxesThenCreateCells();

        // Ajout effectif dans le workspace.
        $workspace->addGranularity($this);
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        if (($this->getWorkspace() !== null) && ($this->getWorkspace()->hasGranularity($this))) {
            $this->removeFromWorkspace();
        }
    }

    /**
     * @param Cube $dWCube
     * @return Granularity
     */
    public static function loadByDWCube(Cube $dWCube)
    {
        return self::getEntityRepository()->loadBy(array('dWCube' => $dWCube));
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function removeFromWorkspace()
    {
        if ($this->workspace !== null) {
            $this->getWorkspace()->removeGranularity($this);

            $axes = $this->getAxes();
            $cells = $this->getCells()->toArray();

            // Détachement de la granularité du Workspace.
            $this->workspace = null;

            // Mise à jour des associations des axes.
            foreach ($axes as $axis) {
                $axis->removeGranularity($this);
            }

            // Mise à jour des associations des membres.
            foreach ($cells as $cell) {
                foreach ($cell->getMembers() as $cellMember) {
                    $cellMember->removeCell($cell);
                }
            }
        }
    }

    /**
     * @param Axis $axis
     * @return boolean
     */
    public function hasAxis(Axis $axis)
    {
        return $this->axes->contains($axis);
    }

    /**
     * @return bool
     */
    public function hasAxes()
    {
        return !$this->axes->isEmpty();
    }

    /**
     * @return Axis[]
     */
    public function getAxes()
    {
        return $this->axes->toArray();
    }

    public function updateRef()
    {
        $this->ref = self::buildRefFromAxes($this->getAxes());
    }

    /**
     * @param Axis[] $axes
     * @return string
     */
    public static function buildRefFromAxes(array $axes)
    {
        $axesRefParts = array();
        @usort($axes, ['Orga\Domain\Axis', 'firstOrderAxes']);
        foreach ($axes as $axis) {
            $axesRefParts[] = $axis->getRef();
        }

        if (empty($axesRefParts)) {
            return 'global';
        } else {
            return implode(self::REF_SEPARATOR, $axesRefParts);
        }
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @return TranslatedString
     */
    public function getLabel()
    {
        if (!$this->hasAxes()) {
            return TranslatedString::untranslated(__('Orga', 'granularity', 'labelGlobalGranularity'));
        }

        $labelParts = [];
        foreach ($this->getAxes() as $axis) {
            $labelParts[] = $axis->getLabel();
        }
        return TranslatedString::implode(self::LABEL_SEPARATOR, $labelParts);
    }

    public function updateTag()
    {
        if (!$this->hasAxes()) {
            $this->tag = Workspace::PATH_SEPARATOR;
        } else {
            $axesTagParts = array();
            $axes = $this->getAxes();
            @usort($axes, ['Orga\Domain\Axis', 'firstOrderAxes']);
            foreach ($axes as $axis) {
                $axesTagParts[] = $axis->getBroaderTag();
            }
            $this->tag = implode(Workspace::PATH_JOIN, $axesTagParts);
        }
    }

    public function updateCellsHierarchy()
    {
        foreach ($this->getCells() as $cell) {
            $cell->updateHierarchy();
        }
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param Granularity $a
     * @param Granularity $b
     * @return int 1, 0 ou -1
     */
    public static function orderGranularities(Granularity $a, Granularity $b)
    {
        return $a->getPosition() - $b->getPosition();
    }

    /**
     * @param Member $member
     */
    public function generateCellsFromNewMember(Member $member)
    {
        $this->traverseAxesThenCreateCells(0, [$member], $member->getAxis());
    }

    /**
     * @param int $indexCurrentAxis
     * @param array $selectedMembers
     * @param Axis $ignoredAxis
     */
    protected function traverseAxesThenCreateCells(
        $indexCurrentAxis = 0,
        array $selectedMembers = [],
        Axis $ignoredAxis = null
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
     * @param Member[] $members
     */
    protected function createCell(array $members)
    {
        $cell = new Cell($this, $members);
        $this->cells->add($cell);

        // Affectation de la même pretinence que selon la cellule précédente (suivant l'axe temps).
        if ($this->getCellsControlRelevance()) {
            $timeAxis = $this->getWorkspace()->getTimeAxis();
            if (($timeAxis !== null) && ($this->hasAxis($timeAxis))) {
                $previousCell = $cell->getPreviousCellForAxis($timeAxis);
                if ($previousCell !== null) {
                    $cell->setRelevant($previousCell->getRelevant());
                }
            }
        }
    }

    /**
     * @param Cell $cell
     */
    protected function removeCell(Cell $cell)
    {
        $this->cells->removeElement($cell);
    }

    /**
     * @param Member $member
     */
    public function removeCellsFromMember(Member $member)
    {
        foreach ($this->getCellsByMembers([$member]) as $cell) {
            $this->cells->removeElement($cell);
            $cell->removeFromMember();
        }
    }

    /**
     * @return Collection|Selectable|Cell[]
     */
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * @return Collection|Cell[]
     */
    public function getOrderedCells()
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['tag' => 'ASC']);
        return $this->cells->matching($criteria);
    }

    /**
     * @param Member[] $listMembers
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_InvalidArgument
     * @throws Core_Exception_TooMany
     * @return Cell
     */
    public function getCellByMembers(array $listMembers)
    {
        $matchingCells = $this->getCellsByMembers($listMembers)->toArray();

        if (empty($matchingCells)) {
            @usort($listMembers, [Member::class, 'orderMembers']);
            $membersRef = [];
            foreach ($listMembers as $member) {
                $membersRef[] = $member->getRef();
            }
            $membersHashKey = implode(self::REF_SEPARATOR, $membersRef);
            throw new Core_Exception_NotFound(sprintf(
                'No Cell matching members "%s" for "%s".',
                $membersHashKey,
                $this->getRef()
            ));
        } elseif (count($matchingCells) > 1) {
            @usort($listMembers, [Member::class, 'orderMembers']);
            $membersRef = [];
            foreach ($listMembers as $member) {
                $membersRef[] = $member->getRef();
            }
            $membersHashKey = implode(self::REF_SEPARATOR, $membersRef);
            throw new Core_Exception_TooMany(sprintf(
                'Too many Cell matching members "%s" for "%s".',
                $membersHashKey,
                $this->getRef()
            ));
        }

        return array_pop($matchingCells);
    }

    /**
     * @param Member[] $listMembers
     * @return Collection|Cell[]
     */
    public function getCellsByMembers($listMembers)
    {
        $criteria = Criteria::create();

        $axesPath = [];
        foreach ($listMembers as $member) {
            $axesPath[$member->getAxis()->getRef()][] = $member->getTag();
        }

        foreach ($axesPath as $axisPath) {
            if (count($axisPath) > 1) {
                $orExpressions = [];
                foreach ($axisPath as $memberPath) {
                    $orExpressions[] = $criteria->expr()->contains('tag', $memberPath);
                }
                $criteria->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $orExpressions));
            } elseif (count($axisPath) > 0) {
                $criteria->andWhere($criteria->expr()->contains('tag', array_pop($axisPath)));
            }
        }

        $criteria->orderBy(['tag' => 'ASC']);

        return $this->cells->matching($criteria);
    }

    /**
     * @param Granularity $broaderGranularity
     * @return boolean
     */
    public function isNarrowerThan(Granularity $broaderGranularity)
    {
        if ($broaderGranularity === $this) {
            return false;
        }

        foreach (explode(Workspace::PATH_JOIN, $broaderGranularity->getTag()) as $pathTag) {
            if (strpos($this->tag, $pathTag) !== false) {
                continue;
            }
            return false;
        }

        return true;
    }

    /**
     * @param Granularity $narrowerGranularity
     * @return boolean
     */
    public function isBroaderThan(Granularity $narrowerGranularity)
    {
        return $narrowerGranularity->isNarrowerThan($this);
    }

    /**
     * @return Granularity[]
     */
    public function getNarrowerGranularities()
    {
        $criteria = Criteria::create();
        foreach (explode(Workspace::PATH_JOIN, $this->getTag()) as $pathTag) {
            $criteria->andWhere($criteria->expr()->contains('tag', $pathTag));
        }
        $criteria->andWhere($criteria->expr()->neq('tag', $this->getTag()));
        $criteria->orderBy(['position' => 'ASC']);
        return $this->getWorkspace()->getGranularities()->matching($criteria)->toArray();
    }

    /**
     * @return Granularity[]
     */
    public function getBroaderGranularities()
    {
        $broaderGranularities = [];
        foreach ($this->getWorkspace()->getOrderedGranularities() as $granularity) {
            if ($this->isNarrowerThan($granularity)) {
                $broaderGranularities[] = $granularity;
            }
        }
        return $broaderGranularities;
    }

    /**
     * @param bool $cellsMonitorInventory
     * @throws Core_Exception_User
     */
    public function setCellsMonitorInventory($cellsMonitorInventory)
    {
        if ($this->cellsMonitorInventory !== $cellsMonitorInventory) {
            if ($cellsMonitorInventory) {
                // Vérification qu'il y ait bien une granularité de gestion des statut des inventaire.
                $granularityForInventoryStatus = $this->getWorkspace()->getGranularityForInventoryStatus();
                if ($granularityForInventoryStatus === null) {
                    throw new Core_Exception_User('Orga', 'inventory', 'monitoringInventoryWithoutGranularity');
                }
                // Vérification que cette granularité est plus fine que la granularité de gestion des inventaires.
                if (!$this->isNarrowerThan($granularityForInventoryStatus)) {
                    throw new Core_Exception_User('Orga', 'inventory', 'monitoringNotNarrowerThanGranularity');
                }
            }

            $this->cellsMonitorInventory = $cellsMonitorInventory;
        }
    }

    /**
     * @return bool
     */
    public function getCellsMonitorInventory()
    {
        return $this->cellsMonitorInventory;
    }

    /**
     * @param bool $cellsControlRelevance
     */
    public function setCellsControlRelevance($cellsControlRelevance)
    {
        if ($this->cellsControlRelevance !== $cellsControlRelevance) {
            // Si le granularité ne contrôle plus la pertinence, les cellules sont réinitialisées.
            if (!$cellsControlRelevance) {
                foreach ($this->getCells() as $cell) {
                    $cell->setRelevant(true);
                }
            }

            $this->cellsControlRelevance = $cellsControlRelevance;
        }
    }

    /**
     * @return bool
     */
    public function getCellsControlRelevance()
    {
        return $this->cellsControlRelevance;
    }

    /**
     * @param Granularity|null $configGranularity
     * @throws Core_Exception_InvalidArgument
     */
    public function setInputConfigGranularity(Granularity $configGranularity = null)
    {
        if ($this->inputConfigGranularity !== $configGranularity) {
            if (($configGranularity !== null)
                && ($this !== $configGranularity)
                && (!$this->isNarrowerThan($configGranularity))
            ) {
                throw new Core_Exception_InvalidArgument(
                    'The config Granularity needs to be broader than this Granularity.'
                );
            }

            if ($this->inputConfigGranularity !== null) {
                $this->inputConfigGranularity->removeInputGranularity($this);
            }

            $this->inputConfigGranularity = $configGranularity;

            if ($configGranularity !== null) {
                $configGranularity->addInputGranularity($this);
                foreach ($this->getCells() as $cell) {
                    $cell->enableDocLibraryForAFInputSetPrimary();
                    $cell->updateInputStatus();
                }
            } else {
                foreach ($this->getCells() as $cell) {
                    $cell->disableDocLibraryForAFInputSetPrimary();
                    $cell->setAFInputSetPrimary();
                    $cell->updateInputStatus();
                }
            }
        }
    }

    /**
     * @throws Core_Exception_UndefinedAttribute
     * @return Granularity
     */
    public function getInputConfigGranularity()
    {
        return $this->inputConfigGranularity;
    }

    /**
     * @return bool
     */
    public function isInput()
    {
        return $this->inputConfigGranularity !== null;
    }

    /**
     * @param Granularity $inputGranularity
     */
    public function addInputGranularity(Granularity $inputGranularity)
    {
        if (!($this->hasInputGranularity($inputGranularity))) {
            $this->inputGranularities->add($inputGranularity);
            $inputGranularity->setInputConfigGranularity($this);

            foreach ($this->getCells() as $cell) {
                new SubCellsGroup($cell, $inputGranularity);
            }
        }
    }

    /**
     * @param Granularity $inputGranularity
     * @return boolean
     */
    public function hasInputGranularity(Granularity $inputGranularity)
    {
        return $this->inputGranularities->contains($inputGranularity);
    }

    /**
     * @param Granularity $inputGranularity
     */
    public function removeInputGranularity(Granularity $inputGranularity)
    {
        if ($this->hasInputGranularity($inputGranularity)) {
            $this->inputGranularities->removeElement($inputGranularity);

            foreach ($this->getCells() as $cell) {
                $subCellsGroup = $cell->getSubCellsGroupForInputGranularity($inputGranularity);
                $cell->removeSubCellsGroup($subCellsGroup);
            }
        }
    }

    /**
     * @return bool
     */
    public function hasInputGranularities()
    {
        return !$this->inputGranularities->isEmpty();
    }

    /**
     * @return Granularity[]
     */
    public function getInputGranularities()
    {
        return $this->inputGranularities->toArray();
    }

    /**
     * @param bool $cellsGenerateDWCubes
     */
    public function setCellsGenerateDWCubes($cellsGenerateDWCubes)
    {
        if ($this->cellsGenerateDWCubes !== $cellsGenerateDWCubes) {
            $this->cellsGenerateDWCubes = (bool)$cellsGenerateDWCubes;

            if ($this->cellsGenerateDWCubes === true) {
                $this->createDWCube();
                foreach ($this->getCells() as $cell) {
                    $cell->createDWCube();
                }
            } else {
                $this->deleteDWCube();
                foreach ($this->getCells() as $cell) {
                    $cell->deleteDWCube();
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function getCellsGenerateDWCubes()
    {
        return $this->cellsGenerateDWCubes;
    }

    protected function createDWCube()
    {
        if ($this->dWCube === null) {
            $this->dWCube = new Cube();
            $this->dWCube->setLabel(clone $this->getLabel());

            OrgaDomainHelper::getETLStructureService()->populateGranularityDWCube($this);
        }
    }

    protected function deleteDWCube()
    {
        if ($this->dWCube !== null) {
            $this->dWCube = null;
        }
    }

    /**
     * @throws Core_Exception_UndefinedAttribute
     * @return Cube
     */
    public function getDWCube()
    {
        if (!$this->getCellsGenerateDWCubes()) {
            throw new Core_Exception_UndefinedAttribute('The Granularity does not generate DW Cube.');
        }
        return $this->dWCube;
    }

    /**
     * @param bool $cellsWithACL
     */
    public function setCellsWithACL($cellsWithACL)
    {
        if ($this->cellsWithACL !== $cellsWithACL) {
            // Si la granularité ne gère plus les ACL, suppression des roles existants.
            if (!$cellsWithACL) {
                OrgaDomainHelper::getOrgaACLManager()->clearCellsRolesFromGranularity($this);
            }
            
            $this->cellsWithACL = (bool) $cellsWithACL;
        }
    }

    /**
     * @return bool
     */
    public function getCellsWithACL()
    {
        return $this->cellsWithACL;
    }

    /**
     * @return bool
     */
    public function getCellsWithInputDocuments()
    {
        return $this->cellsWithInputDocs;
    }
}
