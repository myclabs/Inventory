<?php
/**
 * Classe Orga_Model_Organization
 * @author     valentin.claras
 * @author     maxime.fourt
 * @package    Orga
 * @subpackage Model
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Orga\Model\ACL\OrganizationAuthorization;
use User\Domain\ACL\Resource\Resource;
use User\Domain\ACL\Resource\ResourceTrait;

/**
 * Organization organisationnel.
 * @package    Orga
 * @subpackage Model
 */
class Orga_Model_Organization extends Core_Model_Entity implements Resource
{
    use Core_Model_Entity_Translatable;
    use ResourceTrait;

    // Constantes de path des Axis, Member, Granularity et Cell.
    const PATH_SEPARATOR = '/';
    const PATH_JOIN = '&';

    /**
     * Identifiant unique du Organization.
     *
     * @var string
     */
    protected $id = null;

    /**
     * Label du Organization.
     *
     * @var string
     */
    protected $label = '';

    /**
     * Collection des Axis du Organization.
     *
     * @var Collection|Orga_Model_Axis[]
     */
    protected $axes = null;

    /**
     * Collection des Granularity du Organization.
     *
     * @var Collection|Orga_Model_Granularity[]
     */
    protected $granularities = null;

    /**
     * Granularity organisationnelle où est spécifiée le statut des inventaires.
     *
     * @var Orga_Model_Granularity
     */
    protected $granularityForInventoryStatus = null;

    /**
     * @var OrganizationAuthorization[]|Collection
     */
    protected $acl;


    /**
     * Constructeur de la classe Organization.
     */
    public function __construct()
    {
        $this->axes = new ArrayCollection();
        $this->granularities = new ArrayCollection();
        $this->acl = new ArrayCollection();
    }

    /**
     * Renvoie l'id du Organization.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Spécifie le label du Organization.
     *
     * @param string $label
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Renvoie le label textuel du projet.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Ajoute un Axis à la collection du Organization.
     *
     * @param Orga_Model_Axis $axis
     *
     * @throws Core_Exception_InvalidArgument
     * @throws Core_Exception_Duplicate
     */
    public function addAxis(Orga_Model_Axis $axis)
    {
        if ($axis->getOrganization() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }
        if (!$this->hasAxis($axis)) {
            $this->axes->add($axis);
        }
    }

    /**
     * Vérifie si l'Axis donné appartient à ceux du Organization.
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
     * Retourne un Axis du organization en fonction de la ref donnée.
     *
     * @param string $ref
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Orga_Model_Axis
     */
    public function getAxisByRef($ref)
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $axis = $this->axes->matching($criteria)->toArray();

        if (count($axis) === 0) {
            throw new Core_Exception_NotFound('No Axis in Organization matching ref "'.$ref.'".');
        } else if (count($axis) > 1) {
            throw new Core_Exception_TooMany('Too many Axis in Organization matching "'.$ref.'".');
        }

        return array_pop($axis);
    }

    /**
     * Retire un Axis de ceux du Organization.
     *
     * @param Orga_Model_Axis $axis
     */
    public function removeAxis(Orga_Model_Axis $axis)
    {
        if ($this->hasAxis($axis)) {
            $narrowerAxis = $axis->getDirectNarrower();
            // Déplacement des broaders au narrower.
            foreach ($axis->getDirectBroaders() as $broaderAxis) {
                $broaderAxis->moveTo($narrowerAxis);
            }
            // Déplacement de l'axe à la racine pour retirer du narrower et placer à la fin.
            if ($narrowerAxis !== null) {
                $axis->moveTo();
                foreach ($axis->getMembers() as $axisMember) {
                    foreach ($axisMember->getDirectChildren() as $childMember) {
                        $childMember->removeDirectParentForAxis($axisMember);
                    }
                }
            } else {
                $axis->setPosition($axis->getLastEligiblePosition());
            }

            $this->axes->removeElement($axis);
            $axis->removeFromOrganization();

            // Suppression des granularités liés.
            foreach ($axis->getGranularities() as $granularity) {
                $this->removeGranularity($granularity);
            }
        }
    }

    /**
     * Vérifie si le Organization ossède au moins un Axis.
     *
     * @return bool
     */
    public function hasAxes()
    {
        return !$this->axes->isEmpty();
    }

    /**
     * Renvoie les Axis du Organization.
     *
     * @return Collection|Orga_Model_Axis[]
     */
    public function getAxes()
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->orderBy(['narrowerTag' => 'ASC']);
        return $this->axes->matching($criteria);
    }

    /**
     * Retourne un tableau contenant les Axis racines du Organization.
     *
     * @return Orga_Model_Axis[]
     */
    public function getRootAxes()
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where(Doctrine\Common\Collections\Criteria::expr()->isNull('directNarrower'));
        $criteria->orderBy(['position' => 'ASC']);
        return $this->axes->matching($criteria)->toArray();
    }

    /**
     * Retourne un tableau contenant les Axis du Organization ordonnés par première exploration.
     *
     * @return Orga_Model_Axis[]
     */
    public function getFirstOrderedAxes()
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->orderBy(['narrowerTag' => 'ASC']);
        return $this->axes->matching($criteria)->toArray();
    }

    /**
     * Retourne un tableau contenant les Axis du Organization ordonnés par dernière exploration.
     *
     * @return Orga_Model_Axis[]
     */
    public function getLastOrderedAxes()
    {
        $axes = $this->getFirstOrderedAxes();
        @usort($axes, ['Orga_Model_Axis', 'lastOrderAxes']);
        return $axes;
    }

    /**
     * Ajoute une Granularity au Organization
     *
     * @param Orga_Model_Granularity $granularity
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addGranularity(Orga_Model_Granularity $granularity)
    {
        if ($granularity->getOrganization() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasGranularity($granularity)) {
            $this->granularities->add($granularity);
        }
    }

    /**
     * Vérifie que la Granularity donnée appartient à celles du Organization.
     *
     * @param Orga_Model_Granularity $granularity
     *
     * @return boolean
     */
    public function hasGranularity(Orga_Model_Granularity $granularity)
    {
        return $this->granularities->contains($granularity);
    }

    /**
     * Retourne une Granularity du organization en fonction de la ref donnée.
     *
     * @param string $ref
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Orga_Model_Granularity
     */
    public function getGranularityByRef($ref)
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $granularity = $this->granularities->matching($criteria)->toArray();

        if (empty($granularity)) {
            throw new Core_Exception_NotFound('No Granularity in Organization matching ref "'.$ref.'".');
        } else {
            if (count($granularity) > 1) {
                throw new Core_Exception_TooMany('Too many Granularity in Organization matching ref "'.$ref.'".');
            }
        }

        return array_pop($granularity);
    }

    /**
     * Retire la Granularity donnée de celles du Organization.
     *
     * @param Orga_Model_Granularity $granularity
     */
    public function removeGranularity(Orga_Model_Granularity $granularity)
    {
        if ($this->hasGranularity($granularity)) {
            $cellChildCells = [];
            foreach ($granularity->getCells() as $cell) {
                $cellChildCells[$cell->getMembersHashKey()] = $cell->getChildCells();
            }

            $this->granularities->removeElement($granularity);

            /** @var Orga_Model_Cell[] $childCells */
            foreach ($cellChildCells as $childCells) {
                foreach ($childCells as $childCell) {
                    $childCell->updateHierarchy();
                }
            }
        }
    }

    /**
     * Vérifie que le Organization possède au moins une Granularity.
     *
     * @return bool
     */
    public function hasGranularities()
    {
        return !$this->granularities->isEmpty();
    }

    /**
     * Renvoie un tableau des Granularity du Organization.
     *
     * @return Collection|Orga_Model_Granularity[]
     */
    public function getGranularities()
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->orderBy(['tag' => 'ASC']);
        return $this->granularities->matching($criteria);
    }

    /**
     * Ordonne les Granularity dans le Organization.
     *
     * @return array
     */
    public function orderGranularities()
    {
        $granularities = array();
        foreach ($this->getGranularities() as $granularity) {
            $granularities[spl_object_hash($granularity)] = array(
                'granularity' => $granularity,
                'position'    => ''
            );
        }

        if (count($granularities) > 1) {
            foreach ($this->getFirstOrderedAxes() as $index => $axis) {
                foreach ($this->getGranularities() as $granularity) {
                    if (!$axis->hasGranularity($granularity)) {
                        $granularities[spl_object_hash($granularity)]['position'] .= '1';
                    } else {
                        $granularities[spl_object_hash($granularity)]['position'] .= '0';
                    }
                }
            }
        }

        $orderedGranularities = array();
        foreach ($granularities as $granularity) {
            $orderedGranularities[$granularity['position']] = $granularity['granularity'];
        }
        ksort($orderedGranularities);

        foreach ($orderedGranularities as $position => $orderedGranularity) {
            try {
                $orderedGranularity->setPosition(1);
            } catch (Core_Exception_UndefinedAttribute $e) {
                // La Granularity n'a pas de position, elle est donc en train d'être supprimée.
            }
        }
    }

    /**
     * Spécifie la Granularity où est spécifié le statut des inventaires.
     *
     * @param Orga_Model_Granularity $granularity
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setGranularityForInventoryStatus(Orga_Model_Granularity $granularity=null)
    {
        if ($this->granularityForInventoryStatus !== $granularity) {
            if ($this->granularityForInventoryStatus !== null) {
                foreach ($this->granularityForInventoryStatus->getCells() as $cell) {
                    $cell->setInventoryStatus(Orga_Model_Cell::STATUS_NOTLAUNCHED);
                }
            }
            $this->granularityForInventoryStatus = $granularity;
        }
    }

    /**
     * Renvoie l'instance de la Granularity où est spécifié le statut des inventaires.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return Orga_Model_Granularity
     */
    public function getGranularityForInventoryStatus()
    {
        if ($this->granularityForInventoryStatus === null) {
            throw new Core_Exception_UndefinedAttribute('Granularity for inventory status has not been chosen.');
        }
        return $this->granularityForInventoryStatus;
    }

    /**
     * Renvoie les Granularity de saisie.
     *
     * @return Orga_Model_Granularity[]
     */
    public function getInputGranularities()
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where(Doctrine\Common\Collections\Criteria::expr()->neq('inputConfigGranularity', null));
        $criteria->orderBy(['tag' => 'ASC']);
        return $this->granularities->matching($criteria)->toArray();
    }

}