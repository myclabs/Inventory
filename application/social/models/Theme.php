<?php
/**
 * @package Social
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author  joseph.rouffet
 * @package Social
 */
class Social_Model_Theme extends Core_Model_Entity
{

    const QUERY_LABEL = 'label';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;


    /**
     * @param string $label
     */
    public function __construct($label = null)
    {
        $this->setLabel($label);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;
    }

    /**
     * Retourne le nombre de modèles d'actions qui ont ce thème
     * @return int
     */
    public function getGenericActionCount()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Social_Model_GenericAction::QUERY_THEME, $this);
        return Social_Model_GenericAction::countTotal($query);
    }

}
