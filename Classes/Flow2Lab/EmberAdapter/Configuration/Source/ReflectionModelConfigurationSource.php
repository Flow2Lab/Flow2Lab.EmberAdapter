<?php
namespace Flow2Lab\EmberAdapter\Configuration\Source;

use Flow2Lab\EmberAdapter\Annotations\AbstractRelationAttribute;
use Flow2Lab\EmberAdapter\Annotations\Attribute;
use Flow2Lab\EmberAdapter\Annotations\BelongsTo;
use Flow2Lab\EmberAdapter\Annotations\HasMany;
use Flow2Lab\EmberAdapter\Annotations\Model;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Annotations\ValueObject;
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
class ReflectionModelConfigurationSource implements ModelConfigurationSourceInterface {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * This is the fallback configuration source
	 *
	 * @return integer Priority of the configuration
	 */
	public function getPriority() {
		return 1;
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
		return $this->reflectionService->isClassAnnotatedWith($className, Model::class);
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

		/** @var Model $model */
		$model = $this->reflectionService->getClassAnnotation($className, Model::class);

		if ($model->name !== NULL) {
			return $model->name;
		} else {
			$classReflection = new \ReflectionClass($className);
			return $classReflection->getShortName();
		}
	}

	/**
	 * @param string $className
	 * @return string[]
	 */
	public function getModelPropertyNames($className) {
		return $this->reflectionService->getPropertyNamesByAnnotation($className, Attribute::class);
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeName($className, $propertyName) {
		$propertyAnnotation = $this->getAttributeAnnotation($className, $propertyName);

		if ($propertyAnnotation->name !== NULL) {
			return $propertyAnnotation->name;
		} else {
			return $propertyName;
		}
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeType($className, $propertyName) {
		$propertyAnnotation = $this->getAttributeAnnotation($className, $propertyName);
		return $propertyAnnotation->type;
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeOptions($className, $propertyName) {
		$propertyAnnotation = $this->getAttributeAnnotation($className, $propertyName);
		return $propertyAnnotation->options;
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return Attribute
	 * @throws \InvalidArgumentException
	 */
	protected function getAttributeAnnotation($className, $propertyName) {
		/** @var Attribute $propertyAnnotation */
		$propertyAnnotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, Attribute::class);

		if ($propertyAnnotation === NULL) {
			throw new \InvalidArgumentException('Given property is not annotated as ember attribute.', 1390666390);
		}
		return $propertyAnnotation;
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return boolean
	 */
	public function isRelation($className, $propertyName) {
		return (
			$this->reflectionService->isPropertyAnnotatedWith($className, $propertyName, BelongsTo::class)
			|| $this->reflectionService->isPropertyAnnotatedWith($className, $propertyName, HasMany::class)
		);
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

		if ($this->reflectionService->isPropertyAnnotatedWith($className, $propertyName, BelongsTo::class)) {
			$relation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, BelongsTo::class);
		} else {
			$relation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, HasMany::class);
		}

		// Parse the properties type and check if it is a value objects.
		// Value objects have to be sideloaded since it's impossible to provide a REST resource for them.
		$tags = $this->reflectionService->getPropertyTagValues($className, $propertyName, 'var');
		$tag = array_shift($tags);
		if ($tag !== NULL) {
			$parsedType = TypeHandling::parseType($tag);
			$propertyType = ($parsedType['elementType']) ?: $parsedType['type'];

			if ($this->reflectionService->isClassAnnotatedWith($propertyType, ValueObject::class)) {
				$relation->sideload = TRUE;
			}
		}

		return $relation;
	}

}