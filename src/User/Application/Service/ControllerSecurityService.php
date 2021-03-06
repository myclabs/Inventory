<?php

namespace User\Application\Service;

use Core_Exception;
use Core_Exception_InvalidArgument;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Core\Annotation\Secure;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Psr\Log\LoggerInterface;
use ReflectionMethod;
use User\Application\HttpNotFoundException;
use Zend_Controller_Front;

/**
 * Service gérant la sécurité pour les controleurs.
 *
 * @author matthieu.napoli
 */
class ControllerSecurityService
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns the security rule that applies to an action
     *
     * @param string $module
     * @param string $controller
     * @param string $action
     *
     * @return null|string Name of the rule, null if not found
     *
     * @throws Core_Exception
     * @throws Core_Exception_InvalidArgument
     */
    public function getSecurityRule($module, $controller, $action)
    {
        $className = '';
        if (strtolower($module) != 'default') {
            foreach (explode('-', $module) as $modulePart) {
                $className .= ucfirst($modulePart);
            }
            $className .= '_';
        }
        foreach (explode('-', $controller) as $controllerPart) {
            $className .= ucfirst($controllerPart);
        }
        $className .= 'Controller';

        $methodName = str_replace('-', '', $action) . 'Action';

        if (!class_exists($className)) {
            // Le contrôleur n'est peut-être pas encore chargé
            if (!$this->loadController($module, $controller)) {
                throw new HttpNotFoundException;
            }
        }

        if (! method_exists($className, $methodName)) {
            throw new HttpNotFoundException;
        }

        $method = new ReflectionMethod($className, $methodName);

        /** @var $annotation Secure|null */
        $annotation = $this->getAnnotationReader()->getMethodAnnotation($method, 'Core\Annotation\Secure');
        if (!$annotation) {
            return null;
        }
        if ($annotation->rule === null) {
            throw new Core_Exception("@Secure was found on $className::$methodName but no rule was given");
        }
        return $annotation->rule;
    }

    /**
     * @return Reader The annotation reader
     */
    private function getAnnotationReader()
    {
        if ($this->annotationReader == null) {
            AnnotationRegistry::registerFile(PACKAGE_PATH . '/src/Core/Annotation/Secure.php');
            $this->annotationReader = new SimpleAnnotationReader();
            $this->annotationReader->addNamespace('Core\Annotation');
        }
        return $this->annotationReader;
    }

    /**
     * @param string $module
     * @param string $controller
     * @return bool
     */
    private function loadController($module, $controller)
    {
        $front = Zend_Controller_Front::getInstance();
        $baseDir = $front->getControllerDirectory($module);

        $array = [];
        foreach (explode('_', $controller) as $controllerPart) {
            $array[] = ucfirst($controllerPart);
        }
        $controller = implode('/', $array);

        $controllerFile = $baseDir . '/';
        foreach (explode('-', $controller) as $controllerPart) {
            $controllerFile .= ucfirst($controllerPart);
        }
        $controllerFile .= 'Controller.php';

        if (file_exists($controllerFile)) {
            /** @noinspection PhpIncludeInspection */
            require_once $controllerFile;
            return true;
        }
        $this->logger->warning('File ' . $controllerFile . ' not found');
        return false;
    }
}
