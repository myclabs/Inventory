<?php
/**
 * @author matthieu.napoli
 * @package User
 * @subpackage Form
 */

/**
 * @package User
 * @subpackage Form
 */
class User_Form_PasswordForgotten extends UI_Form
{

    /**
     * {@inheritdoc}
     */
    public function __construct($ref)
    {
        parent::__construct('passwordForgotten');
        $this->setAction('user/action/password-forgotten');

        $email = new UI_Form_Element_Pattern_Email('email');
        $email->setLabel(__('UI', 'name', 'emailAddress'));
        $email->setRequired();
        $this->addElement($email);

        $captcha = new UI_Form_Element_Captcha('captcha', 'user/captcha/newimage');
        $captcha->setLabel(__('User', 'resetPassword', 'captchaFieldLabel'));
        $captcha->setRequired();
        $this->addElement($captcha);

        $this->addSubmitButton(__('UI', 'verb', 'validate'));
    }

}
