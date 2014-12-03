<?php
namespace Flow2Lab\EmberAdapter\Backend;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\TypeHandling;

/**
 * Mind the naming!
 * ClassName: Name of the Flow class
 * PropertyName: Name of the Flow class property (can be the attribute name, but must not!)
 *
 * ModelName: Name of the ember model
 * AttributeName: Name of the attribute inside an ember model
 *
 * @Flow\Scope("singleton")
 */
class YamlModelConfigurationSource implements ModelConfigurationSourceInterface {

	const EmberModels = 'EmberModels';
	const BELONGS_TO = 'BelongsTo';
	const HAS_MANY = 'HasMany';

	/**
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 * @Flow\Inject
	 */
	protected $configurationManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

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
		foreach ($this->configurationManager->getConfiguration(self::EmberModels) as $modelName => $configuration) {
			if ($configuration['className'] === $className) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param object $object
	 * @return string Ember model name
	 * @throws \InvalidArgumentException If the given object is not annotated as ember model
	 */
	public function getModelNameByObject($object) {
		$className = $this->reflectionService->getClassNameByObject($object);

		if ($this->isClassEmberModel($className) === FALSE) {
			throw new \InvalidArgumentException('Given object is not an ember model.', 1390663864);
		}

		// TODO: Add possibility to set custom name
		$classReflection = new \ReflectionClass($className);
		return $classReflection->getShortName();
	}

	/**
	 * @param string $className
	 * @return array<string>
	 */
	public function getModelPropertyNames($className) {
		foreach ($this->configurationManager->getConfiguration(self::EmberModels) as $modelName => $configuration) {
			if ($configuration['className'] === $className) {
				return array_keys($configuration['properties']);
			}
		}
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeName($className, $propertyName) {
		$propertyConfiguration = $this->getAttributeConfiguration($className, $propertyName);

		return $propertyName;
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeType($className, $propertyName) {
		$propertyConfiguration = $this->getAttributeConfiguration($className, $propertyName);

		if (isset($propertyConfiguration['type'])) {
			return $propertyConfiguration['type'];
		}
		throw new \InvalidArgumentException('Given property has no configured ember type.', 1390666395);
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeOptions($className, $propertyName) {
		$propertyConfiguration = $this->getAttributeConfiguration($className, $propertyName);
		return isset($propertyConfiguration['options']) ? $propertyConfiguration['options'] : array();
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	protected function getAttributeConfiguration($className, $propertyName) {
		foreach ($this->configurationManager->getConfiguration(self::EmberModels) as $modelName => $configuration) {
			if ($configuration['className'] === $className && isset($configuration['properties'][$propertyName])) {
				return $configuration['properties'][$propertyName];
			}
		}

		throw new \InvalidArgumentException('Given property is not configured as ember attribute.', 1390666391);
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return boolean
	 */
	public function isRelation($className, $propertyName) {
		foreach ($this->configurationManager->getConfiguration(self::EmberModels) as $modelName => $configuration) {
			if ($configuration['className'] === $className && (isset($configuration[$propertyName][self::BELONGS_TO]) || isset($configuration[$propertyName][self::HAS_MANY]))) {
				return TRUE;
			}
		}
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return NULL|AbstractRelationAttribute
	 */
	public function getRelation($className, $propertyName) {
		if ($this->isRelation($className, $propertyName) === FALSE) {
			return NULL;
		}

		if ($this->reflectionService->isPropertyAnnotatedWith($className, $propertyName, self::ANNOTATION_BELONGS_TO)) {
			$relation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, self::ANNOTATION_BELONGS_TO);
		} else {
			$relation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, self::ANNOTATION_HAS_MANY);
		}

		// Parse the properties type and check if it is a value objects.
		// Value objects have to be sideloaded since it's impossible to provide a REST resource for them.
		$tags = $this->reflectionService->getPropertyTagValues($className, $propertyName, 'var');
		$tag = array_shift($tags);
		if ($tag !== NULL) {
			$parsedType = TypeHandling::parseType($tag);
			$propertyType = ($parsedType['elementType']) ?: $parsedType['type'];

			if ($this->reflectionService->isClassAnnotatedWith($propertyType, 'TYPO3\\Flow\\Annotations\\ValueObject')) {
				$relation->sideload = TRUE;
			}
		}

		return $relation;
	}

}