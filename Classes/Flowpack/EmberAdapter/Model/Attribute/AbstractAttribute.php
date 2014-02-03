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
	 * The value to be transformed by transforms.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @param string $name
	 * @param mixed $value
	 * @param array $options
	 */
	public function __construct($name, $value, $options = array()) {
		$this->name = $name;
		$this->value = $value;

		if ($options !== array()) {
			$this->options = $options;
		}
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
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

}