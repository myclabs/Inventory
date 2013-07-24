<?php
/**
 * @author     valentin.claras
 * @package    TEC
 * @subpackage Component
 */

namespace TEC\Component;

use Doctrine\Common\Collections\ArrayCollection;
use Core_Exception_InvalidArgument;
use Core_Exception_UndefinedAttribute;

/**
 * @package    TEC
 * @subpackage Component
 */
class Composite extends Component
{
   /**
    * Constante précisant qu'il s'agit d'une somme.
    *
    * @var string
    */
    const OPERATOR_SUM = 'sum';

   /**
    * Constante précisant qu'il s'agit d'un produit.
    *
    * @var string
    */
    const OPERATOR_PRODUCT = 'mul';

   /**
    * Constante précisant qu'il s'agit d'un 'et'.
    *
    * @var string
    */
    const LOGICAL_AND ='and';

   /**
    * Constante précisant qu'il s'agit d'un 'ou'.
    *
    * @var string
    */
    const LOGICAL_OR = 'or';

   /**
    * Constante précisant qu'il s'agit d'un 'select'.
    *
    * @var string
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
     * @var string
     */
    protected $operator;

    /**
     * Collection contenant les noeuds enfants.
     *
     * @var ArrayCollection
     */
    protected $children = null;


    /**
     * Construit l'objet et l'enregistre dans l'EntityManager.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * Spécifi l'opérateur du noeud.
     *
     * @see self::OPERATOR_SUM
     * @see self::OPERATOR_PRODUCT
     * @see self::LOGICAL_AND
     * @see self::LOGICAL_OR
     *
     * @param string $operator
     * 
     * @throws Core_Exception_InvalidArgument
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
     * @throws Core_Exception_UndefinedAttribute
     * 
     * @return string
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
     * @param Component $child
     */
    public function addChild(Component $child)
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
     * @param Component $child
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function removeChild(Component $child)
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
     * @param Component $child
     *
     * @return bool
     */
    public function hasChild(Component $child)
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
     * Retourne un tableau contenant les instances de ses enfants.
     *
     * @return Component[]
     */
    public function getChildren()
    {
        return $this->children->toArray();
    }

    /**
     * Retourne un tableau contenant toutes les feuilles.
     *
     * @return Leaf[]
     */
    public function getAllLeafsRecursively()
    {
        $leafs = [];
        foreach ($this->children as $child) {
            if ($child instanceof Leaf) {
                $leafs[] = $child;
            }
            if ($child instanceof Composite) {
                $leafs = array_merge($leafs, $child->getAllLeafsRecursively());
            }
        }
        return $leafs;
    }

}
