<?php
/**
 * Fichier de la classe Tree.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Tree
 */

/**
 * Description of Controller_Tree.
 *
 * Classe générique des controleurs de la Tree.
 * La classe donne accès à des méthodes permettant de récupérer, envoyer et modifier les données.
 *
 * @package    UI
 * @subpackage Controller
 */
abstract class UI_Controller_Tree extends Core_Controller_Ajax
{
    /**
     * Identifiant du Tree.
     *
     * @var   string
     */
    public $id = null;

    /**
     * Identifiant du node concerné par la requête.
     *
     * @var   string
     */
    public $idNode = null;

    /**
     * Tableau des champs du formulaire d'ajout et d'édition.
     *
     * @var   array
     */
    protected $_form = null;

    /**
     * Tableau des messages d'erreurs des champs du formulaire d'ajout et d'édition.
     *
     * @var   array
     *
     * @see setAddElementErrorMessage
     */
    protected $_formErrorMessages = array();

    /**
     * Contient le message qui sera renvoyé.
     *
     * @var   string
     */
    public $message = null;

    /**
     * Tableau des nodes du tree.
     *
     * @var   array
     */
    public $data = array();


    /**
     * Permet de récupérer automatiquement les informations envoyées par la tree.
     */
    public function init()
    {
        parent::init();

        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();

        // Récupération de l'identifiant de la Datagrid en cour d'utilisation.
        $this->id = $this->_getParam('idTree');
        if (($this->_getParam('idNode') != '') && ($this->_getParam('idNode') !== 'Root')) {
            $this->idNode = $this->_getParam('idNode');
        }

        // Chargement des paramètres en fonction de l'action.
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $action = $request->getActionName();
        switch ($action) {

            case 'addnode':
                // Récupération des champs du formulaire.
                $this->_form = Zend_Json::decode($this->_getParam($this->id . '_addForm'), Zend_Json::TYPE_ARRAY);
                break;

            case 'editnode':
                // Récupération des champs du formulaire.
                $this->_form = Zend_Json::decode($this->_getParam($this->id . '_editForm'), Zend_Json::TYPE_ARRAY);
                $this->idNode = $this->_form[$this->id . '_element']['hiddenValues'][$this->id . '_element'];
                break;

            case 'getelements':
            case 'deletenode':
            default :
                break;

        }

    }

    /**
     * Fonction renvoyant la liste des nodes pour un node donnée.
     *
     * Récupération de l'id du node (null pour la racine).
     *  $this->idNode
     *
     * Récupération des arguments de la manière suivante :
     *  $this->_getParam('nomArgument').
     *
     * @see addNode
     */
    abstract function getnodesAction();

    /**
     * Fonction ajoutant un node.
     *
     * Renvoie un message d'information.
     *
     * @see getAddElementValue
     * @see setAddElementErrorMessage
     */
    public function addnodeAction()
    {
        throw new Core_Exception_InvalidHTTPQuery();
    }

    /**
     * Fonction modifiant un node.
     *
     * Récupération de l'id du node.
     *  $this->idNode
     *
     * Renvoie un message d'information.
     *
     * @see getEditElementValue
     * @see setEditElementErrorMessage
     */
    public function editnodeAction()
    {
        throw new Core_Exception_InvalidHTTPQuery();
    }

    /**
     * Fonction réupérant la liste des parents possible d'un node.
     *
     * Récupération de l'id du node.
     *  $this->idNode
     *
     * @see addElementList
     */
    abstract function getlistparentsAction();

    /**
     * Fonction réupérant la liste des frères possible d'un node.
     *
     * Récupération de l'id du node.
     *  $this->idNode
     * Récupération de l'id du parent.
     *  $this->idParent
     *
     * @see addElementList
     */
    abstract function getlistsiblingsAction();

    /**
     * Fonction supprimant un node.
     *
     * Récupération de l'id du node.
     *  $this->idNode
     *
     * Renvoie une message d'information.
     */
    abstract function deletenodeAction();

    /**
     * Ajoute une ligne à la réponse.
     *
     * @param string $id
     * @param string $label
     * @param bool   $isLeaf      True (par défaut) si c'est une feuille.
     * @param string $url        Url vers lequel point le node, ou, est situé le contenu du popup.
     * @param bool   $isLink     False (par défaut) si l'$url indique le contenu d'un popup et non un lien.
     * @param bool   $isExpanded False (par défaut) si le node est plié.
     * @param bool   $isEditable True (par défaut) si le node est editable.
     *
     */
    public function addNode($id, $label, $isLeaf = true, $url = null, $isLink = false, $isExpanded = false,
                            $isEditable = true
    ) {
        $data = array(
            'id'         => $id,
            'label'      => $label,
            'isLeaf'     => $isLeaf,
            'isExpanded' => $isExpanded,
            'isEditable' => $isEditable
        );
        if ($url !== null) {
            $data['url'] = $url;
            $data['directLink'] = $isLink;
        }
        $this->data[] = $data;
    }

    /**
     * Ajout un élément à la liste.
     *
     * @param string $index Index du label dans la liste.
     * @param string $label Label qui sera afficher dans la liste.
     *
     */
    public function addElementList($index, $label)
    {
        $this->data[(string)$index] = (string)$label;
    }

    /**
     * Fonction renvoyant la valeur d'ajout d'un élément donné.
     *
     * @param string $elementName
     *
     * @return mixed
     */
    public function getAddElementValue($elementName)
    {
        if (! array_key_exists($elementName, $this->_form)) {
            return null;
        }
        return $this->_form[$elementName]['value'];
    }

    /**
     * Fonction qui définit le message d'erreur d'un node du formulaire d'ajout ou d'édition.
     *
     * @param string $elementName
     * @param string $errorMessage
     */
    public function setAddFormElementErrorMessage($elementName, $errorMessage)
    {
        $this->_formErrorMessages[$elementName] = $errorMessage;
    }

    /**
     * Fonction renvoyant la valeur d'ajout d'un élément donné.
     *
     * @param string $elementName
     *
     * @return mixed
     */
    public function getEditElementValue($elementName)
    {
        return $this->_form[$this->id . '_' . $elementName]['value'];
    }

    /**
     * Fonction qui définit le message d'erreur d'un node du formulaire d'ajout ou d'édition.
     *
     * @param string $elementName
     * @param string $errorMessage
     */
    public function setEditFormElementErrorMessage($elementName, $errorMessage)
    {
        $this->_formErrorMessages[$this->id . '_' . $elementName] = $errorMessage;
    }

    /**
     * Permet d'envoyer la réponse au tree.
     *
     */
    public function send()
    {
        $response = array();

        // Définition du message.
        $response['message'] = $this->message;
        $response['type'] = "success";

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $action = $request->getActionName();
        switch ($action) {
            case 'getnodes':
                //Définition des données.
                $response['data'] = $this->data;
                break;
            case 'editnode':
            case 'addnode':
                $response['status'] = !(count($this->_formErrorMessages) > 0);
                $response['errorMessages'] = $this->_formErrorMessages;
                if (count($this->_formErrorMessages) > 0) {
                    $this->getResponse()->setHttpResponseCode(400);
                }
                break;
            case 'deletenode':
                break;
            default:
                $response = $this->data;
                break;
        }

        // Envoie des données.
        $this->sendJsonResponse($response);
    }

}
