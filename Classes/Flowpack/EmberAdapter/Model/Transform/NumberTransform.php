<?php
namespace Flowpack\EmberAdapter\Model\Transform;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class NumberTransform extends AbstractTransform {

	/**
	 * @var string
	 */
	protected $attributeType = 'Flowpack\\EmberAdapter\\Model\\Attribute\\Number';

	/**
	 * Serializes any the value to match the ember attributes requirements
	 *
	 * @param mixed $value
	 * @return mixed
	 * @throws \InvalidArgumentException if the value cannot be serialized
	 */
	public function serialize($value) {
		return (float) $value;
	}

	/**
	 * Deserializes any value to match the flow property requirements
	 *
	 * @param mixed $value
	 * @return mixed
	 * @throws \InvalidArgumentException if the value cannot be deserialized
	 */
	public function deserialize($value) {
		return (float) $value;
	}

}