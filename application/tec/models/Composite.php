<?php
/**
 * @author valentin.claras
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package TEC
 */

/**
 * @package TEC
 * @subpackage Model
 */
class TEC_Model_Composite extends TEC_Model_Component
{
   /**
    * Constante précisant qu'il s'agit d'une somme.
    *
    * @var const
    */
    const OPERATOR_SUM = 'sum';

   /**
    * Constante précisant qu'il s'agit d'un produit.
    *
    * @var const
    */
    const OPERATOR_PRODUCT = 'mul';

   /**
    * Constante précisant qu'il s'agit d'un 'et'.
    *
    * @var const
    */
    const LOGICAL_AND ='and';

   /**
    * Constante précisant qu'il s'agit d'un 'ou'.
    *
    * @var const
    */
    const LOGICAL_OR = 'or';

   /**
    * Constante précisant qu'il s'agit d'un 'select'.
    *
    * @var const
    */
    const SELECT ='sel';

    /**
     * Liste des opérateurs connus, utilisé pour la vérification.
     *
     * @var array
     */
    protected static $operators = array(
            self::OPERATOR_SUM,
            self::OPERATOR_PRODUCT,
            self::LOGICAL_AND,
            self::LOGICAL_OR,
            self::SELECT,
        );


    /**
     * Type d'opération :
     *  - somme / soustraction / produit / division pour un arbre numérique.
     *  - non / et / ou / ou exclusif pour un arbre logique.
     *
     * @see self::OPERATOR_SUM
     * @see self::OPERATOR_PRODUCT
     * @see self::LOGICAL_AND
     * @see self::LOGICAL_OR
     * @see self::SELECT
     *
     * @var const
     */
    protected $operator;

    /**
     * Collection contenant les noeuds enfants.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $children = null;


    /**
     * Construit l'objet et l'enregistre dans l'EntityManager.
     */
    public function __construct()
    {
        $this->children = new Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Renvoie la référence de la pool active.
     *  Il s'agit de l'entityManager correspondant.
     *
     * @return string
     */
    public static function getActivePoolName()
    {
        return TEC_Model_Component::getActivePoolName();
    }

    /**
     * Spécifi l'opérateur du noeud.
     *
     * @see self::OPERATOR_SUM
     * @see self::OPERATOR_PRODUCT
     * @see self::LOGICAL_AND
     * @see self::LOGICAL_OR
     *
     * @param const $operator
     */
    public function setOperator($operator)
    {
        if (!(in_array($operator, self::$operators))) {
            throw new Core_Exception_InvalidArgument('The Operator has to be a class Constant.');
        }
        $this->operator = $operator;
    }

    /**
     * Renvoi l'opérateur du noeud.
     *
     * @return const
     */
    public function getOperator()
    {
        if ($this->operator === null) {
            throw new Core_Exception_UndefinedAttribute('No Operator has been set yet.');
        }
        return $this->operator;
    }

    /**
     * Ajout un enfant.
     *
     * @param TEC_Model_Composite $child
     */
    public function addChild($child)
    {
        if ($child->getParent() !== $this) {
            $child->setParent($this);
        } else {
            $this->children->add($child);
        }
    }

    /**
     * Supprime un enfant.
     *
     * @param TEC_Model_Composite $child
     */
    public function removeChild($child)
    {
        if ($this->hasChild($child)) {
            if ($child->getParent() === $this) {
                throw new Core_Exception_InvalidArgument('Can\'t remove a child without first setting his new parent.');
            }
            $this->children->removeElement($child);
        }
    }

    /**
     * Indique si le component donné est un enfant de ce composite.
     *
     * @param TEC_Model_Component $child
     *
     * @return bool
     */
    public function hasChild($child)
    {
        return $this->children->contains($child);
    }

    /**
     * Indique si le composite possède au moins un enfant.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return !$this->children->isEmpty();
    }

    /**
     * Retourne un tableau contenant les instances de ses enfants
     * @return TEC_Model_Component[]
     */
    public function getChildren()
    {
        return $this->children->toArray();
    }

    /**
     * Retourne un tableau contenant toutes les feuilles
     * @return TEC_Model_Leaf[]
     */
    public function getAllLeafsRecursively()
    {
        $leafs = [];
        foreach ($this->children as $child) {
            if ($child instanceof TEC_Model_Leaf) {
                $leafs[] = $child;
            }
            if ($child instanceof TEC_Model_Composite) {
                $leafs = array_merge($leafs, $child->getAllLeafsRecursively());
            }
        }
        return $leafs;
    }

}
