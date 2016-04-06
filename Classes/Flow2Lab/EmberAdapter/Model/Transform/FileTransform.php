<?php
namespace Flow2Lab\EmberAdapter\Model\Transform;

use TYPO3\Flow\Annotations as Flow;

/**
 * Does not transform any values (noop).
 * @Flow\Scope("singleton")
 */
class FileTransform extends AbstractTransform {

	/**
	 * @var string
	 */
	protected $attributeType = 'Flow2Lab\\EmberAdapter\\Model\\Attribute\\FileAttribute';

	/**
	 * Serializes any the value to match the ember attributes requirements
	 *
	 * @param mixed $value
	 * @return mixed
	 * @throws \InvalidArgumentException if the value cannot be serialized
	 */
	public function serialize($value) {
		if ($value === NULL) {
			return '';
		} else if (is_string($value)) {
			return $value;
		} else if (is_object($value) && method_exists($value, '__toString')) {
			return (string) $value;
		} else {
			$type = is_object($value) ? get_class($value) : gettype($value);
			throw new \InvalidArgumentException('StringTransform can only transform strings and objects implementing __toString, ' . $type . ' given.', 1391457710);
		}
	}

	/**
	 * Deserializes any value to match the flow property requirements
	 *
	 * @param mixed $value
	 * @return mixed
	 * @throws \InvalidArgumentException if the value cannot be deserialized
	 */
	public function deserialize($value) {
		return (string) $value;
	}

}