<?php

/**
 * Controleur pour afficher des Captcha.
 * @author yoann.croizer
 */
class UI_Controller_Captcha extends Core_Controller
{
    /**
     * Fonction qui permet de générer une nouvelle captcha
     *
     * On génère un formulaire contenant un UI_Form_Element_Captcha
     * sur lequel on fait un render
     */
    public function newimageAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $captchaElement = new UI_Form_Element_Captcha('imageCaptcha');
        $captchaElement->setLabel(__('UI', 'captcha', 'labelCaptcha'));
        $captchaElement->setRequired(true);
        $captcha = $captchaElement->getCaptcha();

        $data = array();
        $data['id'] = $captcha->generate();
        $data['src'] = $captcha->getImgUrl() . $captcha->getId() . $captcha->getSuffix();

        $this->sendJsonResponse($data);
    }
}
