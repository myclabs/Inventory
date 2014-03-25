<?php
/**
 * Fichier de la classe Message.
 *
 * @author     valentin.claras
 * @package    UI
 * @subpackage Message
 */

/**
 * Description of Message.
 *
 * Une classe permettant de stocker des messages qui seront affichés à l'utilisateur.
 *
 * @package    UI
 * @subpackage Message
 */
class UI_Message
{
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';

    /**
     * Tableau où seront stockés les messages.
     *
     * @var array(string)
     */
    protected $_messages = array();


    /**
     * Constructeur protégé.
     */
    private function __construct()
    {
    }

    /**
     * Récupère l'instance existante de UI_Message ou la créer.
     * @return UI_Message
     */
    public static function getInstance()
    {
        // Récupère la session concernant les messages.
        $sessionName = \Core\ContainerSingleton::getContainer()->get('session.storage.name');
        $zendSessionMessage = new Zend_Session_Namespace($sessionName . '_');
        if ((!(isset($zendSessionMessage->messages))) || (!($zendSessionMessage->messages instanceof UI_Message))) {
            // Si messages n'existe pas alors il est crée.
            $zendSessionMessage->messages = new UI_Message();
        }

        // Renvoi l'instance de la classe UI_Message.
        return $zendSessionMessage->messages;
    }

    /**
     * Fonction static que permet d'ajouter un message sans instancier UI_Message.
     *
     * @param string $texte Texte qui sera affiché à l'utilisateur.
     * @param const  $type Type de message affiché, constante de la classe.
     *
     * @return void
     */
    public static function addMessageStatic($texte, $type=self::TYPE_WARNING)
    {
        self::getInstance()->addMessage($texte, $type);
    }

    /**
     * Fonction que permet d'ajouter un message.
     *
     * @param string $texte Texte qui sera affiché à l'utilisateur.
     * @param const  $type Type de message affiché, constante de la classe.
     *
     * @return void
     */
    public function addMessage($texte, $type=self::TYPE_WARNING)
    {
        switch ($type) {
            case self::TYPE_WARNING:
                $class = 'alert alert-warning';
                $title = __('UI', 'message', 'titleWarning');
                break;
            case self::TYPE_ERROR:
                $class = 'alert alert-danger';
                $title = __('UI', 'message', 'titleError');
                break;
            case self::TYPE_INFO:
                $class = 'alert alert-info';
                $title = __('UI', 'message', 'titleInfo');
                break;
            case self::TYPE_SUCCESS:
                $class = 'alert alert-success';
                $title = __('UI', 'message', 'titleSuccess');
                break;
            default:
                throw new Core_Exception_InvalidArgument('The message type must be a class constant !');
                break;
        }
        $this->_messages[] = array(
                                'text'  => $texte,
                                'class' => $class,
                                'title' => $title,
                            );
    }

    /**
     * Méthode static affichant les messages sans instancier UI_Message.
     *
     * @param bool $display Affiche:true ou renvoie:false le texte html.
     *
     * @return mixed (void|string) chaine html de l'image.
     */
    public static function renderStatic($display=true)
    {
        return self::getInstance()->render($display);
    }

    /**
     * Méthode affichant les messages.
     *
     * @param bool $display Affiche:true ou renvoie:false le texte html.
     *
     * @return mixed (void|string) chaine html de l'image.
     */
    public function render($display=true)
    {
        $render = '';

        foreach ($this->_messages as $message) {
            $render .= '
            <div class="'.$message['class'].' fade in">';

            $render .= '
                <button class="close" data-dismiss="alert">×</button>';

            $render .= '
                <strong>'.$message['title'].'</strong> '.$message['text'];

            $render .= '
            </div>';
        }

        $this->_messages = array();

        if ($display === true) {
            // Affichage des messages.
            echo $render;
            return true;
        } else {
            // Retour du code des messages.
            return $render;
        }
    }

    /**
     * Renvoi le type de message en fonction d'un code HTTP.
     * @param string $code
     */
    public static function getTypeByHTTPCode($code)
    {
        $code = (string) $code;
        switch ($code[0]) {
            case '5':
                return UI_Message::TYPE_ERROR;
                break;
            case '4':
                return UI_Message::TYPE_WARNING;
                break;
            case '2':
                return UI_Message::TYPE_SUCCESS;
                break;
            default:
                return UI_Message::TYPE_INFO;
                break;
        }
    }

}
