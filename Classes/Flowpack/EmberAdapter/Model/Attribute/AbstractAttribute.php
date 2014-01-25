<?php
namespace Flowpack\EmberAdapter\Model\Attribute;

abstract class AbstractAttribute {

	/**
	 * Name of the attribute in the ember model.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __construct($name, $value) {
		$this->name = $name;
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Serializes any the value to match the ember attributes requirements
	 *
	 * @return mixed
	 * @throw \InvalidArgumentException if the value cannot be serialized
	 */
	abstract public function serialize();

	/**
	 * Deserializes any value to match the flow property requirements
	 *
	 * @return mixed
	 * @throw \InvalidArgumentException if the value cannot be serialized
	 */
	abstract public function deSerialize();

}