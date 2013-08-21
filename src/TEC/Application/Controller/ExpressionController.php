<?php
/**
 * @author hugo.charbonnier
 * @package TEC
 */

use Core\Annotation\Secure;
use TEC\Exception\InvalidExpressionException;
use TEC\Expression;

/**
 * Index controller.
 * @package TEC
 */
class Tec_ExpressionController extends Core_Controller
{
   /**
    * Appel de la vue index.
    * @Secure("viewTEC")
    */
   public function indexAction()
   {
        $this->redirect("tec/expression/test");
   }

   /**
    * (non-PHPdoc)
    * @see Core_Controller::init()
    * @Secure("viewTEC")
    */
   public function init()
   {
        $this->view->typeExpression = ($this->hasParam('typeExpression')) ? $this->getParam('typeExpression') : 'numeric';
        $this->view->input = $this->getParam('input');
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
            $expression = new Expression($this->view->input, $this->view->typeExpression);

            try {
                $expression->check();
                $this->view->correctedExpression = $expression->getAsString();
                $this->view->graphExpression = $expression->getGraph();
            } catch (InvalidExpressionException $e) {
                $this->view->errors = $e->getErrors();
            }
        }

        $this->forward('test');
    }

}