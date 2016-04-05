<?php
namespace Flow2Lab\EmberAdapter\Model\Factory;

use Flow2Lab\EmberAdapter\Model\Attribute\AbstractAttribute;
use Flow2Lab\EmberAdapter\Model\Attribute\BooleanAttribute;
use Flow2Lab\EmberAdapter\Model\Attribute\DateAttribute;
use Flow2Lab\EmberAdapter\Model\Attribute\NumberAttribute;
use Flow2Lab\EmberAdapter\Model\Attribute\RawAttribute;
use Flow2Lab\EmberAdapter\Model\Attribute\StringAttribute;
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
				return new BooleanAttribute($attributeName, $attributeValue, $attributeOptions);
			case 'date':
				return new DateAttribute($attributeName, $attributeValue, $attributeOptions);
			case 'number':
				return new NumberAttribute($attributeName, $attributeValue, $attributeOptions);
			case 'raw':
				return new RawAttribute($attributeName, $attributeValue, $attributeOptions);
			case 'string':
			default:
				return new StringAttribute($attributeName, $attributeValue, $attributeOptions);
		}
	}

}