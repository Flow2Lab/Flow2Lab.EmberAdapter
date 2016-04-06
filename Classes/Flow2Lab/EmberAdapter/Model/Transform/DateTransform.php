<?php
namespace Flow2Lab\EmberAdapter\Model\Transform;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class DateTransform extends AbstractTransform {

	/**
	 * @var string
	 */
	protected $attributeType = 'Flow2Lab\\EmberAdapter\\Model\\Attribute\\DateAttribute';

	/**
	 * Serializes any the value to match the ember attributes requirements
	 *
	 * @param mixed $value
	 * @return mixed
	 * @throws \InvalidArgumentException if the value cannot be serialized
	 */
	public function serialize($value) {
		if ($value instanceof \DateTime) {
			return $value->format(\DateTime::ISO8601);
		}
		return NULL;
	}

	/**
	 * Deserializes any value to match the flow property requirements
	 *
	 * @param mixed $value
	 * @return mixed
	 * @throws \InvalidArgumentException if the value cannot be deserialized
	 */
	public function deserialize($value) {
		return new \DateTime($value);
	}

}