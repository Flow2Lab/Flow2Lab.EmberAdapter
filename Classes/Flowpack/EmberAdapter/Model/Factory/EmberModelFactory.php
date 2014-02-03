<?php
namespace Flowpack\EmberAdapter\Model\Factory;

use Flowpack\EmberAdapter\Model\EmberModelInterface;
use Flowpack\EmberAdapter\Model\GenericEmberModel;
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

		$model = new GenericEmberModel($modelName, $modelIdentifier);

		foreach ($this->reflectionService->getModelPropertyNames($className) as $propertyName) {
			$attributeName = $this->reflectionService->getModelAttributeName($className, $propertyName);
			$attributeType = $this->reflectionService->getModelAttributeType($className, $propertyName);
			$attributeValue = ObjectAccess::getProperty($domainModel, $propertyName, TRUE);
			$attributeOptions = $this->reflectionService->getModelAttributeOptions($className, $propertyName);

			if ($this->reflectionService->isRelation($className, $propertyName) === FALSE) {
				$attribute = $this->attributeFactory->createByType($attributeType, $attributeName, $attributeValue, $attributeOptions);
				$model->addAttribute($attribute);
			} else {
				// todo: convert relations to ids
				// todo: can call itself basically
			}
		}

		return $model;
	}

}