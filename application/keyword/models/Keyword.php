<?php
/**
 * @author valentin.claras
 * @author bertrand.ferry
 * @author maxime.fourt
 * @package    Keyword
 * @subpackage Model
 */

/**
 * Classe metier de Keyword.
 * @package    Keyword
 * @subpackage Model
 */
class Keyword_Model_Keyword extends Core_Model_Entity
{
    // Constantes de tri et filtres.
    const QUERY_ID = 'id';
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';

    /**
     * Identifiant unique du Keyword.
     *
     * @var int
     */
    protected $id;

    /**
     * Identifiant textuel du Keyword.
     *
     * @var string
     */
    protected $ref = null;

    /**
     * Label du Keyword.
     *
     * @var string
     */
    protected $label;

    /**
     * Collection des associations avec les autres Keyword en tant qu'objet.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $objectAssociation;

    /**
     * Collection des associations avec les autres Keyword en tant que subjet.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $subjectAssociation;


    /**
     * Constructeur.
     */
    public function __construct()
    {
        $this->objectAssociation = new Doctrine\Common\Collections\ArrayCollection();
        $this->subjectAssociation = new Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Retourne le mot-cle correspondant a la reference.
     *
     * @param string $ref
     *
     * @return Keyword_Model_Keyword
     */
    public static function loadByRef($ref)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref));
    }

    /**
     * Charge la liste des Keyword ne possédant pas d'association en tant qu'objet.
     *
     * @param Core_Model_Query $queryParameters
     *
     * @return Keyword_Model_Keyword[]
     */
    public static function loadListRoots($queryParameters=null)
    {
        if ($queryParameters == null) {
            $queryParameters = new Core_Model_Query();
            $queryParameters->order->addOrder(self::QUERY_LABEL);
        }

        return self::getEntityRepository()->loadListRoots($queryParameters);
    }

    /**
     * Charge la liste des Keyword répondant à la requête donnée.
     *
     * @param string $expressionQuery
     *
     * @return Keyword_Model_Keyword[]
     */
    public static function loadListMatchingQuery($expressionQuery)
    {
        return self::getEntityRepository()->loadListMatchingQuery($expressionQuery);
    }

    /**
     * Modifie la reference du Keyword.
     *
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Renvoi la référence du Keyword.
     *
     * @return string
     */
    public function getRef()
    {
        if ($this->ref === null) {
            throw new Core_Exception_UndefinedAttribute('The keyword reference has not been defined yet.');
        }
        return $this->ref;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if ($this->ref === null) {
            throw new Core_Exception_UndefinedAttribute('The keyword label has not been defined yet.');
        }
        return $this->label;
    }

    /**
     * @return string
     */
    public function getCapitalizedLabel()
    {
        return ucfirst($this->getLabel());
    }

    /**
     * Ajoute une Association où le Keyword agit en tant qu'object.
     *
     * @param Keyword_Model_Association $association
     */
    public function addAssociationAsObject(Keyword_Model_Association $association)
    {
        if (!($this->hasAssociationAsObject($association))) {
            $this->objectAssociation->add($association);
            $association->setObject($this);
        }
    }

    /**
     * Vérifie sur le Keyword possède l'association donnée en tant qu'objet.
     *
     * @param Keyword_Model_Association $association
     *
     * @return bool
     */
    public function hasAssociationAsObject(Keyword_Model_Association $association)
    {
        return $this->objectAssociation->contains($association);
    }


    /**
     * Vérifie si le Keyword possède au moins une association en tant qu'objet.
     *
     * @return bool
     */
    public function hasAssociationsAsObject()
    {
        return !$this->objectAssociation->isEmpty();
    }

    /**
     * Compte le nombre de relation possédant ce Keyword en tant qu'objet.
     *
     * @return int
     */
    public function countAssociationsAsObject()
    {
        $queryFilterThisAsAsubject = new Core_Model_Query();
        $queryFilterThisAsAsubject->filter->addCondition(Keyword_Model_Association::QUERY_OBJECT, $this);
        return Keyword_Model_Association::countTotal($queryFilterThisAsAsubject);
    }

    /**
     * Renvoi le tableau des associations du Keyword en tant qu'objet.
     *
     * @return array
     */
    public function getAssociationsAsObject()
    {
        return $this->objectAssociation->toArray();
    }

    /**
     * Ajoute une Association où le Keyword agit en tant que sujet.
     *
     * @param Keyword_Model_Association $association
     */
    public function addAssociationAsSubject(Keyword_Model_Association $association)
    {
        if (!($this->hasAssociationsAsSubject($association))) {
            $this->subjectAssociation->add($association);
            $association->setSubject($this);
        }
    }

    /**
     * Vérifie sur le Keyword possède l'association donnée en tant que sujet.
     *
     * @param Keyword_Model_Association $association
     *
     * @return bool
     */
    public function hasAssociationAsSubject(Keyword_Model_Association $association)
    {
        return $this->subjectAssociation->contains($association);
    }


    /**
     * Vérifie si le Keyword possède au moins une association en tant que sujet.
     *
     * @return bool
     */
    public function hasAssociationsAsSubject()
    {
        return !$this->subjectAssociation->isEmpty();
    }

    /**
     * Compte le nombre de relation possédant ce Keyword en tant que sujet.
     *
     * @return int
     */
    public function countAssociationsAsSubject()
    {
        $queryFilterThisAsAsubject = new Core_Model_Query();
        $queryFilterThisAsAsubject->filter->addCondition(Keyword_Model_Association::QUERY_SUBJECT, $this);
        return Keyword_Model_Association::countTotal($queryFilterThisAsAsubject);
    }

    /**
     * Renvoi le tableau des associations du Keyword en tant que sujet.
     *
     * @return array
     */
    public function getAssociationsAsSubject()
    {
        return $this->subjectAssociation->toArray();
    }

    /**
     * Compte le nombre de relation possédant ce Keyword en tant qu'objet ou sujet.
     *
     * @return int
     */
    public function countAssociations()
    {
        return $this->countAssociationsAsObject() + $this->countAssociationsAsSubject();
    }

    /**
     * Retourne l'alias de la classe quand elle agit en tant que Subject dans un Association.
     *
     * @return string
     */
    public static function getAliasAsSubject()
    {
        return self::getAlias().'_AsS';
    }

    /**
     * Retourne l'alias de la classe quand elle agit en tant que Subject dans un Association.
     *
     * @return string
     */
    public static function getAliasAsObject()
    {
        return self::getAlias().'_AsO';
    }

}
