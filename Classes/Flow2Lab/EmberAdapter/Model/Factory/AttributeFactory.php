<?php
namespace Flow2Lab\EmberAdapter\Model\Factory;

use Flow2Lab\EmberAdapter\Model\Attribute\AbstractAttribute;
use Flow2Lab\EmberAdapter\Model\Attribute\Boolean;
use Flow2Lab\EmberAdapter\Model\Attribute\Date;
use Flow2Lab\EmberAdapter\Model\Attribute\Number;
use Flow2Lab\EmberAdapter\Model\Attribute\Raw;
use Flow2Lab\EmberAdapter\Model\Attribute\String;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class AttributeFactory {

	/**
	 * @todo: Replace this with a real implementation; get all classes inheriting AbstractAttribute and check class name
	 *        or add a supported type and interface.
	 *
	 * @param string $attributeType
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @param array $attributeOptions
	 * @return AbstractAttribute
	 */
	public function createByType($attributeType, $attributeName, $attributeValue = '', $attributeOptions = array()) {
		switch ($attributeType) {
			case 'boolean':
				return new Boolean($attributeName, $attributeValue, $attributeOptions);
			case 'date':
				return new Date($attributeName, $attributeValue, $attributeOptions);
			case 'number':
				return new Number($attributeName, $attributeValue, $attributeOptions);
			case 'raw':
				return new Raw($attributeName, $attributeValue, $attributeOptions);
			case 'string':
			default:
				return new String($attributeName, $attributeValue, $attributeOptions);
		}
	}

}