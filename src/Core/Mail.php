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
     * @throws Core_Exception_UndefinedAttribute
     * @return \Core_Mail
     */
    public function __construct()
    {
        parent::__construct('utf-8');

        $container = \Core\ContainerSingleton::getContainer();

        $this->setFrom($container->get('emails.noreply.adress'), $container->get('emails.noreply.name'));
        $this->setReplyTo($container->get('emails.noreply.adress'), $container->get('emails.noreply.name'));
    }

    /**
     * Envoi du mail.
     *
     * Pas de nécessité de modifier le moyen de transport : ceci est fait dans le bootstrap.
     *
     * @param Zend_Mail_Transport_Abstract $transport
     *
     * @return self
     */
    public function send($transport = null)
    {
        return parent::send();
    }
}
