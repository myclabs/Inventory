<?php
/**
 * @author     matthieu.napoli
 * @package    Social
 * @subpackage Service
 */

/**
 * @package    Social
 * @subpackage Service
 */
class Social_Service_Message extends Core_Singleton
{

    /**
     * Envoie un nouveau message
     *
     * @param User_Model_User $author
     * @param array           $recipients
     * @param string          $title
     * @param string          $text
     *
     * @return Social_Model_Message
     */
    public function sendNewMessage(User_Model_User $author, array $recipients, $title, $text)
    {
        /** @var $userService User_Service_User */
        $userService = User_Service_User::getInstance();

        // Création du message
        $message = new Social_Model_Message($author, $title);
        $message->setText($text);
        foreach ($recipients as $recipient) {
            if ($recipient instanceof User_Model_User) {
                $message->addUserRecipient($recipient);
            } elseif ($recipient instanceof Social_Model_UserGroup) {
                $message->addGroupRecipient($recipient);
            }
        }
        $message->send();
        $message->save();

        // Envoi de l'email de notification
        $config = Zend_Registry::get('configuration');

        $url = 'http://' . $_SERVER['SERVER_NAME']
            . Zend_Controller_Front::getInstance()->getBaseUrl()
            . '/social/message/list';

        $content = __('Social', 'email', 'mailWasSendBy',
                      array(
                           'APPLICATION_NAME' => $config->emails->noreply->name,
                           'SENDER'           => $author->getName(),
                      ))
            . PHP_EOL . '--' . PHP_EOL
            . __('Social', 'message', 'object') . __('UI', 'other', ':') . $message->getTitle()
            . PHP_EOL . '--' . PHP_EOL
            . $message->getText()
            . PHP_EOL . '--' . PHP_EOL . PHP_EOL
            . $url;

        foreach ($recipients as $recipient) {

            if ($recipient instanceof User_Model_User) {
                $subject = $message->getTitle();
                $userService->sendEmail($recipient, $subject, $content);

            } elseif ($recipient instanceof Social_Model_UserGroup) {
                $subject = '[' . $recipient->getLabel() . '] ' . $message->getTitle();
                foreach ($recipient->getUsers() as $user) {
                    $userService->sendEmail($user, $subject, $content);
                }
            }
        }

        return $message;
    }

    /**
     * Retourne la liste des messages reçus pour un utilisateur
     *
     * @param User_Model_User $user
     * @param int             $count Nombre maximum de messages à retourner
     *
     * @return Social_Model_Message[]
     */
    public function getUserInbox(User_Model_User $user, $count)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $entityManagers['default'];

        $query = $em->createQuery("SELECT m FROM Social_Model_Message m
            LEFT JOIN m.userRecipients user
            LEFT JOIN m.groupRecipients userGroup
            WHERE m.sent = true
            AND (user.id = :userId
                OR userGroup.id IN (SELECT ug.id FROM Social_Model_UserGroup ug JOIN ug.users u WHERE u.id = :userId))
            ORDER BY m.creationDate DESC");
        $query->setMaxResults($count);

        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    /**
     * Retourne le nombre de messages reçus pour un utilisateur
     *
     * @param User_Model_User $user
     *
     * @return int Nombre de messages dans la boite de réception
     */
    public function getUserInboxSize(User_Model_User $user)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $entityManagers['default'];

        $query = $em->createQuery("SELECT COUNT(m) FROM Social_Model_Message m
            LEFT JOIN m.userRecipients user
            LEFT JOIN m.groupRecipients userGroup
            WHERE m.sent = true
            AND (user.id = :userId
                OR userGroup.id IN (SELECT ug.id FROM Social_Model_UserGroup ug JOIN ug.users u WHERE u.id = :userId))
                ");
        $query->setParameter('userId', $user->getId());

        return $query->getSingleScalarResult();
    }

    /**
     * Retourne la liste des messages envoyés pour un utilisateur
     *
     * @param User_Model_User $user
     * @param int             $count Nombre maximum de messages à retourner
     *
     * @return Social_Model_Message[]
     */
    public function getUserOutbox(User_Model_User $user, $count)
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Social_Model_Message::QUERY_CREATION_DATE, Core_Model_Order::ORDER_DESC);
        $query->filter->addCondition(Social_Model_Message::QUERY_SENT, true);
        $query->filter->addCondition(Social_Model_Message::QUERY_AUTHOR, $user);
        $query->totalElements = $count;

        $messages = Social_Model_Message::loadList($query);
        return $messages;
    }

    /**
     * Retourne le nombre de messages envoyés pour un utilisateur
     *
     * @param User_Model_User $user
     *
     * @return int Nombre de messages dans la boite de messages envoyés
     */
    public function getUserOutboxSize(User_Model_User $user)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Social_Model_Message::QUERY_SENT, true);
        $query->filter->addCondition(Social_Model_Message::QUERY_AUTHOR, $user);

        return Social_Model_Message::countTotal($query);
    }

}
