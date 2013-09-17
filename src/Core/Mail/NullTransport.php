<?php

namespace Core\Mail;

use Zend_Mail_Transport_Abstract;

/**
 * Classe de transport de mail pour le debug : n'envoie le mail nulle part
 *
 * @package    Core
 * @subpackage Mail
 * @uses       Zend_Mail_Transport_Abstract
 */
class NullTransport extends Zend_Mail_Transport_Abstract
{

    /**
     * Envoi du mail
     */
    public function _sendMail()
    {
        // Ne fait rien
    }

}
