<?php
namespace Flowpack\EmberAdapter\Model\Transform;

use TYPO3\Flow\Annotations as Flow;

/**
 * Does not transform any values (noop).
 * @Flow\Scope("singleton")
 */
class RawTransform extends AbstractTransform {

	/**
	 * @var string
	 */
	protected $attributeType = 'Flowpack\\EmberAdapter\\Model\\Attribute\\Raw';

	/**
	 * Serializes any the value to match the ember attributes requirements
	 *
	 * @param mixed $value
	 * @return mixed
	 * @throws \InvalidArgumentException if the value cannot be serialized
	 */
	public function serialize($value) {
		return $value;
	}

	/**
	 * Deserializes any value to match the flow property requirements
	 *
	 * @param mixed $value
	 * @return mixed
	 * @throws \InvalidArgumentException if the value cannot be deserialized
	 */
	public function deserialize($value) {
		return $value;
	}

}