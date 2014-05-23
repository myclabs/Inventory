<?php

namespace Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Common\Util\ClassUtils;
use stdClass;

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

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function serialize($data, $pretty = false)
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

        $options = $pretty ? JSON_PRETTY_PRINT : null;

        return json_encode($this->objectMap, $options);
    }

    public function unserialize($json)
    {
        $this->objectMap = [];
        $this->callbacks = [];

        $objects = json_decode($json, true);

        foreach ($objects as $id => $object) {
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
        $objectHash = '@@@' . ltrim(spl_object_hash($object), '0');

        if (isset($this->objectMap[$objectHash])) {
            return $objectHash;
        }

        $className = ClassUtils::getClass($object);
        $serialized = new \stdClass();
        $config = $this->getClassConfig($className);

        // Ignore class
        if (isset($config['exclude']) && $config['exclude'] === true) {
            return null;
        }

        $serialized->__class = $className;
        $this->objectMap[$objectHash] = $serialized;

        // If it's a proxy, we trigger it to load it
        if ($object instanceof Proxy) {
            $object->__load();
        }

        // Serialization de l'objet via PHP
        if (isset($config['serialize'])) {
            $callable = $config['serialize'];
            $serialized->__serialized = $callable($object);
            return $objectHash;
        }

        $refl = new \ReflectionObject($object);
        foreach ($refl->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propertyName = $property->getName();
            $propertyConfig = isset($config['properties'][$propertyName]) ? $config['properties'][$propertyName] : [];

            // Ignore Proxy properties
            if (strpos($propertyName, '__') === 0) {
                continue;
            }

            // Ignore property
            if (isset($propertyConfig['exclude']) && $propertyConfig['exclude'] === true) {
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

            if (isset($propertyConfig['serialize'])) {
                $callable = $propertyConfig['serialize'];
                $serializedValue = $callable($property->getValue($object));
            } else {
                $serializedValue = $this->recursiveSerialization($property->getValue($object));
            }

            $serialized->$propertyName = $serializedValue;
        }

        return $objectHash;
    }

    private function unserializeObject($id, $vars)
    {
        $className = $vars['__class'];
        unset($vars['__class']);
        $config = $this->getClassConfig($className);

        // Serialized object
        if (isset($vars['__serialized'])) {
            if (!isset($config['unserialize'])) {
                throw new \Exception('No "unserialize" callback defined for class ' . $className);
            }
            $callable = $config['unserialize'];
            $this->objectMap[$id] = $callable($vars['__serialized']);
            return;
        }

        // Ignore class
        if (isset($config['exclude']) && $config['exclude'] === true) {
            return;
        }

        $class = new \ReflectionClass($className);
        $object = $class->newInstanceWithoutConstructor();
        $this->objectMap[$id] = $object;

        foreach ($vars as $propertyName => $value) {
            $propertyConfig = isset($config['properties'][$propertyName]) ? $config['properties'][$propertyName] : [];

            // Ignore property
            if (isset($propertyConfig['exclude']) && $propertyConfig['exclude'] === true) {
                continue;
            }

            try {
                $property = $class->getProperty($propertyName);
            } catch (\Exception $e) {
                throw new \Exception("Unknown property $propertyName in $className");
            }

            $property->setAccessible(true);

            // Callback
            if (isset($propertyConfig['unserialize'])) {
                $callback = $propertyConfig['unserialize'];

                $property->setValue($object, $callback($value));
                continue;
            }

            $this->unserializePropertyValue($property, $object, $value);
        }

        // Callbacks
        if (isset($config['postUnserialize'])) {
            $callables = $config['postUnserialize'];
            if (! is_array($callables)) {
                $callables = [ $callables ];
            }
            foreach ($callables as $callable) {
                $this->callbacks[] = function () use ($callable, $object, $vars) {
                    $callable($object, $vars);
                };
            }
        }
    }

    private function unserializePropertyValue(\ReflectionProperty $property, $object, $value)
    {
        // ArrayCollection
        if (is_array($value) && ((strpos(reset($value), '@@@') === 0) || empty($value))) {
            $collection = new ArrayCollection();
            $property->setValue($object, $collection);

            foreach ($value as $valueItem) {
                $this->callbacks[] = function () use ($collection, $valueItem) {
                    $collection->add($this->objectMap[$valueItem]);
                };
            }
            return;
        }

        // Reference to another object
        if (!is_array($value) && strpos($value, '@@@') === 0) {
            $this->callbacks[] = function () use ($property, $object, $value) {
                $property->setValue($object, $this->objectMap[$value]);
            };
            return;
        }

        $property->setValue($object, $value);
    }

    private function getClassConfig($className)
    {
        $config = isset($this->config[$className]) ? $this->config[$className] : [];

        foreach ($this->config as $class => $classConfig) {
            if (is_subclass_of($className, $class)) {
                $config += $classConfig;
            }
        }

        return $config;
    }
}
