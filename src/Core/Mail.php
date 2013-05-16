<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Mail
 */

/**
 * Permet d'envoyer un mail
 *
 * @package    Core
 * @subpackage Mail
 *
 * @uses Zend_Mail
 */
class Core_Mail extends Zend_Mail
{

    /**
     * Constructeur
     *
     * Définition de paramètres par défaut.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('utf-8');

        $config = Zend_Registry::get('configuration');

        if ((empty($config->emails->noreply->adress)) || (empty($config->emails->noreply->name))) {
            throw new Core_Exception_UndefinedAttribute('Le mail "no-reply" n\'est pas définit dans le application.ini');
        } else {
            $this->setFrom($config->emails->noreply->adress, $config->emails->noreply->name);
        }
    }

    /**
     * Envoi du mail.
     *
     * Pas de nécessité de modifier le moyen de transport : ceci est fait dans le bootstrap.
     *
     * @param Zend_Mail_Transport_Abstract $transport
     *
     * @return void
     */
    public function send($transport = null)
    {
        return parent::send();
    }

}
