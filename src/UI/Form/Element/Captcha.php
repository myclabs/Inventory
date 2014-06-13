<?php

/**
 * Generate a Captcha Image.
 *
 * @author valentin.claras
 */
class UI_Form_Element_Captcha extends Zend_Form_Element_Captcha
{
    /**
     * @param string $name
     * @param string $urlReload
     *
     * @throws Core_Exception
     * @throws Core_Exception_InvalidArgument if $name is not valid.
     */
    public function __construct($name, $urlReload = null)
    {
        if (!(is_string($name))) {
            throw new Core_Exception_InvalidArgument('Name is required for the Element');
        }
        $cacheDir = APPLICATION_PATH.'/../public/cache/captcha';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        // Font path
        $container = \Core\ContainerSingleton::getContainer();
        if (file_exists($container->get('police.path') . 'arial.ttf')) {
            $fontPath = $container->get('police.path') . 'arial.ttf';
        } elseif (file_exists(PACKAGE_PATH . '/' . $container->get('police.path') . 'arial.ttf')) {
            $fontPath = PACKAGE_PATH . '/'  . $container->get('police.path') . 'arial.ttf';
        } else {
            throw new Core_Exception("Font file not found for captcha");
        }

        $options = [
            'class' => 'captcha',
            'required' => true,
            'captcha' => [
                'font' => $fontPath,
                'captcha' => 'Image',
                'fontSize' => 26,
                'wordLen' => 5,
                'timeout' => 200,
                'imgDir' => $cacheDir,
                'imgUrl' => 'cache/captcha',
                'imgAlt' => 'Captcha',
                'dotNoiseLevel' => 15,
                'lineNoiseLevel' => 5,
                //Fréquence de passage du garbage collector pour la suppression
                //des images du captcha
                'gcFreq'=> 50,
                //Durée de stockage des images
                'expiration' => 50,
            ]
        ];

        parent::__construct($name, $options);
    }
}
