<?php
namespace Flowpack\EmberAdapter\Mvc\View;

use Flowpack\EmberAdapter\Model\EmberModelInterface;
use Flowpack\EmberAdapter\Model\Factory\AttributeFactory;
use Flowpack\EmberAdapter\Model\GenericEmberModel;
use Flowpack\EmberAdapter\Utility\EmberInflector;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\View\AbstractView;
use TYPO3\Flow\Mvc\View\JsonView;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Reflection\ObjectAccess;

class EmberView extends AbstractView {

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var \Flowpack\EmberAdapter\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @Flow\Inject
	 * @var AttributeFactory
	 */
	protected $attributeFactory;

	/**
	 * @var array<EmberModelInterface>
	 */
	protected $models = array();

	/**
	 * @return string The JSON encoded variables
	 */
	public function render() {
		$this->controllerContext->getResponse()->setHeader('Content-Type', 'application/json');
		$this->transformValue($this->variables);

		$renderedModels = $this->renderArray();
		return json_encode($renderedModels);
	}

	/**
	 * @param array $value
	 */
	protected function transformValue($value) {
		if (is_array($value) || $value instanceof \ArrayAccess) {
			foreach ($value as $element) {
				$this->transformValue($element);
			}
		} elseif (is_object($value)) {
			$this->transformObject($value);
		}
	}

	/**
	 * Groups all ember models by name and converts them to an array.
	 *
	 * @return array
	 */
	protected function renderArray() {
		$groupedModels = array();
		$convertedModels = array();

		/** @var EmberModelInterface $model */
		foreach ($this->models as $model) {
			if (!isset($groupedModels[$model->getName()])) {
				$groupedModels[$model->getName()] = array();
			}

			$groupedModels[$model->getName()][] = $model;
		}

		foreach ($groupedModels as $modelName => $models) {
			if (count($models) === 1) {
				$convertedModels[$modelName] = $this->convertModelToArray($models[0]);
			} else {
				$pluralizedModelName = lcfirst(EmberInflector::pluralize($modelName));
				$convertedModels[$pluralizedModelName] = array();

				foreach ($models as $model) {
					$convertedModels[$pluralizedModelName][] = $this->convertModelToArray($model);
				}
			}
		}

		return $convertedModels;
	}

	/**
	 * @param EmberModelInterface $emberModel
	 * @return array
	 */
	protected function convertModelToArray(EmberModelInterface $emberModel) {
		$attributes = $emberModel->getAttributesArray();
		$attributes['id'] = $emberModel->getId();

		return $attributes;
	}

	/**
	 * Traverses the given object structure in order to transform it into models.
	 *
	 * @param object $object Object to traverse
	 */
	protected function transformObject($object) {
		$className = $this->reflectionService->getClassNameByObject($object);

		if ($this->reflectionService->isClassEmberModel($className) === FALSE) {
			return;
		}

		$modelName = $this->reflectionService->getModelNameByObject($object);
		$modelIdentifier = $this->persistenceManager->getIdentifierByObject($object);

		$model = new GenericEmberModel($modelName, $modelIdentifier);

		foreach ($this->reflectionService->getModelPropertyNames($className) as $propertyName) {
			$attributeName = $this->reflectionService->getModelAttributeName($className, $propertyName);
			$attributeType = $this->reflectionService->getModelAttributeType($className, $propertyName);
			$attributeValue = ObjectAccess::getProperty($object, $propertyName, TRUE);

			if ($this->reflectionService->isRelation($className, $propertyName) === FALSE) {
				$attribute = $this->attributeFactory->createByType($attributeType, $attributeName, $attributeValue);
				$model->addAttribute($attribute);
			} else {
				// todo: convert relations to ids
				// todo: can call itself basically
			}
		}

		$this->models[] = $model;
	}

}