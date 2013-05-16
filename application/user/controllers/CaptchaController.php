<?php
/**
 * @package    User
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * @package    User
 * @subpackage Controller
 */
class User_CaptchaController extends UI_Controller_Captcha
{

    /**
     * {@inheritdoc}
     * @Secure("public")
     */
    public function newimageAction()
    {
        parent::newimageAction();
    }

}
