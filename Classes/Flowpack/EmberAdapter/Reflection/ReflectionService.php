<?php
namespace Flowpack\EmberAdapter\Reflection;

use Flowpack\EmberAdapter\Annotations\AbstractRelationAttribute;
use Flowpack\EmberAdapter\Annotations\Attribute;
use Flowpack\EmberAdapter\Annotations\Model;
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
class ReflectionService {

	const ANNOTATION_MODEL = 'Flowpack\\EmberAdapter\\Annotations\\Model';
	const ANNOTATION_ATTRIBUTE = 'Flowpack\\EmberAdapter\\Annotations\\Attribute';
	const ANNOTATION_BELONGS_TO = 'Flowpack\\EmberAdapter\\Annotations\\BelongsTo';
	const ANNOTATION_HAS_MANY = 'Flowpack\\EmberAdapter\\Annotations\\HasMany';

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
		return $this->reflectionService->isClassAnnotatedWith($className, self::ANNOTATION_MODEL);
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
		$model = $this->reflectionService->getClassAnnotation($className, self::ANNOTATION_MODEL);

		if ($model->name !== NULL) {
			return $model->name;
		} else {
			$classReflection = new \ReflectionClass($className);
			return $classReflection->getShortName();
		}
	}

	/**
	 * @param string $className
	 * @return array<string>
	 */
	public function getModelPropertyNames($className) {
		return $this->reflectionService->getPropertyNamesByAnnotation($className, self::ANNOTATION_ATTRIBUTE);
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
		$propertyAnnotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, self::ANNOTATION_ATTRIBUTE);

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
			$this->reflectionService->isPropertyAnnotatedWith($className, $propertyName, self::ANNOTATION_BELONGS_TO)
			|| $this->reflectionService->isPropertyAnnotatedWith($className, $propertyName, self::ANNOTATION_HAS_MANY)
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