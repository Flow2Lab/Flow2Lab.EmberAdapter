<?php
namespace Flow2Lab\EmberAdapter\Mvc\View;

use Flow2Lab\EmberAdapter\Model\EmberModelInterface;
use Flow2Lab\EmberAdapter\Model\Factory\EmberModelFactory;
use Flow2Lab\EmberAdapter\Model\Relation\BelongsTo;
use Flow2Lab\EmberAdapter\Model\Relation\HasMany;
use Flow2Lab\EmberAdapter\Model\RelationCollection;
use Flow2Lab\EmberAdapter\Model\Serializer\ArraySerializer;
use Flow2Lab\EmberAdapter\Utility\EmberInflector;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\View\AbstractView;

/**
 * Class EmberView
 *
 * @package Flow2Lab\EmberAdapter\Mvc\View
 */
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
	 * @var EmberModelInterface[]
	 */
	protected $models = array();

	/**
	 * Contains an array of already loaded models
	 *
	 * @var string[]
	 */
	protected $addedModels = array();

	/**
	 * Array of converted Models
	 * @var array
	 */
	protected $renderedModels = array();

	/**
	 * @var string
	 */
	protected $rootModel = NULL;

	/**
	 * @return string The JSON encoded variables
	 */
	public function render() {
		unset($this->variables['settings']);

		$this->detectRootModel($this->variables);

		$this->controllerContext->getResponse()->setHeader('Content-Type', 'application/json');
		$this->transformValue($this->variables);
		$this->renderArray();
		return json_encode($this->renderedModels);
	}

	/**
	 * When displaying a single model, the key must not be pluralized while any sideloaded
	 * entries have to maintain their pluralized key.
	 *
	 * This can be detected when looking at the given data. If the given data consists of
	 * one item, and that item is an object, it is considered to be a single view.
	 *
	 * Example:
	 * $this->view->assign('someIrrelevantKey', $object);
	 *
	 * Will result in {'modelName': { serialized model }}
	 *
	 * $this->view->assign('someIrrelevantKey', [$object1, $object2, $object3]);
	 *
	 * Will result in {'modelNames': [{...}, {...}]}
	 *
	 * @param array $models
	 */
	protected function detectRootModel($models) {
		if (count($models) !== 1) {
			return;
		}

		$model = current($models);

		if (is_array($model) === TRUE) {
			return;
		}

		// load the QueryResult
		if ($model instanceof \Iterator) {
			$model = iterator_to_array($model);
			if (count($model) !== 1) {
				return;
			}

			$model = array_shift($model);
		}

		$rootModel = $this->emberModelFactory->create($model);

		if ($rootModel !== NULL) {
			$this->rootModel = $rootModel->getName();
		}
	}

	/**
	 * @param mixed $value
	 * @param string $modelName
	 */
	protected function transformValue($value, $modelName = '') {
		if (is_array($value) || $value instanceof \ArrayAccess) {
			if ($modelName !== '') {
				$this->renderedModels[lcfirst(EmberInflector::pluralize($modelName))] = array();
			}
			foreach ($value as $modelKey => $element) {
				$this->transformValue($element, $modelKey);
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
					if (count($relation->getRelatedModels()) > 0) {
						foreach ($relation->getRelatedModels() as $relatedModel) {
							$this->addModel($relatedModel);
						}
					}
				}
			}
		}
	}

	/**
	 * Groups all ember models by name and converts them to an array.
	 *
	 */
	protected function renderArray() {
		$groupedModels = array();

		/** @var EmberModelInterface $model */
		foreach ($this->models as $model) {
			if (!isset($groupedModels[$model->getName()])) {
				$groupedModels[$model->getName()] = array();
			}

			$groupedModels[$model->getName()][] = $model;
		}

		foreach ($groupedModels as $modelName => $models) {
			if (count($models) === 0) {
				continue;
			}

			$modelName = lcfirst($modelName);

			if (count($models) === 1 && $modelName === lcfirst($this->rootModel)) {
				$this->renderedModels[$modelName] = $this->emberModelSerializer->serialize($models[0]);
			} else {
				$pluralizedModelName = EmberInflector::pluralize($modelName);
				$this->renderedModels[$pluralizedModelName] = array();

				foreach ($models as $model) {
					$this->renderedModels[$pluralizedModelName][] = $this->emberModelSerializer->serialize($model);
				}
			}
		}
	}

}