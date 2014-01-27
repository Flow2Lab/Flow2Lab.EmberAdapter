<?php
namespace Flowpack\EmberAdapter\Model\Attribute;

class String extends AbstractAttribute {

	/**
	 * Serializes the value to match the ember attributes requirements.
	 *
	 * @return string
	 * @throw \InvalidArgumentException if the value cannot be serialized
	 */
	public function serialize() {
		if (is_string($this->value)) {
			return $this->value;
		} else if (is_object($this->value) && method_exists($this->value, '__toString')) {
			return (string) $this->value;
		} else {
			// todo: introduce own exception, make it more meaningful
			throw new \InvalidArgumentException('Unable to convert the value to a string.');
		}
	}

	/**
	 * Deserializes the value to match flow property requirements.
	 *
	 * @return string
	 * @throw \InvalidArgumentException if the value cannot be serialized
	 */
	public function deSerialize() {
		// not implemented yet
	}

}