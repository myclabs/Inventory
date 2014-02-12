<?php
/**
 * @author     matthieu.napoli
 * @package    UI
 * @subpackage Form
 */

/**
 * Helper pour les controleurs de formulaires
 * @package    UI
 * @subpackage Form
 */
trait UI_Controller_Helper_Form
{

    private $formResponse;

    /**
     * Initialisation
     */
    public function init()
    {
        parent::init();
        $this->formResponse = new stdClass();
        $this->formResponse->type = UI_Message::TYPE_SUCCESS;
    }

    /**
     * Retourne les données soumises dans un formulaire
     * @param string $ref Identifiant du formulaire
     * @return UI_Controller_Helper_FormData
     */
    public function getFormData($ref)
    {
        $param = $this->getRequest()->getParam($ref);
        if (empty($param)) {
            throw new Core_Exception_InvalidArgument("The form '$ref' was not found in the request params");
        }
        $data = json_decode($param, true);
        return new UI_Controller_Helper_FormData($data, $this->getRequest());
    }

    /**
     * Ajoute des messages d'erreur à des champs
     *
     * Si au moins un message d'erreur est ajouté, alors la requête est considérée comme échouée
     * @param array $errorMessages Tableau de messages d'erreur indexés par le ref du champ ciblé
     */
    public function addFormErrors(array $errorMessages)
    {
        foreach ($errorMessages as $field => $message) {
            $this->addFormError($field, $message);
        }
    }

    /**
     * Ajoute un message d'erreur à un champ
     *
     * Si au moins un message d'erreur est ajouté, alors la requête est considérée comme échouée
     * @param string $field
     * @param string $message
     */
    public function addFormError($field, $message)
    {
        if (! isset($this->formResponse->errorMessages)) {
            $this->formResponse->errorMessages = [];
        }
        $this->formResponse->type = UI_Message::TYPE_WARNING;
        $this->formResponse->errorMessages[$field] = $message;
    }

    /**
     * @return boolean True si des messages d'erreur ont été ajoutés
     */
    public function hasFormError()
    {
        return ($this->formResponse->type != UI_Message::TYPE_SUCCESS);
    }

    /**
     * Définit le message renvoyé
     * @param string $message
     * @param string $type
     * @see UI_Message::TYPE_SUCCESS
     * @see UI_Message::TYPE_INFO
     * @see UI_Message::TYPE_WARNING
     * @see UI_Message::TYPE_ERROR
     */
    public function setFormMessage($message, $type = null)
    {
        if ($type !== null) {
            $this->formResponse->type = $type;
        }
        $this->formResponse->message = $message;
    }

    /**
     * Envoie la réponse AJAX pour le formulaire
     * @param mixed $data Données supplémentaires à retourner
     */
    public function sendFormResponse($data = null)
    {
        if ($data) {
            $this->formResponse->data = $data;
        }
        if ($this->hasFormError()) {
            $this->getResponse()->setHttpResponseCode(400);
        }
        $this->sendJsonResponse($this->formResponse);
    }

    /**
     * Return the Request object
     * @return Zend_Controller_Request_Http
     */
    abstract public function getRequest();

    /**
     * Return the Response object
     * @return Zend_Controller_Response_Abstract
     */
    abstract public function getResponse();

    /**
     * Envoie une réponse ajax encodée en Json
     * @param mixed $response
     */
    abstract public function sendJsonResponse($response);

}
