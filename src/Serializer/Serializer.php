<?php

namespace Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Query;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;

class Serializer
{
    /**
     * @var array
     */
    private $objectMap = [];

    /**
     * @var string[]
     */
    private $stackTrace = [];

    /**
     * @var array
     */
    private $config;

    /**
     * @var \Closure[]
     */
    private $callbacks = [];

    /**
     * @var TranslationRepository
     */
    private $translationRepository;

    public function __construct(array $config, TranslationRepository $translationRepository)
    {
        $this->config = $config;
        $this->translationRepository = $translationRepository;
    }

    public function serialize($data)
    {
        $this->objectMap = [];
        $this->stackTrace = [];

        try {
            $this->recursiveSerialization($data);
        } catch (\Exception $e) {
            throw new \Exception(sprintf(
                "Error while serializing: %s\nFull stack trace:\n\t%s",
                $e->getMessage(),
                implode("\n\t", $this->stackTrace)
            ), 0, $e);
        }

        return json_encode($this->objectMap, JSON_PRETTY_PRINT);
    }

    public function unserialize($json)
    {
        $this->objectMap = [];
        $this->callbacks = [];

        foreach (json_decode($json, true) as $id => $object) {
            $this->unserializeObject($id, $object);
        }

        // Run the callbacks
        foreach ($this->callbacks as $callback) {
            $callback();
        }

        return $this->objectMap;
    }

    private function recursiveSerialization($var)
    {
        $this->stackTrace[] = is_object($var) ? get_class($var) : gettype($var);

        if (is_resource($var)) {
            throw new \RuntimeException('Impossible to serialize a resource');
        } elseif (is_array($var) || $var instanceof \Traversable) {
            $return = $this->serializeArray($var);
        } elseif (! is_object($var)) {
            $return = $var;
        } else {
            $return = $this->serializeObject($var);
        }

        array_pop($this->stackTrace);

        return $return;
    }

    private function serializeArray($array)
    {
        $serialized = [];

        foreach ($array as $key => $item) {
            $serialized[$key] = $this->recursiveSerialization($item);
        }

        return $serialized;
    }

    private function serializeObject($object)
    {
        $objectHash = '@@@' . spl_object_hash($object);

        if (isset($this->objectMap[$objectHash])) {
            return $objectHash;
        }

        $serialized = new \stdClass();

        $serialized->__objectClassName = ClassUtils::getClass($object);

        // If it's a proxy, we trigger it to load it
        if ($object instanceof Proxy) {
            $object->__load();
        }

        if (isset($this->config[$serialized->__objectClassName])) {
            $config = $this->config[$serialized->__objectClassName];
        } else {
            $config = [];
        }

        // Ignore class
        if (isset($config['exclude']) && $config['exclude'] === true) {
            return;
        }

        $this->objectMap[$objectHash] = $serialized;

        // Serialization de l'objet via PHP
        if (isset($config['serialize']) && $config['serialize'] === true) {
            $serialized->__serialized = serialize($object);
            return $objectHash;
        }

        $refl = new \ReflectionObject($object);
        foreach ($refl->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propertyName = $property->getName();

            // Ignore ID
            if ($propertyName == 'id') {
                continue;
            }

            // Ignore Proxy properties
            if (strpos($propertyName, '__') === 0) {
                continue;
            }

            // Ignore property
            if (isset($config['properties'][$propertyName]['exclude'])
                && $config['properties'][$propertyName]['exclude'] === true) {
                continue;
            }

            $property->setAccessible(true);

            // Translated property
            if (isset($config['properties'][$propertyName]['translated'])
                && $config['properties'][$propertyName]['translated'] === true) {
                $translations = $this->translationRepository->findTranslations($object);
                // Moche, à cause d'un bug dans Translatable
                if (empty($translations)) {
                    $qb = $this->translationRepository->createQueryBuilder('trans');
                    $qb->select('trans.content, trans.field, trans.locale')
                        ->where('trans.foreignKey = :entityId', 'trans.objectClass = :entityClass');
                    $data = $qb->getQuery()->execute(
                        ['entityId' => $object->getId(), 'entityClass' => ClassUtils::getClass($object)],
                        Query::HYDRATE_ARRAY
                    );
                    if ($data && is_array($data) && count($data)) {
                        foreach ($data as $row) {
                            $translations[$row['locale']][$row['field']] = $row['content'];
                        }
                    }
                }
                $propertyTranslations = [
                    'translated' => true,
                    'fr'         => $property->getValue($object), // valeur par défaut
                ];
                foreach ($translations as $lang => $properties) {
                    if (isset($properties[$propertyName])) {
                        $propertyTranslations[$lang] = $properties[$propertyName];
                    }
                }
                $property->setValue($object, $propertyTranslations);
            }

            if (isset($config['properties'][$propertyName]['transform'])) {
                $callable = $config['properties'][$propertyName]['transform'];
                $serialized->$propertyName = $callable($property->getValue($object));
                continue;
            }

            $serialized->$propertyName = $this->recursiveSerialization($property->getValue($object));
        }

        return $objectHash;
    }

    private function unserializeObject($id, $vars)
    {
        if (isset($vars['__serialized'])) {
            $object = unserialize($vars['__serialized']);
            $this->objectMap[$id] = $object;
            return;
        }

        $className = $vars['__objectClassName'];

        if (isset($this->config[$className])) {
            $config = $this->config[$className];
        } else {
            $config = [];
        }

        // Ignore class
        if (isset($config['exclude']) && $config['exclude'] === true) {
            // Callbacks
            if (isset($config['callbacks'])) {
                $callables = $config['callbacks'];
                if (! is_array($callables)) {
                    $callables = [ $callables ];
                }
                foreach ($callables as $callable) {
                    $callable($vars);
                }
            }
            return;
        }

        // Class alias
        if (isset($config['class'])) {
            $className = $config['class'];
        }

        $class = new \ReflectionClass($className);
        $object = $class->newInstanceWithoutConstructor();

        $this->objectMap[$id] = $object;

        foreach ($vars as $propertyName => $value) {
            if (strpos($propertyName, '__') === 0) {
                continue;
            }

            // Ignore property
            if (isset($config['properties'][$propertyName]['exclude'])
                && $config['properties'][$propertyName]['exclude'] === true) {
                continue;
            }

            // Property name
            if (isset($config['properties'][$propertyName]['name'])) {
                $propertyName = $config['properties'][$propertyName]['name'];
            }

            try {
                $property = $class->getProperty($propertyName);
            } catch (\Exception $e) {
                throw new \Exception("Unknown property $propertyName in $className");
            }

            // Callback
            if (isset($config['properties'][$propertyName]['callback'])) {
                $callback = $config['properties'][$propertyName]['callback'];

                $value = $callback($value);
                $property->setAccessible(true);
                $property->setValue($object, $value);
                continue;
            }

            $this->unserializePropertyValue($property, $object, $value);
        }

        // Callbacks
        if (isset($config['callbacks'])) {
            $callables = $config['callbacks'];
            if (! is_array($callables)) {
                $callables = [ $callables ];
            }
            foreach ($callables as $callable) {
                $callable($object, $vars);
            }
        }
    }

    private function unserializePropertyValue(\ReflectionProperty $property, $object, $value)
    {
        $property->setAccessible(true);

        if (is_array($value) && (strpos(reset($value), '@@@') === 0)) {
            $collection = new ArrayCollection();
            $property->setValue($object, $collection);

            foreach ($value as $valueItem) {
                $this->callbacks[] = function () use ($collection, $valueItem) {
                    $collection->add($this->objectMap[$valueItem]);
                };
            }
            return;
        }

        if (!is_array($value) && strpos($value, '@@@') === 0) {
            $this->callbacks[] = function () use ($property, $object, $value) {
                $property->setValue($object, $this->objectMap[$value]);
            };
            return;
        }

        $property->setValue($object, $value);
    }
}
