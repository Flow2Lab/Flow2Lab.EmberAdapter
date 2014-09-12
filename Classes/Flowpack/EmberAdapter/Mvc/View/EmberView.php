<?php
namespace Flowpack\EmberAdapter\Mvc\View;

use Flowpack\EmberAdapter\Model\EmberModelInterface;
use Flowpack\EmberAdapter\Model\Factory\EmberModelFactory;
use Flowpack\EmberAdapter\Model\Relation\AbstractRelation;
use Flowpack\EmberAdapter\Model\Relation\BelongsTo;
use Flowpack\EmberAdapter\Model\Relation\HasMany;
use Flowpack\EmberAdapter\Model\RelationCollection;
use Flowpack\EmberAdapter\Model\Serializer\ArraySerializer;
use Flowpack\EmberAdapter\Utility\EmberInflector;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\View\AbstractView;
use TYPO3\Flow\Mvc\View\JsonView;
use TYPO3\Flow\Reflection\ObjectAccess;

class EmberView extends AbstractView {

	/**
	 * @Flow\Inject
	 * @var EmberModelFactory
	 */
	protected $emberModelFactory;

	/**
	 * @Flow\Inject
	 * @var ArraySerializer
	 */
	protected $emberModelSerializer;

	/**
	 * @var array<EmberModelInterface>
	 */
	protected $models = array();

	/**
	 * Contains an array of already loaded models
	 *
	 * @var array<string>
	 */
	protected $addedModels = array();

	/**
	 * @return string The JSON encoded variables
	 */
	public function render() {
		$this->transformValue($this->variables);
		$renderedModels = $this->renderArray();
		if (empty($renderedModels)) {
			$modelName = $this->controllerContext->getRequest()->hasArgument('modelName');
			$emptyObject = new $modelName();
			if ($emptyObject->getResourceArgumentName() !== '') {
				$emptyModel = array($emptyObject->getResourceArgumentName() => array());
				return json_encode((object) $emptyModel);
			} else {
				$this->throwStatus(404);
			}
		}
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
		} else if (is_object($value)) {
			$this->transformObject($value);
		}
	}

	/**
	 * Traverses the given object structure in order to transform it into models.
	 *
	 * @param object $object Object to traverse
	 */
	protected function transformObject($object) {
		$model = $this->emberModelFactory->create($object);

		if ($model !== NULL) {
			$this->addModel($model);
		}
	}

	/**
	 * @param EmberModelInterface $emberModel
	 */
	protected function addModel(EmberModelInterface $emberModel = NULL) {
		if ($emberModel === NULL || $this->modelIsAlreadyAdded($emberModel) === TRUE) {
			return;
		}

		$this->models[] = $emberModel;
		$this->markModelAsAdded($emberModel);
		$this->addRelations($emberModel->getRelations());
	}

	/**
	 * @param EmberModelInterface $emberModel
	 * @return boolean
	 */
	protected function modelIsAlreadyAdded(EmberModelInterface $emberModel) {
		if (array_key_exists($emberModel->getName(), $this->addedModels) === FALSE) {
			return FALSE;
		} else {
			return in_array($emberModel->getId(), $this->addedModels[$emberModel->getName()]);
		}
	}

	/**
	 * @param EmberModelInterface $emberModel
	 */
	protected function markModelAsAdded(EmberModelInterface $emberModel) {
		if (array_key_exists($emberModel->getName(), $this->addedModels) === FALSE) {
			$this->addedModels[$emberModel->getName()] = array();
		}

		$this->addedModels[$emberModel->getName()][] = $emberModel->getId();
	}

	/**
	 * @param RelationCollection $relations
	 */
	protected function addRelations(RelationCollection $relations) {
		/** @var BelongsTo|HasMany $relation */
		foreach ($relations as $relation) {
			if ($relation->isSideloaded()) {
				if ($relation instanceof BelongsTo) {
					$this->addModel($relation->getRelatedModel());
				} else {
					foreach ($relation->getRelatedModels() as $relatedModel) {
						$this->addModel($relatedModel);
					}
				}
			}
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
					// Determine the action to give a proper response:
					// An array of 1 object or 1 object
				if ($this->controllerContext->getRequest()->getControllerActionName() === 'list') {
					$pluralizedModelName = lcfirst(EmberInflector::pluralize($modelName));
					$convertedModels[$pluralizedModelName] = array();
					$convertedModels[$pluralizedModelName][] = $this->emberModelSerializer->serialize($models[0]);
				} else {
					$singularModelName = lcfirst($modelName);
					$convertedModels[$singularModelName] = $this->emberModelSerializer->serialize($models[0]);
				}
			} else {
				$pluralizedModelName = lcfirst(EmberInflector::pluralize($modelName));
				$convertedModels[$pluralizedModelName] = array();

				foreach ($models as $model) {
					$convertedModels[$pluralizedModelName][] = $this->emberModelSerializer->serialize($model);
				}
			}
		}

		return $convertedModels;
	}

}