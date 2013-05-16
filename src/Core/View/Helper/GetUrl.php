<?php
/**
 * @package    Core
 * @subpackage View
 */

/**
 * Aide de vue permettant de recuperer l'url demandée
 * @package    Core
 * @subpackage View
 */
class Core_View_Helper_GetUrl
{

    /**
     * methodes qui recupere l'url demandée
     *
     * L'url est composée :
     * - du nom du module
     * - du nom du controlleur
     * - du nom de l'action
     * - des paramètres
     * Elle ne contient pas baseUrl
     *
     * @return string
     */
    public function getUrl()
    {
        //on recupere le nom du module, controlleur et action
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        //on recupere les parametres
        $params = $request->getParams();
        //on construit la page
        if ($module != 'default') {
            $page = $module.'/'.$controller.'/'.$action;
        } else {
            $page = $controller.'/'.$action;
        }
        //on test qu'il n'y est pas seulement les parametres correspondant au
        //nom du module, du controlleur et de l'action
        if (count($page > 3)) {
            $compteur = 0;
            //on construit la chaine de paramètres
            foreach ($params as $key => $param) {
                $compteur++;
                if ($key != 'module' && $key != 'controller' && $key != 'action'
                    && $key != 'error_handler'
                    && $controller != 'error'
                ) {
                    //on met le signe ? seulement pour le premier parametre
                    if ($compteur == 4 ) {
                        $page .= '?';
                    }
                    $page .= $key.'='.$param;
                    //on ne met pas le signe & pour le dernier
                    if ($compteur != count($params)) {
                        $page .= '&';
                    }
                }
            }
        }
        return $page;
    }

}