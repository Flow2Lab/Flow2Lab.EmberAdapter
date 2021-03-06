<?php
namespace Flow2Lab\EmberAdapter\Annotations;

use TYPO3\Flow\Annotations as Flow;

/**
 * Marks a class as part of the ember model
 *
 * @Flow\Proxy(false)
 * @Annotation
 * @Target("CLASS")
 */
final class Model {

	/**
	 * Name of the model in the ember application. If left empty, the simple class name will be used.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * @param array $values
	 */
	public function __construct(array $values) {
		if (isset($values['name']) || isset($values['value'])) {
			$this->name = isset($values['name']) ? $values['name'] : $values['value'];
		}
	}

}