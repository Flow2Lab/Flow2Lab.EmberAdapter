<?php
namespace Flow2Lab\EmberAdapter\Configuration;

use Flow2Lab\EmberAdapter\Annotations\AbstractRelationAttribute;
use Flow2Lab\EmberAdapter\Configuration\Source\Exception\ConfigurationNotAvailableException;
use Flow2Lab\EmberAdapter\Configuration\Source\ModelConfigurationSourceInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Reflection\ReflectionService;

/**
 * Handles all backend Model Configuration Sources based on priority
 *
 * @Flow\Scope("singleton")
 */
class ModelConfigurationManager {

	/**
	 * @Flow\Inject
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var ModelConfigurationSourceInterface[]
	 */
	protected $configurationSources = [];

	/**
	 * Initialize Object
	 */
	public function initializeObject() {
		$configurationSourceClassNames = self::getModelConfigurationSourceImplementationClassNames($this->objectManager);

		foreach ($configurationSourceClassNames as $configurationSourceClassName) {
			/** @var ModelConfigurationSourceInterface $source */
			$source = $this->objectManager->get($configurationSourceClassName);

			if (array_key_exists($source->getPriority(), $this->configurationSources) === TRUE) {
				throw new \InvalidArgumentException('The priority of one or more model configuration sources is not unique.', 1428323195);
			}

			$this->configurationSources[$source->getPriority()] = $source;
		}

		// sort by priority in reverse order (highest first)
		krsort($this->configurationSources);
	}

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return array Class names of all available sources
	 * @Flow\CompileStatic
	 */
	static public function getModelConfigurationSourceImplementationClassNames(ObjectManagerInterface $objectManager) {
		$reflectionService = $objectManager->get(ReflectionService::class);
		return $reflectionService->getAllImplementationClassNamesForInterface(ModelConfigurationSourceInterface::class);
	}

	/**
	 * @param object $object
	 * @return string
	 */
	public function getClassNameByObject($object) {
		return $this->reflectionService->getClassNameByObject($object);
	}

	/**
	 * @param string $className
	 * @return boolean
	 */
	public function isClassEmberModel($className) {
		return $this->getConfiguration('isClassEmberModel', $className);
	}

	/**
	 * @param object $object
	 * @return string Ember model name
	 * @throws \InvalidArgumentException If the given object is not annotated as ember model
	 */
	public function getModelNameByObject($object) {
		$this->assertEmberModel($object);
		return $this->getConfiguration('getModelNameByObject', $object);
	}

	/**
	 * @param object $object
	 * @return mixed
	 */
	public function getModelIdentifierByObject($object) {
		$this->assertEmberModel($object);

		if (ObjectAccess::isPropertyGettable($object, 'id')) {
			return ObjectAccess::getProperty($object, 'id');
		}

		return $this->persistenceManager->getIdentifierByObject($object);
	}

	/**
	 * @param string $className
	 * @return string[]
	 */
	public function getModelPropertyNames($className) {
		$this->assertEmberModel($className);
		return $this->getConfiguration('getModelPropertyNames', $className);
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 */
	public function getModelAttributeName($className, $propertyName) {
		$this->assertEmberModel($className);
		return $this->getConfiguration('getModelAttributeName', $className, $propertyName);
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeType($className, $propertyName) {
		$this->assertEmberModel($className);
		return $this->getConfiguration('getModelAttributeType', $className, $propertyName);
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return array
	 */
	public function getModelAttributeOptions($className, $propertyName) {
		$this->assertEmberModel($className);
		return $this->getConfiguration('getModelAttributeOptions', $className, $propertyName);
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return boolean
	 */
	public function isRelation($className, $propertyName) {
		$this->assertEmberModel($className);
		return $this->getConfiguration('isRelation', $className, $propertyName);
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return NULL|AbstractRelationAttribute
	 */
	public function getRelation($className, $propertyName) {
		$this->assertEmberModel($className);
		return $this->getConfiguration('getRelation', $className, $propertyName);
	}

	/**
	 * @param string $methodName Method to call on the source
	 * @return mixed
	 */
	protected function getConfiguration($methodName) {
		$methodArguments = func_get_args();
		array_shift($methodArguments);

		foreach ($this->configurationSources as $configurationSource) {
			try {
				$configuration = call_user_func_array(
					array($configurationSource, $methodName),
					$methodArguments
				);

				return $configuration;
			} catch (ConfigurationNotAvailableException $e) {}
		}

		return NULL;
	}

	/**
	 * @param object|string $classNameOrObject
	 */
	protected function assertEmberModel($classNameOrObject) {
		if (is_object($classNameOrObject)) {
			$objectClassName = $this->getClassNameByObject($classNameOrObject);
		} else {
			$objectClassName = $classNameOrObject;
		}

		if ($this->isClassEmberModel($objectClassName) === FALSE) {
			throw new \InvalidArgumentException('The given className or object is not an ember model.', 1428326298);
		}
	}
}