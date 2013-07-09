<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Service
 */

use Doctrine\ORM\EntityManager;

/**
 * Gestion des utilisateurs
 * @package    User
 * @subpackage Service
 */
class User_Service_User
{

    /**
     * @var User_Service_ACL
     */
    private $aclService;

    /**
     * @param User_Service_ACL $aclService
     */
    public function __construct(User_Service_ACL $aclService)
    {
        $this->aclService = $aclService;
    }

    /**
     * Crée et initialise un nouvel utilisateur
     *
     * @param string $email
     * @param string $password
     * @return User_Model_User
     */
    public function createUser($email, $password)
    {
        $this->getEntityManager()->beginTransaction();

        $user = new User_Model_User();
        $user->setEmail($email);
        $user->setPassword($password);
        $user->save();
        $this->getEntityManager()->flush();

        // Crée la ressource associée à l'utilisateur
        $resource = new User_Model_Resource_Entity();
        $resource->setEntity($user);
        $resource->save();
        $this->getEntityManager()->flush();

        // Donne le droit à l'utilisateur de se modifier
        $this->aclService->allow($user, User_Model_Action_Default::VIEW(), $user);
        $this->aclService->allow($user, User_Model_Action_Default::EDIT(), $user);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->commit();

        return $user;
    }

    /**
     * Supprime un utilisateur et sa ressource associée
     *
     * @param User_Model_User $user
     */
    public function deleteUser($user)
    {
        $this->getEntityManager()->beginTransaction();

        // Supprime les droits
        $this->aclService->disallow($user, User_Model_Action_Default::VIEW(), $user);
        $this->aclService->disallow($user, User_Model_Action_Default::EDIT(), $user);
        $this->getEntityManager()->flush();

        // Supprime la ressource utilisateur
        $resource = User_Model_Resource_Entity::loadByEntity($user);
        if ($resource) {
            $resource->delete();
        }

        // Supprime l'utilisateur
        $user->delete();

        $this->getEntityManager()->flush();
        $this->getEntityManager()->commit();
    }

    /**
     * Crée un utilisateur, lui envoie un mail d'invitation dans l'application et le retourne
     *
     * L'utilisateur est créé avec un mot de passe aléatoire.
     *
     * @param string $email Email de l'utilisateur
     * @param string $extraContent
     * @throws Core_Exception_Duplicate Email is already used
     * @throws Core_Exception
     * @return User_Model_User L'instance de l'utilisateur concerné
     */
    public function inviteUser($email, $extraContent = null)
    {
        if (User_Model_User::isEmailUsed($email)) {
            throw new Core_Exception_Duplicate('Email is already used');
        }

        // Génère un mot de passe à 8 caractères
        $password = Core_Tools::generateString(8);

        // Crée l'utilisateur
        $user = $this->createUser($email, $password);
        $user->generateKeyEmail();

        // Sauvegarde
        $user->save();
        $this->getEntityManager()->flush();

        $url = 'http://' . $_SERVER["SERVER_NAME"] . Zend_Controller_Front::getInstance()->getBaseUrl() . '/';

        $config = Zend_Registry::get('configuration');
        if (empty($config->emails->contact->adress) || empty($config->emails->contact->name)) {
            throw new Core_Exception("Le courriel de 'contact' n'a pas été défini");
        }
        $emailSubject = __('User',
                         'email',
                         'subjectAccountCreated',
                         array(
                              'APPLICATION_NAME' => $config->emails->noreply->name
                         ));
        $emailContent = __('User',
                               'email',
                               'bodyAccountCreated',
                               array(
                                    'PASSWORD'         => $password,
                                    'EMAIL'            => $email,
                                    'CONTACT_NAME'     => $config->emails->contact->name,
                               	    'CONTACT_ADDRESS'  => $config->emails->contact->adress,
                                    'URL_APPLICATION'  => $url,
                                    'APPLICATION_NAME' => $config->emails->noreply->name
                               ));
        $emailContent .= $extraContent;

        $this->sendEmail($user, $emailSubject, $emailContent);

        return $user;
    }

    /**
     * Envoie un mail à l'utilisateur
     * @param User_Model_User $user
     * @param string          $subject
     * @param string          $content
     * @return void
     */
    public function sendEmail(User_Model_User $user, $subject, $content)
    {
        $mail = new Core_Mail();
        $mail->addTo($user->getEmail(), $user->getName());
        $mail->setSubject($subject);
        $content = $this->getEmailHeader() . ' ' . PHP_EOL . $content;
        $content .= ' ' . $this->getEmailConclusion();
        $content .= ' ' . $this->getEmailFooter();
        $mail->setBodyText($content);
        $mail->send();
    }

    /**
     * Retourne le header du courriel
     * @return string
     */
    protected function getEmailHeader()
    {
        return __('User', 'email', 'defaultContentIntroduction');
    }

    /**
     * Retourne la conclusion par défaut des mails
     * @return string
     * @throws Core_Exception
     */
    protected function getEmailConclusion()
    {
        $config = Zend_Registry::get('configuration');
        if ((empty($config->emails->contact->adress)) || (empty($config->emails->contact->name))) {
            throw new Core_Exception('Le courriel de "contact" n\'a pas été défini !');
        }
        return __('User',
                  'email',
                  'defaultContentConclusion',
                  array(
                       'CONTACT_NAME' => $config->emails->contact->name,
                       'CONTACT_ADDRESS' => $config->emails->contact->adress
                  ));
    }

    /**
     * Retourne le pied de page par défaut des mails
     * @return string
     */
    protected function getEmailFooter()
    {
        $config = Zend_Registry::get('configuration');
        return __('User',
                  'email',
                  'footMailContentDefault',
                  array(
                       'APPLICATION_NAME' => $config->emails->noreply->name
                  ));
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        return $entityManagers['default'];
    }

}
