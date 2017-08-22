<?php
namespace Flow2Lab\EmberAdapter\Annotations;

use TYPO3\Flow\Annotations as Flow;

/**
 * Marks a property of the domain model as ember attribute.
 * @Flow\Proxy(false)
 * @Annotation
 * @Target("PROPERTY")
 */
final class Attribute {

	/**
	 * Attribute name in the ember model. If left empty, the class property name will be used.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Type of the ember model attribute.
	 *
	 * @var string
	 */
	public $type = 'string';

	/**
	 * Options passed to the attribute instances.
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * @param array $values
	 */
	public function __construct(array $values) {
		if (isset($values['name']) || isset($values['value'])) {
			$this->name = isset($values['name']) ? $values['name'] : $values['value'];
		}

		if (isset($values['type'])) {
			$this->type = $values['type'];
		}

		if (isset($values['options'])) {
			if (is_array($values['options'])) {
				$this->options = $values['options'];
			} else {
				$this->options = array($values['options']);
			}
		}
	}

}