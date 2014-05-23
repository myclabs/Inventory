<?php

use Core\Annotation\Secure;
use TEC\Exception\InvalidExpressionException;
use TEC\Expression;

/**
 * @author hugo.charbonnier
 */
class Tec_ExpressionController extends Core_Controller
{
   public function init()
   {
        $this->view->typeExpression = ($this->hasParam('typeExpression'))
            ? $this->getParam('typeExpression')
            : 'numeric';
        $this->view->input = $this->getParam('input');
   }

   /**
    * @Secure("public")
    */
    public function testAction()
    {
    }

    /**
     * @Secure("public")
     */
    public function readAction()
    {
        if (!$this->hasParam('typeExpression') && is_null($this->view->input)) {
            $this->redirect('tec/expression/test');
        }

        $this->view->displayResult = true;

        if (!(in_array($this->view->typeExpression, ['numeric', 'logical', 'select']))) {
            UI_Message::addMessageStatic(__('UI', 'formValidation', 'emptyRequiredField'));
        } else {
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
