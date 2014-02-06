<?php
namespace Flowpack\EmberAdapter\Mvc\View;

use Flowpack\EmberAdapter\Model\EmberModelInterface;
use Flowpack\EmberAdapter\Model\Factory\EmberModelFactory;
use Flowpack\EmberAdapter\Model\Relation\AbstractRelation;
use Flowpack\EmberAdapter\Model\Relation\BelongsTo;
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
	 * @return string The JSON encoded variables
	 */
	public function render() {
		//$this->controllerContext->getResponse()->setHeader('Content-Type', 'application/json');
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
	 * Traverses the given object structure in order to transform it into models.
	 *
	 * @param object $object Object to traverse
	 */
	protected function transformObject($object) {
		$model = $this->emberModelFactory->create($object);

		if ($model !== NULL) {
			$this->addModelAndRelations($model);
		}
	}

	/**
	 * @param EmberModelInterface $emberModel
	 */
	protected function addModelAndRelations(EmberModelInterface $emberModel) {
		$this->models[] = $emberModel;
		foreach ($emberModel->getRelations() as $relation) {
			// todo: ugglyyyyy...
			/** @var AbstractRelation $relation */
			if ($relation->isSideloaded()) {
				if ($relation instanceof BelongsTo && $relation->getRelatedModel() !== NULL) {
					$this->addModelAndRelations($relation->getRelatedModel());
				} else {
					foreach ($relation->getRelatedModels() as $relatedModel) {
						$this->addModelAndRelations($relatedModel);
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
				$singularModelName = lcfirst($modelName);
				$convertedModels[$singularModelName] = $this->emberModelSerializer->serialize($models[0]);
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