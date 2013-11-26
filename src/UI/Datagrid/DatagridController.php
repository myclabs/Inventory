<?php

namespace UI\Datagrid;

use Core_Controller;
use Doctrine\Common\Collections\Criteria;
use Zend_Registry;
use Zend_Session_Namespace;
use Zend_Controller_Front;
use Zend_Json;
use Core_Locale;
use Core_Date;
use DateTime;
use Core_Exception_InvalidHTTPQuery;

/**
 * Classe générique des controleurs de datagrid.
 *
 * La classe donne accès à des méthodes permettant de récupérer, envoyer et modifier le contenu du datagrid.
 *
 * @author valentin.claras
 */
abstract class DatagridController extends Core_Controller
{
    /**
     * Identifiant du datagrid.
     *
     * @var string
     */
    public $id;

    /**
     * Objet Requête.
     *
     * @var Criteria
     */
    public $criteria;

    /**
     * Tableau des champs du formulaire d'ajout.
     *
     * @var array
     *
     * @see getAddElementValue
     */
    protected $_add;

    /**
     * Tableau des messages d'erreurs des champs du formulaire d'ajout.
     *
     * @var array
     *
     * @see setAddElementErrorMessage
     */
    protected $_addErrorMessages = [];

    /**
     * Identifiant de la ligne à supprimer.
     *
     * @var   string
     */
    public $delete;

    /**
     * Tableau des paramètres nécéssaires à la mise à jour.
     *
     * index.
     * column.
     * value.
     *
     * @var array
     */
    public $update;

    /**
     * Contient le message qui sera renvoyé.
     *
     * @var string
     */
    public $message;

    /**
     * Tableau des données du datagrid.
     *
     * @var array
     */
    public $data = [];

    /**
     * Nombre total d'éléments du datagrid.
     *
     * @var int
     */
    public $totalElements;


    /**
     * Permet de récupérer automatiquement les informations envoyées par la datagrid.
     */
    public function init()
    {
        parent::init();

        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();

        // Récupération de l'identifiant de la Datagrid en cour d'utilisation.
        $this->id = $this->getParam('idDatagrid');

        // Chargement des paramètres en fonction de l'action.
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $action = $request->getActionName();
        switch ($action) {

            case 'getelements':
                // Préparation de la sauvegarde en session des paramètres d'affichage utilisateur.
                $configuration = Zend_Registry::get('configuration');
                $sessionName = $configuration->sessionStorage->name.'_'.APPLICATION_ENV;
                $zendSessionDatagrid = new Zend_Session_Namespace($sessionName);
                $idDatagrid = 'datagrid'.$this->id;
                if ((!(isset($zendSessionDatagrid->$idDatagrid))) || (!(is_array($zendSessionDatagrid->$idDatagrid)))) {
                    $zendSessionDatagrid->$idDatagrid = array();
                }
                $datagridSession = &$zendSessionDatagrid->$idDatagrid;

                // Création d'un objet Requête.
                $criteriaName = str_replace('|', '\\', $this->getParam('criteriaName'));
                /** @var Criteria criteria */
                $this->criteria = new $criteriaName();

                // Récupération du nombre d'éléments à renvoyer.
                if (($this->getParam('nbElements') !== 'null') && ($this->getParam('nbElements') !== 'false')) {
                    $this->criteria->setMaxResults((int) $this->getParam('nbElements'));
                    // Sauvegarde du nombre d'élément de la pagination.
                    $datagridSession['nbElements'] = (int) $this->getParam('nbElements');
                    // Récupération de l'élément de départ.
                    $this->criteria->setFirstResult((int) $this->getParam('startIndex'));
                    // Sauvegarde de la page de départ.
                    $datagridSession['startIndex'] = (int) $this->getParam('startIndex');
                } else {
                    // Sauvegarde de l'abscence de pagination.
                    $datagridSession['nbElements'] = null;
                    // Sauvegarde de la page de départ.
                    $datagridSession['startIndex'] = null;
                }

                /* Filtre */
                // Récupération du filtre.
                $filters = Zend_Json::decode($this->getParam('filters'), Zend_Json::TYPE_ARRAY);
                foreach ($filters as $attributeName => $filterValue) {
                    foreach ($filterValue as $criteriaOperator => $value) {
                        if ($value !== null) {
                            $this->criteria->$attributeName->$criteriaOperator($value);
                        }
                    }
                }
                // Sauvegarde du filtre.
                $datagridSession['filters'] = $filters;

                /* Tri */
                // Définition de la colonne de tri et sauvegarde du tri.
                if ($this->getParam('sortColumn') !== 'null') {
                    $sortName = $this->getParam('sortColumn');
                    $datagridSession['sortColumn'] = $sortName;
                    // Récupération de la direction du tri.
                    if ($this->getParam('sortDirection') === 'true') {
                        $this->criteria->orderBy([$this->criteria->$sortName->getField() => Criteria::ASC]);
                        $datagridSession['sortDirection'] = true;
                    } else {
                        $this->criteria->orderBy([$this->criteria->$sortName->getField() => Criteria::DESC]);
                        $datagridSession['sortDirection'] = false;
                    }
                } else {
                    $datagridSession['sortColumn'] = null;
                }
                break;

            case 'addelement':
                // Récupération des champs du formulaire.
                $this->_add = Zend_Json::decode($this->getParam($this->id.'_addForm'), Zend_Json::TYPE_ARRAY);
                break;

            case 'deleteelement':
                // Récupération de l'index de l'élément à supprimer.
                $this->delete = $this->getParam('index');
                break;

            case 'updateelement':
                $this->update = array();
                // Récupération de l'index de l'élément à mettre à jour.
                $this->update['index'] = $this->getParam('index');
                // Récupération de l'attribut de l'élément à mettre à jour.
                $this->update['column'] = $this->getParam('column');
                // Récupération de la nouvelle valeur de l'élément à mettre à jour.
                $this->update['value'] = str_replace('<br>', PHP_EOL, $this->getParam('value'));
                break;

            default:
                break;
        }
    }

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * Récupération des paramètres de tris et filtres de la manière suivante :
     *  $this->request.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie la liste d'éléments, le nombre total et un message optionnel.
     */
    abstract public function getelementsAction();

    /**
     * Fonction ajoutant un élément.
     *
     * Renvoie un message d'information.
     *
     * @see getAddElementValue
     * @see setAddElementErrorMessage
     */
    public function addelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery();
    }

    /**
     * Fonction supprimant un élément.
     *
     * Récupération de la ligne à supprimer de la manière suivante :
     *  $this->delete.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie un message d'information.
     */
    public function deleteelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery();
    }

    /**
     * Fonction modifiant un élément.
     *
     * Récupération de la ligne à modifier de la manière suivante :
     *  $this->update['index'].
     *
     * Récupération de la colonne à modifier de la manière suivante :
     *  $this->update['column'].
     *
     * Récupération de la nouvelle valeur à modifier de la manière suivante :
     *  $this->update['value'].
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie un message d'information et la nouvelle donnée à afficher dans la cellule.
     *
     */
    public function updateelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery();
    }

    /**
     * Formattage standard pour une céllule.
     *
     * @param string $value
     * @param string $content
     * @param bool   $editable
     *
     * @return array
     */
    protected function baseCell($value, $content = null, $editable = true)
    {
        $cell = array(
                'value'    => $value,
                'content'  => $content,
                'editable' => $editable,
            );
        return $cell;
    }

    /**
     * Définit si il sera possible d'éditer une cellule.
     *
     * @param  array &$cell       Cellule à définir.
     * @param  bool  $possibility Autorise, ou non, l'édition dans la cellule donnéee.
     */
    public function editableCell(&$cell, $possibility = true)
    {
        if (is_array($cell) === false) {
            $cell = $this->baseCell($cell, null, $possibility);
        }
        $cell['editable'] = $possibility;
    }

    /**
     * Formate les données à renvoyer pour une cellule date.
     *
     * @param DateTime $date Valeur d'une colonne date.
     * @param string   $content Affichage dans la colonne.
     *
     * @return array
     */
    public function cellDate(DateTime $date, $content = null)
    {
        //@todo Date -> Datagrid::cellDate -> afficher la date au format de l'utilisateur.
        if ($date instanceof Core_Date) {
            $value = $date->formatDate('dd/MM/Y');
        } else {
            $value = $date->format('d/m/Y');
        }
        if ($content === null) {
            $content = $value;
        }
        return $this->baseCell($value, $content);
    }

    /**
     * Formate les données à renvoyer pour une cellule lien.
     *
     * Possibilité d'inverser les arguments 2 et 3 (texte/image) pour inverser l'ordre.
     *
     * @param string $url   Lien vers lequel pointra la cellule.
     * @param string $text Texte affiché dans la cellule.
     * @param string $icon Icône (twitter bootstrap) qui sera affichée à gauche du texte.
     *
     * @return array
     */
    public function cellLink($url, $text = null, $icon = null)
    {
        if (($text !== null) || ($icon !== null)) {
            $content = '';
            if ($icon !== null) {
                $content .= '<i class="icon-'.$icon.'"></i>';
            }
            if (($text !== null) && ($icon !== null)) {
                $content .= ' ';
            }
            if ($text !== null) {
                $content .= $text;
            }
        } else {
            $content = null;
        }
        return $this->baseCell($url, $content);
    }

    /**
     * Formate les données à renvoyer pour une cellule liste.
     *
     * @param mixed(int|string) $index     Index de l'élément de la liste à afficher dans la cellule.
     * @param string            $content Valeur à afficher dans la cellule (utilisé pour un gain de temps)
     *
     * @return array
     */
    public function cellList($index, $content = null)
    {
        if (is_array($index)) {
            $value = array();
            foreach ($index as $id) {
                $value[] = (string) $id;
            }
        } else {
            $value = (string) $index;
        }
        return $this->baseCell($value, $content);
    }

    /**
     * Formate les données à renvoyer pour une cellule nombre.
     *
     * @param int $number             Nombre à afficher dans la cellule.
     * @param int $significantFigures Indique le nombre de chiffres significatifs
     * @param int $numberDecimal      Indique le nombre de décimal (incompatible avec les $chiffresSignificatifs).
     *
     * @return array
     */
    public function cellNumber($number, $significantFigures = null, $numberDecimal = null)
    {
        $locale = Core_Locale::loadDefault();
        $content = $locale->formatNumber($number, $significantFigures, $numberDecimal);
        $number = $locale->formatNumberForInput($number);
        return $this->baseCell($number, $content);
    }

    /**
     * Formate les données à renvoyer pour une cellule nombre.
     *
     * @param int $percent Pourcentage à afficher dans la progressBar.
     * @param int $color   Couleur de la progressBar ("info", ["success", "warning", "danger"])
     *
     * @return array
     */
    public function cellPercent($percent, $color = null)
    {
        if ($percent === false) {
            $percent = 0;
        } elseif ($percent === true) {
            $percent = 100;
        } elseif (!(is_int($percent))) {
            $percent = (int) $percent;
        }
        if ($percent < 0) {
            $percent = abs($percent);
        }
        return $this->baseCell($percent, $color);
    }

    /**
     * Formate les données à renvoyer pour une cellule nombre.
     *
     * @param int        $number  Nombre de la cellule.
     * @param int|string $content Nombre à afficher dans la cellule.
     *
     * @return array
     */
    public function cellCustomNumber($number, $content = null)
    {
        return $this->baseCell($number, (string) $content);
    }

    /**
     * Formate les données à renvoyer pour une cellule texte long.
     *
     * @param string $urlPopup   Lien a partir duquel sera chargé le contenu du popup de description.
     * @param string $urlRawText Lien à partir duquel sera chargé le texte brute.
     * @param string $text       Texte affiché dans la cellule.
     * @param string $icon       Icône (twitter bootstrapà qui sera affichée à gauche du texte.
     *
     * @return array
     */
    public function cellLongText($urlPopup, $urlRawText = null, $text = null, $icon = null)
    {
        $urls = [
            'desc' => $urlPopup,
            'brut' => $urlRawText,
        ];
        return $this->cellLink($urls, $text, $icon);
    }

    /**
     * Formate les données à renvoyer pour une cellule popup.
     *
     * Possibilité d'inverser les arguments 2 et 3 (texte/image) pour inverser l'ordre.
     *
     * @param string $urlPopup Lien a partir duquel sera chargé le contenu du popup.
     * @param string $text     Texte affiché dans la cellule.
     * @param string $icon     Image qui sera affiché à gauche du texte.
     *
     * @return array
     */
    public function cellPopup($urlPopup, $text = null, $icon = null)
    {
        return $this->cellLink($urlPopup, $text, $icon);
    }

    /**
     * Formate les données à renvoyer pour une cellule position.
     *
     * @param int  $position    Position de l'élément.
     * @param bool $canMoveUp   False si il s'agit du premier élément.
     * @param bool $canMoveDown False si il s'agit du dernier élément.
     *
     * @return array
     */
    public function cellPosition($position, $canMoveUp = true, $canMoveDown = true)
    {
        $baseCell = $this->baseCell((string) $position);
        $baseCell['up'] = $canMoveUp;
        $baseCell['down'] = $canMoveDown;
        return $baseCell;
    }

    /**
     * Formate les données à renvoyer pour une cellule texte.
     *
     * @param string $value   Texte source de la cellule.
     * @param string $content Texte affiché dans la cellule
     *
     * @return array
     */
    public function cellText($value, $content = null)
    {
        if ($content !== null) {
            $content = (string) $content;
        }
        return $this->baseCell((string) $value, $content);
    }

    /**
     * Ajoute une ligne à la réponse.
     *
     * @param array $ligne Ligne de la Datagrid à ajouter aux données.
     */
    public function addLine($ligne)
    {
        $this->data[] = $ligne;
    }

    /**
     * Ajout un élément à la liste.
     *
     * @param string $index Index du label dans la liste.
     * @param string $label Label qui sera afficher dans la liste.
     */
    public function addElementList($index, $label)
    {
        $this->data[(string) $index] = (string) $label;
    }

    /**
     * Ajout un élément à la liste issue d'un élément Autocomplete.
     *
     * @param string $index Index du label dans la liste.
     * @param string $label Label qui sera afficher dans la liste.
     */
    public function addElementAutocompleteList($index, $label)
    {
        $this->data[] = array('id' => (string) $index, 'text' => (string) $label);
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
        return $this->_add[$this->id.'_'.$elementName.'_addForm']['value'];
    }

    /**
     * Fonction qui définit le message d'erreur d'un élément du formulaire d'ajout.
     *
     * @param string $elementName
     * @param string $errorMessage
     */
    public function setAddElementErrorMessage($elementName, $errorMessage)
    {
        $this->_addErrorMessages[$this->id.'_'.$elementName.'_addForm'] = $errorMessage;
    }

    /**
     * Permet d'envoyer la réponse au datagrid.
     */
    public function send()
    {
        $response = [];

        // Définition du message.
        $response['message'] = $this->message;
        $response['type'] = "success";

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $actionName = $request->getActionName();
        switch ($actionName) {
            case 'getelements':
                $response['totalElements'] = $this->totalElements;
            case 'updateelement':
                $response['data'] = $this->data;
                break;
            case 'addelement':
                $response['status'] = !(count($this->_addErrorMessages) > 0);
                $response['errorMessages'] = $this->_addErrorMessages;
                if (count($this->_addErrorMessages) > 0) {
                    $this->getResponse()->setHttpResponseCode(400);
                }
                break;
            case 'deleteelement':
                break;
            default:
                $response = $this->data;
                break;
        }

        // Envoie des données.
        $this->sendJsonResponse($response);
    }
}
