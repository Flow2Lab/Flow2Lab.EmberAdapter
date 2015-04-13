<?php
namespace Flow2Lab\EmberAdapter\Configuration\Source;

use Flow2Lab\EmberAdapter\Annotations\AbstractRelationAttribute;
use TYPO3\Flow\Annotations as Flow;

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
interface ModelConfigurationSourceInterface {

	/**
	 * @return integer Priority of the configuration
	 */
	public function getPriority();

	/**
	 * @param object $object
	 * @return string
	 */
	public function getClassNameByObject($object);

	/**
	 * @param string $className
	 * @return boolean
	 */
	public function isClassEmberModel($className);

	/**
	 * @param object $object
	 * @return string Ember model name
	 * @throws \InvalidArgumentException If the given object is not annotated as ember model
	 */
	public function getModelNameByObject($object);

	/**
	 * @param string $className
	 * @return array<string>
	 */
	public function getModelPropertyNames($className);

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeName($className, $propertyName);

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeType($className, $propertyName);

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function getModelAttributeOptions($className, $propertyName);

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return boolean
	 */
	public function isRelation($className, $propertyName);

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return NULL|AbstractRelationAttribute
	 */
	public function getRelation($className, $propertyName);

}