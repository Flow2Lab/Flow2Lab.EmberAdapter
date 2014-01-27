<?php
namespace Flowpack\EmberAdapter\Model\Attribute;

class Date extends AbstractAttribute {

	/**
	 * Serializes any the value to match the ember attributes requirements
	 *
	 * @todo Write a propert implementation
	 * @return string
	 * @throw \InvalidArgumentException if the value cannot be serialized
	 */
	public function serialize() {
		if ($this->value instanceof \DateTime) {
			return $this->value->format(\DateTime::ISO8601);
		}

		return NULL;
	}

	/**
	 * Deserializes any value to match the flow property requirements
	 *
	 * @return \DateTime
	 * @throw \InvalidArgumentException if the value cannot be serialized
	 */
	public function deSerialize() {
		return new \DateTime($this->value);
	}

}