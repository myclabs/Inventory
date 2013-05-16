<?php
/**
 * @author hugo.charbonnier
 * @package TEC
 */

use Core\Annotation\Secure;

/**
 * Index controller.
 * @package TEC
 */
class TEC_ExpressionController extends Core_Controller
{
   /**
    * Appel de la vue index.
    * @Secure("viewTEC")
    */
   public function indexAction()
   {
        $this->_redirect("tec/expression/test");
   }

   /**
    * (non-PHPdoc)
    * @see Core_Controller::init()
    * @Secure("viewTEC")
    */
   public function init()
   {
        $this->view->typeExpression = ($this->_hasParam('typeExpression')) ? $this->_getParam('typeExpression') : 'numeric';
        $this->view->input = $this->_getParam('input');
   }

   /**
    * Appel de la vue acceuil.
    * @Secure("viewTEC")
    */
    public function testAction()
    {
    }

    /**
     * Interpete une expression.
     * @Secure("viewTEC")
     */
    public function readAction()
    {
        $this->view->displayResult = true;

        if (!(in_array($this->view->typeExpression, array('numeric', 'logical', 'select')))) {
            UI_Message::addMessageStatic(__('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            $this->view->typeExpression = $this->view->typeExpression;
            $expression = new TEC_Model_Expression();
            $expression->setType($this->view->typeExpression);
            $expression->setExpression($this->view->input);

            try {
                $expression->check();
                $this->view->correctedExpression = $expression->getTreeAsString();
                $this->view->graphExpression = $expression->getGraph();
            } catch (TEC_Model_InvalidExpressionException $e) {
                $this->view->errors = $e->getErrors();
            }
        }

        $this->_forward('test');
    }

}