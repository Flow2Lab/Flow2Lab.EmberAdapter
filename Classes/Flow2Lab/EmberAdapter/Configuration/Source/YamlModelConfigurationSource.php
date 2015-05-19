<?php
namespace Flow2Lab\EmberAdapter\Configuration\Source;

use Flow2Lab\EmberAdapter\Annotations\AbstractRelationAttribute;
use Flow2Lab\EmberAdapter\Configuration\Source\Exception\ConfigurationNotAvailableException;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Reflection\ReflectionService;

/**
 * Yaml Source for configuring ember model with a yaml file
 *
 * @Flow\Scope("singleton")
 */
class YamlModelConfigurationSource implements ModelConfigurationSourceInterface {

	const EmberModels = 'EmberModels';
	const BELONGS_TO = 'BelongsTo';
	const HAS_MANY = 'HasMany';

	/**
	 * @var ConfigurationManager
	 * @Flow\Inject
	 */
	protected $configurationManager;

	/**
	 * @var ReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * @var array
	 */
	protected $yamlEmberModels = array();

	/**
	 * Load ember model yaml configuration
	 */
	public function initializeObject() {
		foreach ($this->configurationManager->getConfiguration('EmberModels') as $emberClassName => $properties) {
			$this->yamlEmberModels[$emberClassName] = $properties;
		}
	}

	/**
	 * @param string $className
	 * @return array
	 * @throws ConfigurationNotAvailableException
	 */
	protected function assertConfigurationExists($className) {
		if (array_key_exists($className, $this->yamlEmberModels) === FALSE) {
			throw new ConfigurationNotAvailableException();
		}
	}

	/**
	 * @return integer Priority of the configuration
	 */
	public function getPriority() {
		return 10;
	}

	/**
	 * @param object $object
	 * @return string
	 * @throws ConfigurationNotAvailableException
	 */
	public function getClassNameByObject($object) {
		throw new ConfigurationNotAvailableException();
	}

	/**
	 * @param string $className
	 * @return boolean
	 * @throws ConfigurationNotAvailableException
	 */
	public function isClassEmberModel($className) {
		return array_key_exists($className, $this->yamlEmberModels);
	}

	/**
	 * @param object $object
	 * @return string
	 * @throws ConfigurationNotAvailableException
	 */
	public function getModelNameByObject($object) {
		$className = $this->reflectionService->getClassNameByObject($object);

		$this->assertConfigurationExists($className);

		return $this->yamlEmberModels[$className]['modelName'];
	}

	/**
	 * @param string $className
	 * @return array
	 * @throws ConfigurationNotAvailableException
	 */
	public function getModelPropertyNames($className) {
		$this->assertConfigurationExists($className);

		if (isset($this->yamlEmberModels[$className]['properties']) === FALSE) {
			return array();
		}

		return array_keys($this->yamlEmberModels[$className]['properties']);
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 * @throws ConfigurationNotAvailableException
	 */
	public function getModelAttributeName($className, $propertyName) {
		$propertyConfiguration = $this->getAttributeConfiguration($className, $propertyName);

		if (isset($propertyConfiguration['type'])) {
			return $propertyName;
		}

		throw new ConfigurationNotAvailableException();
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 * @throws ConfigurationNotAvailableException
	 */
	public function getModelAttributeType($className, $propertyName) {
		$propertyConfiguration = $this->getAttributeConfiguration($className, $propertyName);

		if (isset($propertyConfiguration['type'])) {
			return $propertyConfiguration['type'];
		}

		throw new ConfigurationNotAvailableException();
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return array
	 * @throws ConfigurationNotAvailableException
	 */
	public function getModelAttributeOptions($className, $propertyName) {
		$propertyConfiguration = $this->getAttributeConfiguration($className, $propertyName);

		if (isset($propertyConfiguration['options'])) {
			return $propertyConfiguration['options'];
		}

		throw new ConfigurationNotAvailableException();
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return array
	 * @throws ConfigurationNotAvailableException
	 */
	protected function getAttributeConfiguration($className, $propertyName) {
		$this->assertConfigurationExists($className);

		$configuration = $this->yamlEmberModels[$className];

		if (isset($configuration['properties'][$propertyName])) {
			return $configuration['properties'][$propertyName];
		}

		throw new ConfigurationNotAvailableException();
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return bool
	 * @throws ConfigurationNotAvailableException
	 */
	public function isRelation($className, $propertyName) {
		$this->assertConfigurationExists($className);

		$configuration = $this->yamlEmberModels[$className]['properties'];

		if (isset($configuration[$propertyName][self::BELONGS_TO]) || isset($configuration[$propertyName][self::HAS_MANY])) {
			return TRUE;
		}

		throw new ConfigurationNotAvailableException();
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return AbstractRelationAttribute|NULL
	 * @throws ConfigurationNotAvailableException
	 */
	public function getRelation($className, $propertyName) {
		throw new ConfigurationNotAvailableException();
	}

}