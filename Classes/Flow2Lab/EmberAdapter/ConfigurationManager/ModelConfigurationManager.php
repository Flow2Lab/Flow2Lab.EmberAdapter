<?php
namespace Flow2Lab\EmberAdapter\ConfigurationManager;

use Flow2Lab\EmberAdapter\Backend\ModelConfigurationSourceInterface;
use TYPO3\Flow\Annotations as Flow;

/**
 * Handles all backend Model Configuration Sources based on priority
 *
 * @Flow\Scope("singleton")
 */
class ModelConfigurationManager implements ModelConfigurationSourceInterface {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @Flow\Inject
	 * @var \Flow2Lab\EmberAdapter\Backend\YamlModelConfigurationSource
	 */
	protected $yamlModelConfigurationSource;

	/**
	 * @Flow\Inject
	 * @var \Flow2Lab\EmberAdapter\Backend\ReflectionModelConfigurationSource
	 */
	protected $reflectionModelConfigurationSource;

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
		return ($this->yamlModelConfigurationSource->isClassEmberModel($className)
			|| $this->reflectionModelConfigurationSource->isClassEmberModel($className));
	}

	/**
	 * @param object $object
	 * @return string Ember model name
	 * @throws \InvalidArgumentException If the given object is not annotated as ember model
	 */
	public function getModelNameByObject($object) {
		if ($this->yamlModelConfigurationSource->isClassEmberModel($this->getClassNameByObject($object))) {
			return $this->yamlModelConfigurationSource->getModelNameByObject($object);
		}
		if ($this->reflectionModelConfigurationSource->isClassEmberModel($this->getClassNameByObject($object))) {
			return $this->reflectionModelConfigurationSource->getModelNameByObject($object);
		}
	}

	/**
	 * @param string $className
	 * @return array<string>
	 */
	public function getModelPropertyNames($className) {
		if ($this->yamlModelConfigurationSource->isClassEmberModel($className)) {
			return $this->yamlModelConfigurationSource->getModelPropertyNames($className);
		}
		if ($this->reflectionModelConfigurationSource->isClassEmberModel($className)) {
			return $this->reflectionModelConfigurationSource->getModelPropertyNames($className);
		}
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeName($className, $propertyName) {
		if ($this->yamlModelConfigurationSource->isClassEmberModel($className)) {
			return $this->yamlModelConfigurationSource->getModelAttributeName($className, $propertyName);
		}
		if ($this->reflectionModelConfigurationSource->isClassEmberModel($className)) {
			return $this->reflectionModelConfigurationSource->getModelAttributeName($className, $propertyName);
		}
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeType($className, $propertyName) {
		if ($this->yamlModelConfigurationSource->isClassEmberModel($className)) {
			return $this->yamlModelConfigurationSource->getModelAttributeType($className, $propertyName);
		}
		if ($this->reflectionModelConfigurationSource->isClassEmberModel($className)) {
			return $this->reflectionModelConfigurationSource->getModelAttributeType($className, $propertyName);
		}
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeOptions($className, $propertyName) {
		if ($this->yamlModelConfigurationSource->isClassEmberModel($className)) {
			return $this->yamlModelConfigurationSource->getModelAttributeOptions($className, $propertyName);
		}
		if ($this->reflectionModelConfigurationSource->isClassEmberModel($className)) {
			return $this->reflectionModelConfigurationSource->getModelAttributeOptions($className, $propertyName);
		}
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return boolean
	 */
	public function isRelation($className, $propertyName) {
		return FALSE;
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return NULL|AbstractRelationAttribute
	 */
	public function getRelation($className, $propertyName) {

	}
}