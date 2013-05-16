<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Test
 */

/**
 * Test de l'envoi de mail
 * @package    Core
 * @subpackage Test
 */
class Core_Test_MailTest extends PHPUnit_Framework_TestCase
{
    /**
     * Vérification qu'aucune exception n'est générée
     */
    function testSend()
    {
        $mail = new Core_Mail();
        $mail->setBodyText('Ceci est le texte du message.');
        $mail->addTo('somebody@example.com', 'Destinataire');
        $mail->setSubject('Sujet de test');
        $mail->send();
    }

}
