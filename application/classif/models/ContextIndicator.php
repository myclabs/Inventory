<?php
/**
 * Classe Classif_Model_ContextIndicator
 * @author valentin.claras
 * @author cyril.perraud
 * @package    Classif
 * @subpackage Model
 */

/**
 * Permet de gérer un indicateur contextualisé.
 * @package    Classif
 * @subpackage Model
 */
class Classif_Model_ContextIndicator extends Core_Model_Entity
{

    // Constantes de tris et de filtres.
    const QUERY_CONTEXT = 'context';
    const QUERY_INDICATOR = 'indicator';


    /**
     * Contexte contextualisant.
     *
     * @var Classif_Model_Context
     */
    protected $context;

    /**
     * Indicator contextualisé.
     *
     * @var Classif_Model_Indicator
     */
    protected $indicator;

    /**
     * Collection d'Axis regroupé dans le ContextIndicator.
     * @var Doctrine\Common\Collections\Collection|Classif_Model_Axis[]
     */
    protected $axes;


    /**
     * Constructeur de la classe ContextIndicator.
     */
    public function __construct()
    {
        $this->axes = new Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Spécifie le Context.
     *
     * @param Classif_Model_Context $context
     */
    public function setContext($context)
    {
        if ($this->context !== null) {
            throw new Core_Exception_TooMany('The Context has already been defined.');
        }
        $this->context = $context;
    }

    /**
     * Retourne le context.
     *
     * @return Classif_Model_Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Spécifie l'Indicator.
     *
     * @param Classif_Model_Indicator $indicator
     */
    public function setIndicator($indicator)
    {
        if ($this->indicator !== null) {
            throw new Core_Exception_TooMany('The Indicator has already been defined.');
        }
        $this->indicator = $indicator;
    }

    /**
     * Retourne l'Indicator.
     *
     * @return Classif_Model_Indicator
     */
    public function getIndicator()
    {
        return $this->indicator;
    }

    /**
     * Ajoute un Axis donné à la collection du ContextIndicator.
     *
     * @param Classif_Model_Axis $axis
     */
    public function addAxis(Classif_Model_Axis $axis)
    {
        if (!($this->hasAxis($axis))) {
            $this->axes->add($axis);
        }
    }

    /**
     * Vérifie si l'Axis donné est bien dans la collection du ContextIndicator.
     *
     * @param Classif_Model_Axis $axis
     *
     * @return boolean
     */
    public function hasAxis(Classif_Model_Axis $axis)
    {
        return $this->axes->contains($axis);
    }

    /**
     * Supprime l'Axis de la collection du ContextIndicator.
     *
     * @param Classif_Model_Axis $axis
     */
    public function removeAxis($axis)
    {
        if ($this->hasAxis($axis)) {
            $this->axes->removeElement($axis);
        }
    }

    /**
     * Indique si le ContextIndicator possède des Axis.
     *
     * @return bool
     */
    public function hasAxes()
    {
        return !$this->axes->isEmpty();
    }

    /**
     * Retourne l'ensemble des Axis du ContextIndicator.
     *
     * @return Classif_Model_Axis[]
     */
    public function getAxes()
    {
        return $this->axes->toArray();
    }

    /**
     * Charge un ContextIndicator
     *
     * @param string $refContext
     * @param string $refIndicator
     * @return Classif_Model_ContextIndicator
     */
    public static function loadByRef($refContext, $refIndicator)
    {
        $query = self::getEntityManager()->createQuery(
            "SELECT ci FROM Classif_Model_ContextIndicator ci
            LEFT JOIN ci.context c
            LEFT JOIN ci.indicator i
            WHERE c.ref = ?1 AND i.ref = ?2");
        $query->setParameters([1 => $refContext, 2 => $refIndicator]);
        try {
            return $query->getSingleResult();
        } catch (Doctrine\ORM\NoResultException $e) {
            throw new Core_Exception_NotFound("ContextIndicator not found matching context=$refContext "
                                                  . "and indicator=$refIndicator");
        }
    }

}
