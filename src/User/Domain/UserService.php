<?php

namespace User\Domain;

use Core_Exception;
use Core_Exception_Duplicate;
use Core_Mail;
use Core_Tools;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Role\UserRole;
use Zend_Controller_Front;
use Zend_Registry;

/**
 * Gestion des utilisateurs.
 *
 * @author matthieu.napoli
 */
class UserService
{
    /**
     * @var ACLService
     */
    private $aclService;

    public function __construct(ACLService $aclService)
    {
        $this->aclService = $aclService;
    }

    /**
     * Crée et initialise un nouvel utilisateur
     *
     * @param string $email
     * @param string $password
     * @return User
     */
    public function createUser($email, $password)
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($password);
        // Ajoute le role utilisateur
        $this->aclService->addRole($user, new UserRole($user));

        $user->save();

        return $user;
    }

    /**
     * Supprime un utilisateur et sa ressource associée
     *
     * @param User $user
     */
    public function deleteUser(User $user)
    {
        foreach ($user->getRoles() as $role) {
            $this->aclService->removeRole($user, $role);
        }

        $user->delete();
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
     * @return User L'instance de l'utilisateur concerné
     */
    public function inviteUser($email, $extraContent = null)
    {
        if (User::isEmailUsed($email)) {
            throw new Core_Exception_Duplicate('Email is already used');
        }

        // Génère un mot de passe à 8 caractères
        $password = Core_Tools::generateString(8);

        // Crée l'utilisateur
        $user = $this->createUser($email, $password);
        $user->generateKeyEmail();

        // Sauvegarde
        $user->save();

        $url = 'http://' . $_SERVER["SERVER_NAME"] . Zend_Controller_Front::getInstance()->getBaseUrl() . '/';

        $config = Zend_Registry::get('configuration');
        if (empty($config->emails->contact->adress)) {
            throw new Core_Exception("Le courriel de 'contact' n'a pas été défini");
        }
        $emailSubject = __('User', 'email', 'subjectAccountCreated', [
            'APPLICATION_NAME' => $config->emails->noreply->name,
        ]);
        $emailContent = __('User', 'email', 'bodyAccountCreated', [
            'PASSWORD'         => $password,
            'EMAIL'            => $email,
            'CONTACT_NAME'     => $config->emails->contact->name,
            'CONTACT_ADDRESS'  => $config->emails->contact->adress,
            'URL_APPLICATION'  => $url,
            'APPLICATION_NAME' => $config->emails->noreply->name,
        ]);
        $emailContent .= $extraContent;

        $this->sendEmail($user, $emailSubject, $emailContent);

        return $user;
    }

    /**
     * Envoie un mail à l'utilisateur
     * @param User   $user
     * @param string $subject
     * @param string $content
     * @return void
     */
    public function sendEmail(User $user, $subject, $content)
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
        if (empty($config->emails->contact->adress)) {
            throw new Core_Exception('Le courriel de "contact" n\'a pas été défini !');
        }
        return __('User', 'email', 'defaultContentConclusion', [
            'CONTACT_NAME' => $config->emails->contact->name,
            'CONTACT_ADDRESS' => $config->emails->contact->adress,
        ]);
    }

    /**
     * Retourne le pied de page par défaut des mails
     * @return string
     */
    protected function getEmailFooter()
    {
        $config = Zend_Registry::get('configuration');
        return __('User', 'email', 'footMailContentDefault', [
            'APPLICATION_NAME' => $config->emails->noreply->name,
        ]);
    }
}
