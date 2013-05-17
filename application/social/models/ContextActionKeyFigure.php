<?php
/**
 * @package Social
 */

/**
 * @author  joseph.rouffet
 * @author  matthieu.napoli
 * @package Social
 */
class Social_Model_ContextActionKeyFigure extends Core_Model_Entity
{

    /**
     * @var Social_Model_ActionKeyFigure
     */
    protected $actionKeyFigure;

    /**
     * @var Social_Model_ContextAction
     */
    protected $contextAction;

    /**
     * Valeur de la contextActionKeyFigure
     * @var float
     */
    protected $value;


    /**
     * @param Social_Model_ActionKeyFigure $actionKeyFigure
     * @param Social_Model_ContextAction   $contextAction
     */
    public function __construct(Social_Model_ActionKeyFigure $actionKeyFigure,
                                Social_Model_ContextAction $contextAction
    ) {
        $this->actionKeyFigure = $actionKeyFigure;
        $this->contextAction = $contextAction;
    }

    /**
     * @return Social_Model_ActionKeyFigure
     */
    public function getActionKeyFigure()
    {
        return $this->actionKeyFigure;
    }

    /**
     * @return Social_Model_ContextAction
     */
    public function getContextAction()
    {
        return $this->contextAction;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     */
    public function setValue($value)
    {
        if ($value === null) {
            $this->value = null;
        } else {
            $this->value = (float) $value;
        }
    }

    /**
     * @param Social_Model_ActionKeyFigure $actionKeyFigure
     * @param Social_Model_ContextAction   $contextAction
     * @return Social_Model_ContextActionKeyFigure
     */
    public static function loadByKey(Social_Model_ActionKeyFigure $actionKeyFigure,
                                Social_Model_ContextAction $contextAction
    ) {
        return parent::load([
                            'actionKeyFigure' => $actionKeyFigure,
                            'contextAction'   => $contextAction,
                            ]);
    }

}
