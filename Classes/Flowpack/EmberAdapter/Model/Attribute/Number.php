<?php
namespace Flowpack\EmberAdapter\Model\Attribute;

class Number extends AbstractAttribute {

	/**
	 * Serializes any the value to match the ember attributes requirements
	 *
	 * @return mixed
	 * @throw \InvalidArgumentException if the value cannot be serialized
	 */
	public function serialize() {
		return (float) $this->value;
	}

	/**
	 * Unserializes any value to match the flow property requirements
	 *
	 * @return mixed
	 * @throw \InvalidArgumentException if the value cannot be serialized
	 */
	public function unserialize() {
		return (float) $this->value;
	}

}