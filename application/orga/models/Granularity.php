<?php
/**
 * Classe Orga_Model_Granularity
 * @author valentin.claras
 * @author diana.dragusin
 * @package    Orga
 * @subpackage Model
 */
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Objet métier Granularité : ensemble d'Axis formant des Cell pour chaque association de Member.
 * @package    Orga
 * @subpackage Model
 */
class Orga_Model_Granularity extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_POSITION = 'position';
    const QUERY_ORGANIZATION = 'organization';


    /**
     * Identifiant unique de la Granularity.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Référence unique (au sein d'un organization) de la Granularity.
     *
     * @var string
     */
    protected  $ref = null;

    /**
     * Organization contenant la Granularity.
     *
     * @var Orga_Model_Organization
     */
    protected $organization = null;

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
     * Indique la Granularity configurant cette Granularity de saisie.
     *
     * @var Orga_Model_Granularity
     */
    protected $inputConfigGranularity = null;

    /**
     * Collection des Granularity de saisie configurées par cette Granularity.
     *
     * @var Collection|Orga_Model_Granularity[]
     */
    protected $inputGranularities = null;

    /**
     * Défini si les cellules génerent un Organization de DW.
     *
     * @var bool
     */
    protected $cellsGenerateDWCubes = false;

    /**
     * Organization de DW généré par et propre à la Cell.
     *
     * @var DW_model_cube
     */
    protected $dWCube = null;

    /**
     * Défini si les cellules de la granularité utilisent les ACL.
     *
     * @var bool
     */
    protected $cellsWithACL = false;

    /**
     * Défini si les cellules de la granularité affichent l'onglet d'Orga.
     *
     * @var bool
     */
    protected $cellsWithOrgaTab = false;

    /**
     * Défini si les cellules de la granularité affichent l'onglet de configuration des AF.
     *
     * @var bool
     */
    protected $cellsWithAFConfigTab = false;

    /**
     * Défini si les cellules de la granularité comportent des GenericAction.
     *
     * @var bool
     */
    protected $cellsWithSocialGenericActions = false;

    /**
     * Défini si les cellules de la granularité comportent des ContextAction.
     *
     * @var bool
     */
    protected $cellsWithSocialContextActions = false;

    /**
     * Défini si les cellules de la granularité contiennent des documents.
     *
     * @var bool
     */
    protected $cellsWithInputDocs = false;


    /**
     * Constructeur de la classe Granularity.
     *
     * @param Orga_Model_Organization $organization
     * @param Orga_Model_Axis[] $axes
     */
    public function __construct(Orga_Model_Organization $organization, array $axes=array())
    {
        $this->axes = new ArrayCollection();
        $this->cells = new ArrayCollection();
        $this->inputGranularities = new ArrayCollection();

        $this->organization = $organization;
        foreach ($axes as $axis) {
            if (!($this->hasAxis($axis))) {
                $this->axes->add($axis);
                $axis->addGranularity($this);
            }
        }
        $this->updateRef();
        $this->traverseAxesThenCreateCells();
        $organization->addGranularity($this);
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     *
     * @return array
     */
    protected function getContext()
    {
        return array('organization' => $this->organization);
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
     * Charge une Granularity en fonction de sa référence et de son Organization.
     *
     * @param string $ref
     * @param Orga_Model_Organization $organization
     *
     * @return Orga_Model_Granularity
     */
    public static function loadByRefAndOrganization($ref, $organization)
    {
        return $organization->getGranularityByRef($ref);
    }

    /**
     * Charge le Granularity correspondant à un Organization de DW.
     *
     * @param DW_model_cube $dWCube
     *
     * @return Orga_Model_Granularity
     */
    public static function loadByDWCube($dWCube)
    {
        return self::getEntityRepository()->loadBy(array('dWCube' => $dWCube));
    }

    /**
     * Renvoie l'id de la Granularity.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Renvoie le Organization de la Granularity.
     *
     * @return Orga_Model_Organization
     */
    public function getOrganization()
    {
        return $this->organization;
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
     * Permet de mettre à jour la ref Granularity.
     */
    public function updateRef()
    {
        $this->ref = self::buildRefFromAxes($this->getAxes());
    }

    /**
     * Renvoi la ref d'une granularité à partir d'un ensemble de ref d'axes.
     *
     * @param Orga_Model_Axis[] $axes
     *
     * @return string
     */
    public static function buildRefFromAxes($axes)
    {
        $axesRefParts = array();
        // Suppression des erreurs avec '@' dans le cas ou des proxies sont utilisées.
        @uasort($axes, array('Orga_Model_Axis', 'orderAxes'));
        foreach ($axes as $axis) {
            $axesRefParts[] = $axis->getRef();
        }

        if (empty($axesRefParts)) {
            return 'global';
        } else {
            return implode('|', $axesRefParts);
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
            $axes = $this->getAxes();
            // Suppression des erreurs avec '@' dans le cas ou des proxies sont utilisées.
            @uasort($axes, array('Orga_Model_Axis', 'orderAxes'));
            foreach ($axes as $axis) {
                $labelParts[] = $axis->getLabel();
            }
            $label = implode(' | ', $labelParts);
        }
        return $label;
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
        } else if ($this->axes[$indexCurrentAxis] === $ignoredAxis) {
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
        $this->cells->add(new Orga_Model_Cell($this, $members));
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
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     * 
     * @return Orga_Model_Cell
     */
    public function getCellByMembers($listMembers)
    {
        $matchingCells = $this->getCellsByMembers($listMembers);

        if (empty($matchingCells)) {
            $membersHashKey = Orga_Model_Cell::buildMembersHashKey($listMembers);
            throw new Core_Exception_NotFound('No "Orga_Model_Cell" matching attributes '.$membersHashKey);
        } else if (count($matchingCells) > 1) {
            $membersHashKey = Orga_Model_Cell::buildMembersHashKey($listMembers);
            throw new Core_Exception_TooMany('Too many "Orga_Model_Cell" matching attributes '.$membersHashKey);
        }

        return array_pop($matchingCells);
    }

    /**
     * Renvoie les cellule correspondants aux membres données.
     *
     * @param Orga_Model_Member[] $listMembers
     *
     * @return Orga_Model_Cell[]
     */
    public function getCellsByMembers($listMembers)
    {
        $criteria = \Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('granularity', $this));

        $axisMembers = [];
        foreach ($this->axes as $indexAxis => $axis) {
            $axisMembers[$indexAxis] = [];
            foreach ($listMembers as $member) {
                if ($axis->hasMember($member)) {
                    $axisMembers[$indexAxis][] = $member;
                }
            }
            if (empty($axisMembers[$indexAxis])) {
                $axisMembers[$indexAxis] = $axis->getMembers();
            }
        }

        $expressions = [];
        foreach ($this->traverseMembersThenBuildMembersHashKey($axisMembers) as $membersHashKey) {
            $expressions[] = $criteria->expr()->eq('membersHashKey', $membersHashKey);
        }
        if (count($expressions) > 1) {
            $criteria->andWhere(
                new Doctrine\Common\Collections\Expr\CompositeExpression(
                    Doctrine\Common\Collections\Expr\CompositeExpression::TYPE_OR,
                    $expressions
                )
            );
        } else if (count($expressions) > 0) {
            $criteria->andWhere(array_pop($expressions));
        }
        $matchingCells = $this->cells->matching($criteria);

        return $matchingCells->toArray();
    }

    /**
     * Parcours les Axis et crée les Cell..
     *
     * @param array $axisMembers
     * @param int $indexCurrentAxis
     * @param Orga_Model_Member[] $selectedMembers
     *
     * @return string[]
     */
    public function traverseMembersThenBuildMembersHashKey(array $axisMembers, $indexCurrentAxis = 0,
        array $selectedMembers=array())
    {
        $membersHashKeys = array();

        if ($indexCurrentAxis >= count($this->axes)) {
            return [Orga_Model_Cell::buildMembersHashKey($selectedMembers)];
        } else {
            foreach ($axisMembers[$indexCurrentAxis] as $currentAxisMember) {
                $nextSelectedMembers = array_merge($selectedMembers, array($currentAxisMember));
                $membersHashKeys = array_merge(
                    $membersHashKeys,
                    $this->traverseMembersThenBuildMembersHashKey(
                        $axisMembers,
                        $indexCurrentAxis + 1,
                        $nextSelectedMembers
                    )
                );
            }
        }

        return $membersHashKeys;
    }

    /**
     * Définit la navigabilité de la Granularity.
     *
     * @param boolean $navigable
     */
    public function setNavigability($navigable)
    {
        $this->navigable = (bool) $navigable;
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
     * Indique si la Granularity courante est narrower (ou égale) de la Granularity donnée.
     *
     * @param Orga_Model_Granularity $broaderGranularity
     *
     * @return boolean
     */
    public function isNarrowerThan($broaderGranularity)
    {
        if ($broaderGranularity->getRef() === $this->getRef()) {
            return false;
        }

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

        foreach ($this->getOrganization()->getGranularities() as $granularity) {
            if (($granularity->getRef() !== $this->getRef()) && ($granularity->isNarrowerThan($this))) {
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

        foreach ($this->getOrganization()->getGranularities() as $granularity) {
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

        return $this->organization->getGranularityByRef(self::buildRefFromAxes(array_merge($currentAxes, $crossingAxes)));
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
                foreach ($crossingGranularity->getAxes() as $crossingAxis) {
                    if ($currentAxis->isNarrowerThan($crossingAxis)) {
                        $encompassingAxes[$currentIndex] = $crossingAxis;
                    } else {
                        $encompassingAxes[$currentIndex] = $currentAxis;
                    }
                }
            }
        }

        return $this->organization->getGranularityByRef(self::buildRefFromAxes($encompassingAxes));
    }

    /**
     * Défini la Granularity utilisé pour configerer cett Granularity de saisie..
     *
     * @param Orga_Model_Granularity $configGranularity
     */
    public function setInputConfigGranularity($configGranularity=null)
    {
        if ($this->inputConfigGranularity !== $configGranularity) {
            if ($this->inputConfigGranularity !== null) {
                $this->inputConfigGranularity->removeInputGranularity($this);
            }

            $this->inputConfigGranularity = $configGranularity;

            if ($configGranularity !== null) {
                $configGranularity->addInputGranularity($this);

                foreach ($this->getCells() as $cell) {
                    $cell->setDocBibliographyForAFInputSetPrimary(new Doc_Model_Bibliography());
                }
            } else {
                foreach ($this->getCells() as $cell) {
                    $cell->setDocBibliographyForAFInputSetPrimary();
                    try {
                        $cell->setAFInputSetPrimary();
                    } catch (Core_Exception_UndefinedAttribute $e) {
                        // Pas de saisie pour cette cellule.
                    }
                }
            }
        }
    }

    /**
     * Renvoi la Granularity de configuration des saisies.
     *
     * @throws Core_Exception_UndefinedAttribute
     * 
     * @return Orga_Model_Granularity
     */
    public function getInputConfigGranularity()
    {
        return $this->inputConfigGranularity;
    }

    /**
     * Ajoute une Granularity de saisie configurée par cette Granularity.
     *
     * @param Orga_Model_Granularity $inputGranularity
     */
    public function addInputGranularity(Orga_Model_Granularity $inputGranularity)
    {
        if (!($this->hasInputGranularity($inputGranularity))) {
            $this->inputGranularities->add($inputGranularity);
            $inputGranularity->setInputConfigGranularity($this);

            foreach ($this->getCells() as $cell) {
                $cellsGroup = new Orga_Model_CellsGroup($cell, $inputGranularity);
            }
        }
    }

    /**
     * Vérifie si la Granularity possède le Granularity de saisie donnée.
     *
     * @param Orga_Model_Granularity $inputGranularity
     *
     * @return boolean
     */
    public function hasInputGranularity(Orga_Model_Granularity $inputGranularity)
    {
        return $this->inputGranularities->contains($inputGranularity);
    }

    /**
     * Retire une Granularity de saisie configurée par cette Granularity.
     *
     * @param Orga_Model_Granularity $inputGranularity
     */
    public function removeInputGranularity($inputGranularity)
    {
        if ($this->hasInputGranularity($inputGranularity)) {
            $this->inputGranularities->removeElement($inputGranularity);

            foreach ($this->getCells() as $cell) {
                $cellsGroup = $cell->getCellsGroupForInputGranularity($inputGranularity);
                $cell->removeCellsGroup($cellsGroup);
            }
        }
    }

    /**
     * Vérifie que la Granularity possède au moins une Granularity de saisie.
     *
     * @return bool
     */
    public function hasInputGranularities()
    {
        return !$this->inputGranularities->isEmpty();
    }

    /**
     * Renvoie un tableau des Granularity de saisie configurées par cette Granularity.
     *
     * @return Orga_Model_Granularity[]
     */
    public function getInputGranularities()
    {
        return $this->inputGranularities->toArray();
    }

    /**
     * Défini si les cellules de la granularité génereront des organizations de DW.
     *
     * @param bool $bool
     */
    public function setCellsGenerateDWCubes($bool)
    {
        $this->cellsGenerateDWCubes = (bool) $bool;
        if ($this->cellsGenerateDWCubes === true) {
            $actionToDWCube = 'createDWCube';
        } else {
            $actionToDWCube = 'deleteDWCube';
        }

        $this->$actionToDWCube();
        foreach ($this->getCells() as $cell) {
            $cell->$actionToDWCube();
        }
    }

    /**
     * Indique si les cellules de la granularité génerent des organizations de DW.
     *
     * @return bool
     */
    public function getCellsGenerateDWCubes()
    {
        return $this->cellsGenerateDWCubes;
    }

    /**
     * Créé le Organization pour la simulation.
     *
     * @return int Identifiant unique du Organization.
     */
    protected function createDWCube()
    {
        if ($this->dWCube === null) {
            $this->dWCube = new DW_model_cube();
            $this->dWCube->setLabel($this->getLabel());

            /** @var \DI\Container $container */
            $container = Zend_Registry::get('container');
            /** @var Orga_Service_ETLStructure $etlStructureService */
            $etlStructureService = $container->get('Orga_Service_ETLStructure');

            $etlStructureService->populateGranularityDWCube($this);
        }
    }

    /**
     * Créé le Organization pour la simulation.
     *
     * @return int Identifiant unique du Organization.
     */
    protected function deleteDWCube()
    {
        if ($this->dWCube !== null) {
            $this->dWCube->delete();
            $this->dWCube = null;
        }
    }

    /**
     * Renvoi le Organization de DW spécifique à la Cell.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return DW_model_cube
     */
    public function getDWCube()
    {
        if (!$this->cellsGenerateDWCubes) {
            throw new Core_Exception_UndefinedAttribute('La Granularity de la Cell ne génère pas de DWCube');
        }
        return $this->dWCube;
    }

    /**
     * Défini si les cellules de la granularité possèdent des droits d'accès.
     *
     * @param bool $bool
     */
    public function setCellsWithACL($bool)
    {
        $this->cellsWithACL = (bool) $bool;
    }

    /**
     * Indique si les cellules de la granularité possèdent des droits d'accès.
     *
     * @return bool
     */
    public function getCellsWithACL()
    {
        return $this->cellsWithACL;
    }

    /**
     * Défini si les cellules de la granularité afficheront le tab d'Orga.
     *
     * @param bool $bool
     */
    public function setCellsWithOrgaTab($bool)
    {
        $this->cellsWithOrgaTab = (bool) $bool;
    }

    /**
     * Indique si les cellules de la granularité affichent le tab d'Orga.
     *
     * @return bool
     */
    public function getCellsWithOrgaTab()
    {
        return $this->cellsWithOrgaTab;
    }

    /**
     * Défini si les cellules de la granularité afficheront le tab de configuration d'AF.
     *
     * @param bool $bool
     */
    public function setCellsWithAFConfigTab($bool)
    {
        $this->cellsWithAFConfigTab = (bool) $bool;
    }

    /**
     * Indique si les cellules de la granularité affichent le tab de configuration d'AF.
     *
     * @return bool
     */
    public function getCellsWithAFConfigTab()
    {
        return $this->cellsWithAFConfigTab;
    }

    /**
     * Défini si les cellules de la granularité posséderont des GenericAction de Social.
     *
     * @param bool $bool
     *
     * @throws Core_Exception_User
     */
    public function setCellsWithSocialGenericActions($bool)
    {
        if ($this->cellsWithSocialGenericActions !== (bool) $bool) {
            $this->cellsWithSocialGenericActions = (bool) $bool;
            if ($this->cellsWithSocialGenericActions === false) {
                foreach ($this->getCells() as $cell) {
                    if ($cell->getDocLibraryForSocialGenericAction()->hasDocuments()) {
                        throw new Core_Exception_User('Orga', 'exception', 'changeCellsWithSocialGenericActions');
                    }
                }
                foreach ($this->getCells() as $cell) {
                    $cell->setDocLibraryForSocialGenericAction();
                }
            } else  {
                foreach ($this->getCells() as $cell) {
                    $cell->setDocLibraryForSocialGenericAction(new Doc_Model_Library());
                }
            }
        }
    }

    /**
     * Indique si les cellules de la granularité possédent des GenericAction de Social.
     *
     * @return bool
     */
    public function getCellsWithSocialGenericActions()
    {
        return $this->cellsWithSocialGenericActions;
    }

    /**
     * Défini si les cellules de la granularité posséderont des GenericAction de Social.
     *
     * @param bool $bool
     *
     * @throws Core_Exception_User
     */
    public function setCellsWithSocialContextActions($bool)
    {
        if ($this->cellsWithSocialContextActions !== (bool) $bool) {
            $this->cellsWithSocialContextActions = (bool) $bool;
            if ($this->cellsWithSocialContextActions === false) {
                foreach ($this->getCells() as $cell) {
                    if ($cell->getDocLibraryForSocialContextAction()->hasDocuments()) {
                        throw new Core_Exception_User('Orga', 'exception', 'changeCellsWithSocialContextActions');
                    }
                }
                foreach ($this->getCells() as $cell) {
                    $cell->setDocLibraryForSocialContextAction();
                }
            } else  {
                foreach ($this->getCells() as $cell) {
                    $cell->setDocLibraryForSocialContextAction(new Doc_Model_Library());
                }
            }
        }
    }

    /**
     * Indique si les cellules de la granularité possédent des ContextAction de Social.
     *
     * @return bool
     */
    public function getCellsWithSocialContextActions()
    {
        return $this->cellsWithSocialContextActions;
    }

    /**
     * Défini si les cellules de la granularité possèderont des Doc pour l'InputSetPrimary.
     *
     * @param bool $bool
     *
     * @throws Core_Exception_User
     */
    public function setCellsWithInputDocuments($bool)
    {
        if ($this->cellsWithInputDocs !== (bool) $bool) {
            $this->cellsWithInputDocs = (bool) $bool;
            if ($this->cellsWithInputDocs === false) {
                foreach ($this->getCells() as $cell) {
                    if ($cell->getDocLibraryForAFInputSetsPrimary()->hasDocuments()) {
                        throw new Core_Exception_User('Orga', 'exception', 'changeCellsWithInputDocs');
                    }
                }
                foreach ($this->getCells() as $cell) {
                    $cell->setDocLibraryForAFInputSetsPrimary();
                }
            } else  {
                foreach ($this->getCells() as $cell) {
                    $cell->setDocLibraryForAFInputSetsPrimary(new Doc_Model_Library());
                }
            }
        }
    }

    /**
     * Indique si les cellules de la granularité possèdent des Doc pour l'InputSetPrimary.
     *
     * @return bool
     */
    public function getCellsWithInputDocuments()
    {
        return $this->cellsWithInputDocs;
    }

    /**
     * @return string Représentation textuelle de la Granularity
     */
    public function __toString()
    {
        return $this->getRef();
    }

}
