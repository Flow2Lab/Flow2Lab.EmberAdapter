<?php
namespace Flowpack\EmberAdapter\Model\Serializer;

use Flowpack\EmberAdapter\Model\Attribute\AbstractAttribute;
use Flowpack\EmberAdapter\Model\EmberModelInterface;
use Flowpack\EmberAdapter\Model\Relation\AbstractRelation;
use Flowpack\EmberAdapter\Model\Relation\BelongsTo;
use Flowpack\EmberAdapter\Model\Relation\HasMany;
use Flowpack\EmberAdapter\Model\Serializer\Exception\UnknownRelationException;
use Flowpack\EmberAdapter\Model\Transform\Exception\DuplicateTransformException;
use Flowpack\EmberAdapter\Model\Transform\Exception\MissingTransformException;
use Flowpack\EmberAdapter\Model\Transform\TransformInterface;
use TYPO3\Flow\Annotations as Flow;

/**
 * Serializes any given ember model to array.
 *
 * @Flow\Scope("singleton")
 */
class ArraySerializer {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $transforms = array();

	/**
	 * Initialize all known transforms.
	 *
	 * @return void
	 * @throws DuplicateTransformException
	 */
	public function initializeObject() {
		$transformClassNames = static::getTransformImplementationClassNames($this->objectManager);
		foreach ($transformClassNames as $transformClassName) {
			/** @var TransformInterface $transform */
			$transform = $this->objectManager->get($transformClassName);
			if (isset($this->transforms[$transform->getAttributeType()][$transform->getPriority()])) {
				throw new DuplicateTransformException('There is more than one transform that can transform the attribute type ' . $transform->getAttributeType() . ' with priority ' . $transform->getPriority() . ' and ' . get_class($transform) . '.', 1391459078);
			}
			$this->transforms[$transform->getAttributeType()][$transform->getPriority()] = $transform;
		}
	}

	/**
	 * Returns all class names implementing the TransformInterface.
	 *
	 * @param \TYPO3\Flow\Object\ObjectManagerInterface $objectManager
	 * @return array Array of available transform implementations
	 * @Flow\CompileStatic
	 */
	static public function getTransformImplementationClassNames($objectManager) {
		$reflectionService = $objectManager->get('TYPO3\Flow\Reflection\ReflectionService');
		return $reflectionService->getAllImplementationClassNamesForInterface('Flowpack\\EmberAdapter\\Model\\Transform\\TransformInterface');
	}

	/**
	 * Serializes the ember model by transforming all of it's attributes and including the identifier.
	 *
	 * @param EmberModelInterface $model
	 * @return array
	 */
	public function serialize(EmberModelInterface $model) {
		$serializedModel = array();

		$serializedModel['id'] = $model->getId();
		foreach ($model->getAttributes() as $attribute) {
			/** @var AbstractAttribute $attribute */
			$serializedModel[$attribute->getName()] = $this->serializeAttribute($attribute);
		}

		foreach ($model->getRelations() as $relation) {
			/** @var AbstractRelation $relation */
			$serializedModel[$relation->getName()] = $this->serializeRelation($relation);
		}

		return $serializedModel;
	}

	/**
	 * @param AbstractAttribute $attribute
	 * @return mixed
	 */
	protected function serializeAttribute(AbstractAttribute $attribute) {
		$transform = $this->findTransformForAttribute($attribute);
		return $transform->serialize($attribute->getValue(), $attribute->getOptions());
	}

	/**
	 * @param AbstractAttribute $attribute
	 * @return TransformInterface
	 * @throws \Flowpack\EmberAdapter\Model\Transform\Exception\MissingTransformException
	 */
	protected function findTransformForAttribute(AbstractAttribute $attribute) {
		$attributeType = get_class($attribute);

		if (!isset($this->transforms[$attributeType])) {
			throw new MissingTransformException('No suitable transform available to transform the attribute type ' . $attributeType . '.', 1391459659);
		}

		$availableTransforms = $this->transforms[$attributeType];

		krsort($availableTransforms);
		reset($availableTransforms);

		$transformWithHighestPriority = array_shift($availableTransforms);
		return $transformWithHighestPriority;
	}

	/**
	 * @param AbstractRelation $relation
	 * @return array|string
	 * @throws Exception\UnknownRelationException
	 */
	protected function serializeRelation(AbstractRelation $relation) {
		if ($relation instanceof BelongsTo) {
			return $relation->getId();
		} else if ($relation instanceof HasMany) {
			return $relation->getIds();
		}

		throw new UnknownRelationException('Currently only BelongsTo and HasMany relations are supported.', 1395657631);
	}

}