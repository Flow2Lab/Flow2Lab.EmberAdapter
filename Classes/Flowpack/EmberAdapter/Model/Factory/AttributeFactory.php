<?php
namespace Flowpack\EmberAdapter\Model\Factory;

use Flowpack\EmberAdapter\Model\Attribute\AbstractAttribute;
use Flowpack\EmberAdapter\Model\Attribute\Boolean;
use Flowpack\EmberAdapter\Model\Attribute\Date;
use Flowpack\EmberAdapter\Model\Attribute\Number;
use Flowpack\EmberAdapter\Model\Attribute\Raw;
use Flowpack\EmberAdapter\Model\Attribute\String;
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
	 * @return AbstractAttribute
	 */
	public function createByType($attributeType, $attributeName, $attributeValue = '') {
		switch ($attributeType) {
			case 'boolean':
				return new Boolean($attributeName, $attributeValue);
			case 'date':
				return new Date($attributeName, $attributeValue);
			case 'number':
				return new Number($attributeName, $attributeValue);
			case 'raw':
				return new Raw($attributeName, $attributeValue);
			case 'string':
			default:
				return new String($attributeName, $attributeValue);
		}
	}

}