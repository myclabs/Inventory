<?php
/**
 * @package Inventory
 */

use Core\Annotation\Secure;

/**
 * @package Inventory
 */
class FeedbackController extends Core_Controller_Ajax
{

    /**
     * Submit feedback form
     * @Secure("public")
     */
    public function submitAction()
    {
        $subject = "Feedback utilisateur reçu";

        $params = $this->_getAllParams();
        unset($params['module']);
        unset($params['controller']);
        unset($params['action']);
        if (isset($params['ajaxSuccesses'])) {
            $params['ajaxSuccesses'] = json_decode($params['ajaxSuccesses']);
        }
        if (isset($params['ajaxErrors'])) {
            $params['ajaxErrors'] = json_decode($params['ajaxErrors']);
        }
        $content = '';
        foreach ($params as $key => $param) {
            if (is_array($param)) {
                $content .= "$key : [" . PHP_EOL;
                foreach ($param as $itemKey => $item) {
                    $content .= "\t $itemKey : $item" . PHP_EOL;
                }
                $content .= "]" . PHP_EOL . PHP_EOL;
            } else {
                $content .= "$key : $param" . PHP_EOL . PHP_EOL;
            }
        }

        $content .= PHP_EOL . "* les listes des requêtes AJAX ne prennent pas en compte les requêtes des Datagrid";

        // Envoie un mail
        $email = new Core_Mail();
        $email->addTo("tous@myc-sense.com", "Dev");
        $email->setSubject($subject);
        $email->setBodyText($content);
        $email->send();

        $this->sendJsonResponse('');
    }

}