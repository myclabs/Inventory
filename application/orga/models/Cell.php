<?php
/**
 * Classe Orga_Model_Cell
 * @author     valentin.claras
 * @author     simon.rieu
 * @package    Orga
 * @subpackage Model
 */

use Doctrine\Common\Collections\Collection;

/**
 * Definit une cellule organisationnelle.
 * @package    Orga
 * @subpackage Model
 */
class Orga_Model_Cell extends Core_Model_Entity
{

    use Core_Event_ObservableTrait;

    // Constantes de tris et de filtres.
    const QUERY_GRANULARITY = 'granularity';
    const QUERY_RELEVANT = 'relevant';
    const QUERY_ALLPARENTSRELEVANT = 'allParentsRelevant';
    const QUERY_MEMBERS_HASHKEY = 'membersHashKey';
    // Constantes d'événement.
    const EVENT_SAVE = 'orgaCellSave';
    const EVENT_DELETE = 'orgaCellDelete';


    /**
     * Identifiant uniqu de la Cell.
     *
     * @var int
     */
    protected $id;

    /**
     * Granularity à laquelle appartient la Cell.
     *
     * @var Orga_Model_Granularity
     */
    protected $granularity;

    /**
     * Collection des Member indexant la Cell.
     *
     * @var Collection|Orga_Model_Member[]
     */
    protected $members = array();

    /**
     * Représentation en chaine de caractère des membres de la cellule.
     *
     * @var string
     */
    protected $membersHashKey = '';

    /**
     * Définit si la cellule est pertinente.
     *
     * @var bool
     */
    protected $relevant = true;

    /**
     * Définit si toute les cellules parentes sont aussi pertinentes.
     *
     * @var bool
     */
    protected $allParentsRelevant = true;


    /**
     * Constructeur de la classe Cell.
     */
    public function __construct()
    {
        $this->members = new Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Fonction appelé avant un persist de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        $this->updateAllParentsRelevant();
        $this->launchEvent(self::EVENT_SAVE);
    }

    /**
     * Fonction appelé avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->launchEvent(self::EVENT_DELETE);
    }

    /**
     * Charge une Cell en fonction de sa Granularity et de ses Member.
     *
     * @param Orga_Model_Granularity $granularity
     * @param Orga_Model_Member[]    $listMembers
     *
     * @return Orga_Model_Cell
     */
    public static function loadByGranularityAndListMembers(Orga_Model_Granularity $granularity, $listMembers)
    {
        return $granularity->getCellByMembers($listMembers);
    }

    /**
     * Définit la Granularity de la Cell
     *
     * @param Orga_Model_Granularity|null $granularity
     * @throws Core_Exception_Duplicate Granularité déjà définie
     */
    public function setGranularity(Orga_Model_Granularity $granularity = null)
    {
        if ($this->granularity !== $granularity) {
            if ($this->granularity !== null) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir la Granularity, elle a déjà été défini.'
                );
            }
            $this->granularity = $granularity;
            $granularity->addCell($this);
        }

    }

    /**
     * Renvoie la Granularity de la Cell.
     *
     * @return Orga_Model_Granularity
     */
    public function getGranularity()
    {
        return $this->granularity;
    }

    /**
     * Ajoute un Member à ceux indexant la Cell.
     *
     * @param Orga_Model_Member $member
     */
    public function addMember(Orga_Model_Member $member)
    {
        if (!($this->hasMember($member))) {
            $this->members->add($member);
            $member->addCell($this);
            $this->updateMembersHashKey();
        }
    }

    /**
     * Vérifie si le Member donné indexe la Cell.
     *
     * @param Orga_Model_Member $member
     *
     * @return boolean
     */
    public function hasMember(Orga_Model_Member $member)
    {
        return $this->members->contains($member);
    }

    /**
     * Supprime le Member donné de ceux indexant la Cell.
     *
     * @param Orga_Model_Member $member
     */
    public function removeMember($member)
    {
        if ($this->hasMember($member)) {
            $this->members->removeElement($member);
            $member->removeCell($this);
            $this->updateMembersHashKey();
        }
    }

    /**
     * Vérifie que la Cell possède au moir un Member.
     *
     * @return bool
     */
    public function hasMembers()
    {
        return !$this->members->isEmpty();
    }

    /**
     * Renvoi tous les Member indexant la Cell.
     *
     * @return Orga_Model_Member[]
     */
    public function getMembers()
    {
        return $this->members->toArray();
    }

    /**
     * Met à jour la member hashKey de la cellule.
     */
    public function updateMembersHashKey()
    {
        $this->membersHashKey = self::buildMembersHashKey($this->getMembers());
    }

    /**
     * Retourne la clé de hashage des membres de la cellule.
     *
     * @return string
     */
    public function getMembersHashKey()
    {
        return $this->membersHashKey;
    }

    /**
     * Construit une chaine de caractère représentant les membres.
     *
     * Les membres sont ordonnés en fonction de la position globle de l'axe.
     *
     * @param Orga_Model_Member[] $listMembers
     *
     * @return string
     */
    public static function buildMembersHashKey($listMembers)
    {
        uasort(
            $listMembers,
            function (Orga_Model_Member $a, Orga_Model_Member $b) {
                return $a->getAxis()->getGlobalPosition() - $b->getAxis()->getGlobalPosition();
            }
        );
        $membersRef = [];

        foreach ($listMembers as $member) {
            $membersRef[] = $member->getCompleteRef();
        }

        return implode('|', $membersRef);
    }

    /**
     * Définit si la Cell est pertinente ou non.
     *
     * @param bool $accessible
     */
    public function setRelevant($accessible)
    {
        if ($accessible != $this->relevant) {
            $this->relevant = $accessible;
            // Si les cellules supérieures ne sont pas pertinentes,
            //  alors modifier celle-ci n'impactera pas les cellules inférieures.
            if ($this->getAllParentsRelevant() === true) {
                // Nécessaire pour mettre à jour l'intégralité des cellules filles.
                foreach ($this->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
                    foreach ($this->getChildCellsForGranularity($narrowerGranularity) as $childCell) {
                        $childAccess = $accessible;
                        if ($childAccess) {
                            foreach ($childCell->getParentCells() as $parentCell) {
                                if (!($parentCell->getRelevant())) {
                                    $childAccess = false;
                                    break;
                                }
                            }
                        }
                        if ($childCell->getAllParentsRelevant() !== $childAccess) {
                            $childCell->setAllParentsRelevant($childAccess);
                        }
                    }
                }
            }
        }
    }

    /**
     * Renvoie la pertinence de la Cell.
     *
     * @return bool
     */
    public function getRelevant()
    {
        return $this->relevant;
    }

    /**
     * Met à jour l'attribut allParentsRelevant.
     */
    public function updateAllParentsRelevant()
    {
        $access = true;
        foreach ($this->getParentCells() as $parentCell) {
            if (!($parentCell->getRelevant())) {
                $access = false;
                break;
            }
        }
        $this->setAllParentsRelevant($access);
    }

    /**
     * Définit si tous les parents de la Cell sont pertinents.
     *
     * @param bool $allParentsRelevant
     */
    protected function setAllParentsRelevant($allParentsRelevant)
    {
        $this->allParentsRelevant = $allParentsRelevant;
    }

    /**
     * Renvoie la pertinence des Cell parentes de la Cell courante.
     *
     * @return bool
     */
    public function getAllParentsRelevant()
    {
        return $this->allParentsRelevant;
    }

    /**
     * Vérifie si la Cell et ses Cell parentes sont pertinentes.
     *
     * @return bool
     */
    public function isRelevant()
    {
        return ($this->getRelevant() && $this->getAllParentsRelevant());
    }

    /**
     * Renvoie le label de la Cell. Basée sur les labels des Member.
     *
     * @return String
     */
    public function getLabel()
    {
        if ($this->members->isEmpty()) {
            return __('Orga', 'navigation', 'labelGlobalCell');
        }

        $labels = [];
        foreach ($this->members as $member) {
            $labels[] = $member->getLabel();
        }

        return implode(' | ', $labels);
    }

    /**
     * Renvoie le label étendue de la Cell. Basée sur les labels étendues des Member.
     *
     * @return String
     */
    public function getLabelExtended()
    {
        if ($this->members->isEmpty()) {
            return __('Orga', 'navigation', 'labelGlobalCellExtended');
        }

        $labels = [];
        foreach ($this->members as $member) {
            $labels[] = $member->getExtendedLabel();
        }

        return implode(' | ', $labels);
    }

    /**
     * Renvoie la liste des Member parents aux Member de la Cell courante pour une Granularity broader donnée.
     *
     * @param Orga_Model_Granularity $broaderGranularity
     *
     * @return Orga_Model_Member[]
     */
    protected function getParentMembersForGranularity($broaderGranularity)
    {
        $parentMembers = array();

        foreach ($this->getMembers() as $member) {
            foreach ($broaderGranularity->getAxes() as $broaderAxis) {
                if ($member->getAxis()->isNarrowerThan($broaderAxis)) {
                    $parentMembers[$broaderAxis->getRef()] = $member->getParentForAxis($broaderAxis);
                } else {
                    if ($member->getAxis() === $broaderAxis) {
                        $parentMembers[$broaderAxis->getRef()] = $member;
                    }
                }
            }
        }

        return $parentMembers;
    }

    /**
     * Renvoie la liste des Member enfants aux Member de la Cell courante pour une Granularity broader donnée.
     *
     * @param Orga_Model_Granularity $narrowerGranularity
     *
     * @return Orga_Model_Member[]
     */
    public function getChildMembersForGranularity($narrowerGranularity)
    {
        $childMembers = array();

        foreach ($this->getMembers() as $member) {
            foreach ($narrowerGranularity->getAxes() as $narrowerAxis) {
                $refNarrowerAxis = $narrowerAxis->getRef();
                if ($member->getAxis()->isBroaderThan($narrowerAxis)) {
                    if (!isset($childMembers[$refNarrowerAxis])) {
                        $childMembers[$refNarrowerAxis] = $member->getChildrenForAxis($narrowerAxis);
                    } else {
                        $childMembers[$refNarrowerAxis] = array_intersect(
                            $childMembers[$refNarrowerAxis],
                            $member->getChildrenForAxis($narrowerAxis)
                        );
                    }
                } else {
                    if ($member->getAxis() === $narrowerAxis) {
                        $childMembers[$refNarrowerAxis] = $member;
                    }
                }
            }
        }

        return $childMembers;
    }

    /**
     * Renvoie la Cell parente pour une Granularity donnée.
     *
     * @param Orga_Model_Granularity $broaderGranularity
     *
     * @throws Core_Exception_InvalidArgument The given granularity is not broader than the current
     * @return Orga_Model_Cell
     */
    public function getParentCellForGranularity($broaderGranularity)
    {
        if (!$this->getGranularity()->isNarrowerThan($broaderGranularity)) {
            throw new Core_Exception_InvalidArgument('The given granularity is not broader than the current');
        }

        return $broaderGranularity->getCellByMembers($this->getParentMembersForGranularity($broaderGranularity));
    }

    /**
     * Renvoie les Cell parentes pour toutes les Granularity broader.
     *
     * @return Orga_Model_Cell[]
     */
    public function getParentCells()
    {
        $parentCells = array();
        foreach ($this->getGranularity()->getBroaderGranularities() as $broaderGranularity) {
            $parentCells[] = $this->getParentCellForGranularity($broaderGranularity);
        }

        return $parentCells;
    }

    /**
     * Renvoie les Cell enfantes pour une Granularity donnée.
     *
     * @param Orga_Model_Granularity $narrowerGranularity
     * @param Core_Model_Query|null  $queryParameters
     *
     * @throws Core_Exception_InvalidArgument The given granularity is not narrower than the current
     * @return Orga_Model_Cell[]
     */
    public function getChildCellsForGranularity($narrowerGranularity, Core_Model_Query $queryParameters = null)
    {
        if (!($this->getGranularity()->isBroaderThan($narrowerGranularity))) {
            throw new Core_Exception_InvalidArgument('The given granularity is not narrower than the current');
        }
        if ($queryParameters === null) {
            $queryParameters = new Core_Model_Query();
            $queryParameters->order->addOrder(self::QUERY_MEMBERS_HASHKEY);
        }

        $childMembersForGranularity = $this->getChildMembersForGranularity($narrowerGranularity);

        // Si l'un des axes de la granularité ne possède pas d'enfants, alors il n'y a pas de cellules enfantes.
        foreach ($childMembersForGranularity as $childAxisMembersForGranularity) {
            if (empty($childAxisMembersForGranularity)) {
                return [];
            }
        }

        $childMembers = array(
            array(
                'granularity' => $narrowerGranularity,
                'members'     => $childMembersForGranularity
            )
        );
        return self::getEntityRepository()->loadByMembers($childMembers, $queryParameters);
    }

    /**
     * Compte le total des Cell enfantes pour une Granularity donnée.
     *
     * @param Orga_Model_Granularity $narrowerGranularity
     * @param Core_Model_Query       $queryParameters
     *
     * @throws Core_Exception_InvalidArgument The given granularity is not narrower than the current
     * @return int
     */
    public function countTotalChildCellsForGranularity($narrowerGranularity, Core_Model_Query $queryParameters = null)
    {
        if (!($this->getGranularity()->isBroaderThan($narrowerGranularity))) {
            throw new Core_Exception_InvalidArgument('The given granularity is not narrower than the current');
        }
        if ($queryParameters === null) {
            $queryParameters = new Core_Model_Query();
            $queryParameters->order->addOrder(self::QUERY_MEMBERS_HASHKEY);
        }

        $childMembersForGranularity = $this->getChildMembersForGranularity($narrowerGranularity);

        // Si l'un des axes de la granularité ne possède pas d'enfants, alors il n'y a pas de cellules enfantes.
        foreach ($childMembersForGranularity as $childAxisMembersForGranularity) {
            if (empty($childAxisMembersForGranularity)) {
                return array();
            }
        }

        $childMembers = array(
            array(
                'granularity' => $narrowerGranularity,
                'members'     => $childMembersForGranularity
            )
        );
        return self::getEntityRepository()->countTotalByMembers($childMembers, $queryParameters);
    }

    /**
     * Renvoie les Cell enfantes pour toutes les Granularity narrower.
     *
     * @param Core_Model_Query $queryParameters
     *
     * @return Orga_Model_Cell[]
     */
    public function getChildCells(Core_Model_Query $queryParameters = null)
    {
        if ($queryParameters === null) {
            $queryParameters = new Core_Model_Query();
            $queryParameters->order->addOrder(self::QUERY_MEMBERS_HASHKEY);
        }

        $childMembers = array();
        foreach ($this->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            $childMembersForGranularity = $this->getChildMembersForGranularity($narrowerGranularity);
            // Si l'un des axes de la granularité ne possède pas d'enfants, alors il n'y a pas de cellules enfantes.
            foreach ($childMembersForGranularity as $childAxisMembersForGranularity) {
                if (empty($childAxisMembersForGranularity)) {
                    continue 2;
                }
            }

            $childMembers[] = array(
                'granularity' => $narrowerGranularity,
                'members'     => $childMembersForGranularity
            );
        }

        if (empty($childMembers)) {
            return array();
        }
        return self::getEntityRepository()->loadByMembers($childMembers, $queryParameters);
    }

    /**
     * Compte le total des Cell enfantes pour toutes les Granularity narrower.
     *
     * @param Core_Model_Query $queryParameters
     *
     * @return int
     */
    public function countTotalChildCells(Core_Model_Query $queryParameters = null)
    {
        if ($queryParameters === null) {
            $queryParameters = new Core_Model_Query();
            $queryParameters->order->addOrder(self::QUERY_MEMBERS_HASHKEY);
        }

        $childMembers = array();
        foreach ($this->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            $childMembersForGranularity = $this->getChildMembersForGranularity($narrowerGranularity);
            // Si l'un des axes de la granularité ne possède pas d'enfants, alors il n'y a pas de cellules enfantes.
            foreach ($childMembersForGranularity as $childAxisMembersForGranularity) {
                if (empty($childAxisMembersForGranularity)) {
                    continue 2;
                }
            }

            $childMembers[] = array(
                'granularity' => $narrowerGranularity,
                'members'     => $childMembersForGranularity
            );
        }

        if (empty($childMembers)) {
            return 0;
        }
        return self::getEntityRepository()->countTotalByMembers($childMembers, $queryParameters);
    }

    /**
     * @return string Représentation textuelle de l'unité
     */
    public function __toString()
    {
        return $this->getMembersHashKey();
    }

}