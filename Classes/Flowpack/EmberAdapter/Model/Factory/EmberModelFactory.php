<?php
namespace Flowpack\EmberAdapter\Model\Factory;

use Doctrine\Common\Collections\Collection;
use Flowpack\EmberAdapter\Annotations as Ember;
use Flowpack\EmberAdapter\Model\EmberModelInterface;
use Flowpack\EmberAdapter\Model\GenericEmberModel;
use Flowpack\EmberAdapter\Model\Relation\AbstractRelation;
use Flowpack\EmberAdapter\Model\Relation\BelongsTo;
use Flowpack\EmberAdapter\Model\Relation\HasMany;
use Flowpack\EmberAdapter\Reflection\ReflectionService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * @Flow\Scope("singleton")
 */
class EmberModelFactory {

	/**
	 * @Flow\Inject
	 * @var ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var AttributeFactory
	 */
	protected $attributeFactory;

	/**
	 * todo: find a way to not recursively load bidirectional relations.
	 *
	 * @param object $domainModel
	 * @return NULL|EmberModelInterface
	 */
	public function create($domainModel) {
		$className = $this->reflectionService->getClassNameByObject($domainModel);

		if ($this->reflectionService->isClassEmberModel($className) === FALSE) {
			return NULL;
		}

		$modelName = $this->reflectionService->getModelNameByObject($domainModel);
		$modelIdentifier = $this->persistenceManager->getIdentifierByObject($domainModel);

		// todo: add optional parameter class name to @Ember\Model annotation (must implement EmberModelInterface)
		$model = new GenericEmberModel($modelName, $modelIdentifier);

		foreach ($this->reflectionService->getModelPropertyNames($className) as $propertyName) {
			if (ObjectAccess::isPropertyGettable($domainModel, $propertyName)) {
				$attributeName = $this->reflectionService->getModelAttributeName($className, $propertyName);
				$attributeType = $this->reflectionService->getModelAttributeType($className, $propertyName);
				$attributeValue = ObjectAccess::getProperty($domainModel, $propertyName);
				$attributeOptions = $this->reflectionService->getModelAttributeOptions($className, $propertyName);

				if ($this->reflectionService->isRelation($className, $propertyName) === FALSE) {
					$attribute = $this->attributeFactory->createByType($attributeType, $attributeName, $attributeValue, $attributeOptions);
					$model->addAttribute($attribute);
				} else {
					// Relation attributes can be omitted if they are NULL or contain no items
					if ($attributeValue !== NULL && (!$attributeValue instanceof Collection || $attributeValue->count() > 0)) {
						$relationAnnotation = $this->reflectionService->getRelation($className, $propertyName);
						$relation = $this->handleRelation($relationAnnotation, $attributeName, $attributeValue, $model);
						$model->addRelation($relation);
					}
				}
			}
		}

		return $model;
	}

	/**
	 * @param Ember\AbstractRelationAttribute $relation
	 * @param string $attributeName
	 * @param mixed $attributeValue
	 * @return AbstractRelation
	 */
	protected function handleRelation(Ember\AbstractRelationAttribute $relation, $attributeName, $attributeValue) {
		if ($relation->type === Ember\AbstractRelationAttribute::RELATION_BELONGS_TO) {
			return $this->handleBelongsToRelation($relation, $attributeName, $attributeValue);
		} else {
			return $this->handleHasManyRelation($relation, $attributeName, $attributeValue);
		}
	}

	/**
	 * @param Ember\AbstractRelationAttribute $relation
	 * @param string $attributeName
	 * @param mixed $attributeValue
	 * @return AbstractRelation
	 */
	protected function handleBelongsToRelation($relation, $attributeName, $attributeValue) {
		$belongsToRelation = new BelongsTo($attributeName, $relation->sideload);

		if ($relation->sideload === TRUE) {
			$relatedModel = $this->create($attributeValue);
			$belongsToRelation->setRelatedModel($relatedModel);
		} else {
			$relatedModelsIdentifier = $this->persistenceManager->getIdentifierByObject($attributeValue);
			$belongsToRelation->setId($relatedModelsIdentifier);
		}

		return $belongsToRelation;
	}

	/**
	 * @param Ember\AbstractRelationAttribute $relation
	 * @param $attributeName
	 * @param $attributeValue
	 * @return AbstractRelation
	 */
	protected function handleHasManyRelation($relation, $attributeName, $attributeValue) {
		$hasManyRelation = new HasMany($attributeName, $relation->sideload);

		if ($relation->sideload === TRUE) {
			$relatedModels = array();
			foreach ($attributeValue as $relatedDomainModel) {
				$relatedModel = $this->create($relatedDomainModel);
				$relatedModels[] = $relatedModel;
			}
			$hasManyRelation->setRelatedModels($relatedModels);
		} else {
			$relatedModelIdentifiers = array();
			foreach ($attributeValue as $relatedDomainModel) {
				$relatedModelIdentifiers[] = $this->persistenceManager->getIdentifierByObject($relatedDomainModel);
			}
			$hasManyRelation->setIds($relatedModelIdentifiers);
		}

		return $hasManyRelation;
	}

}