<?php
/**
 * @package    Social
 * @subpackage Service
 */

use Doctrine\ORM\EntityManager;
use User\Domain\User;
use User\Domain\UserService;

/**
 * Service de gestion des messages entre utilisateurs.
 *
 * @author matthieu.napoli
 */
class Social_Service_MessageService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param EntityManager     $entityManager
     * @param UserService $userService
     */
    public function __construct(EntityManager $entityManager, UserService $userService)
    {
        $this->entityManager = $entityManager;
        $this->userService = $userService;
    }

    /**
     * Envoie un nouveau message
     *
     * @param User $author
     * @param array           $recipients
     * @param string          $title
     * @param string          $text
     *
     * @return Social_Model_Message
     */
    public function sendNewMessage(User $author, array $recipients, $title, $text)
    {
        // Création du message
        $message = new Social_Model_Message($author, $title);
        $message->setText($text);
        foreach ($recipients as $recipient) {
            if ($recipient instanceof User) {
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

            if ($recipient instanceof User) {
                $subject = $message->getTitle();
                $this->userService->sendEmail($recipient, $subject, $content);

            } elseif ($recipient instanceof Social_Model_UserGroup) {
                $subject = '[' . $recipient->getLabel() . '] ' . $message->getTitle();
                foreach ($recipient->getUsers() as $user) {
                    $this->userService->sendEmail($user, $subject, $content);
                }
            }
        }

        return $message;
    }

    /**
     * Retourne la liste des messages reçus pour un utilisateur
     *
     * @param User $user
     * @param int             $count Nombre maximum de messages à retourner
     *
     * @return Social_Model_Message[]
     */
    public function getUserInbox(User $user, $count)
    {
        $query = $this->entityManager->createQuery("SELECT m FROM Social_Model_Message m
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
     * @param User $user
     *
     * @return int Nombre de messages dans la boite de réception
     */
    public function getUserInboxSize(User $user)
    {
        $query = $this->entityManager->createQuery("SELECT COUNT(m) FROM Social_Model_Message m
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
     * @param User $user
     * @param int             $count Nombre maximum de messages à retourner
     *
     * @return Social_Model_Message[]
     */
    public function getUserOutbox(User $user, $count)
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Social_Model_Message::QUERY_CREATION_DATE, Core_Model_Order::ORDER_DESC);
        $query->filter->addCondition(Social_Model_Message::QUERY_SENT, true);
        $query->filter->addCondition(Social_Model_Message::QUERY_AUTHOR, $user);
        $query->totalElements = $count;

        return Social_Model_Message::loadList($query);
    }

    /**
     * Retourne le nombre de messages envoyés pour un utilisateur
     *
     * @param User $user
     *
     * @return int Nombre de messages dans la boite de messages envoyés
     */
    public function getUserOutboxSize(User $user)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Social_Model_Message::QUERY_SENT, true);
        $query->filter->addCondition(Social_Model_Message::QUERY_AUTHOR, $user);

        return Social_Model_Message::countTotal($query);
    }

}
