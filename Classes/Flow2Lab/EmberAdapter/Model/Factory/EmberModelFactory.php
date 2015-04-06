<?php
namespace Flow2Lab\EmberAdapter\Model\Factory;

use Doctrine\Common\Collections\Collection;
use Flow2Lab\EmberAdapter\Annotations as Ember;
use Flow2Lab\EmberAdapter\Model\EmberModelInterface;
use Flow2Lab\EmberAdapter\Model\GenericEmberModel;
use Flow2Lab\EmberAdapter\Model\Relation\AbstractRelation;
use Flow2Lab\EmberAdapter\Model\Relation\BelongsTo;
use Flow2Lab\EmberAdapter\Model\Relation\HasMany;
use Flow2Lab\EmberAdapter\Configuration\ModelConfigurationManager;
use Flow2Lab\EmberAdapter\Utility\EmberDataUtility;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * @Flow\Scope("singleton")
 */
class EmberModelFactory {

	/**
	 * @Flow\Inject
	 * @var ModelConfigurationManager
	 */
	protected $modelConfigurationManager;

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
		$className = $this->modelConfigurationManager->getClassNameByObject($domainModel);

		if ($this->modelConfigurationManager->isClassEmberModel($className) === FALSE) {
			return NULL;
		}

		$modelName = $this->modelConfigurationManager->getModelNameByObject($domainModel);
		$modelIdentifier = $this->modelConfigurationManager->getModelIdentifierByObject($domainModel);

		// todo: add optional parameter class name to @Ember\Model annotation (must implement EmberModelInterface)
		$model = new GenericEmberModel($modelName, $modelIdentifier);

		foreach ($this->modelConfigurationManager->getModelPropertyNames($className) as $propertyName) {
			if (ObjectAccess::isPropertyGettable($domainModel, $propertyName)) {
				$attributeName = EmberDataUtility::uncamelize($this->modelConfigurationManager->getModelAttributeName($className, $propertyName));
				$attributeType = $this->modelConfigurationManager->getModelAttributeType($className, $propertyName);
				$attributeValue = ObjectAccess::getProperty($domainModel, $propertyName);
				$attributeOptions = $this->modelConfigurationManager->getModelAttributeOptions($className, $propertyName);

				if ($this->modelConfigurationManager->isRelation($className, $propertyName) === FALSE) {
					$attribute = $this->attributeFactory->createByType($attributeType, $attributeName, $attributeValue, $attributeOptions);
					$model->addAttribute($attribute);
				} else {
					// Relation attributes can be omitted if they are NULL or contain no items
					if ($attributeValue !== NULL && (!$attributeValue instanceof Collection || $attributeValue->count() > 0)) {
						$relationAnnotation = $this->modelConfigurationManager->getRelation($className, $propertyName);
						$relation = $this->createRelation($relationAnnotation, $attributeName, $attributeValue, $model);
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
	protected function createRelation(Ember\AbstractRelationAttribute $relation, $attributeName, $attributeValue) {
		if ($relation->type === Ember\AbstractRelationAttribute::RELATION_BELONGS_TO) {
			return $this->createBelongsToRelation($relation, $attributeName, $attributeValue);
		} else {
			return $this->createHasManyRelation($relation, $attributeName, $attributeValue);
		}
	}

	/**
	 * @param Ember\AbstractRelationAttribute $relation
	 * @param string $attributeName
	 * @param mixed $attributeValue
	 * @return AbstractRelation
	 */
	protected function createBelongsToRelation($relation, $attributeName, $attributeValue) {
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
	protected function createHasManyRelation($relation, $attributeName, $attributeValue) {
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