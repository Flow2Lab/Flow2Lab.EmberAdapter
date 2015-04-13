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
		// todo: make configurable
		throw new ConfigurationNotAvailableException();
	}

	/**
	 * @param string $className
	 * @return boolean
	 * @throws ConfigurationNotAvailableException
	 */
	public function isClassEmberModel($className) {
		foreach ($this->configurationManager->getConfiguration(self::EmberModels) as $modelName => $configuration) {
			if ($configuration['className'] === $className) {
				return TRUE;
			}
		}

		throw new ConfigurationNotAvailableException();
	}

	/**
	 * @param object $object
	 * @return string Ember model name
	 * @throws ConfigurationNotAvailableException
	 */
	public function getModelNameByObject($object) {
		// todo: implement
		throw new ConfigurationNotAvailableException();
	}

	/**
	 * @param string $className
	 * @return NULL|string[]
	 * @throws ConfigurationNotAvailableException
	 */
	public function getModelPropertyNames($className) {
		foreach ($this->configurationManager->getConfiguration(self::EmberModels) as $modelName => $configuration) {
			if ($configuration['className'] === $className) {
				return array_keys($configuration['properties']);
			}
		}

		throw new ConfigurationNotAvailableException();
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeName($className, $propertyName) {
		// todo: check if name is configured
		$propertyConfiguration = $this->getAttributeConfiguration($className, $propertyName);
		return $propertyName;
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
		foreach ($this->configurationManager->getConfiguration(self::EmberModels) as $modelName => $configuration) {
			if ($configuration['className'] === $className && isset($configuration['properties'][$propertyName])) {
				return $configuration['properties'][$propertyName];
			}
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
		foreach ($this->configurationManager->getConfiguration(self::EmberModels) as $modelName => $configuration) {
			if ($configuration['className'] === $className && (isset($configuration[$propertyName][self::BELONGS_TO]) || isset($configuration[$propertyName][self::HAS_MANY]))) {
				return TRUE;
			}
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
		// todo: implement yaml configuration
		throw new ConfigurationNotAvailableException();
	}

}