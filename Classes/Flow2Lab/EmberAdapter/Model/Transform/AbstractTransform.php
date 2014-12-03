<?php
namespace Flow2Lab\EmberAdapter\Model\Transform;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
abstract class AbstractTransform implements TransformInterface {

	/**
	 * Sets the priority of the transform.
	 *
	 * @var int
	 */
	protected $priority = 1;

	/**
	 * Sets the attribute this transform can transform.
	 *
	 * @var string
	 */
	protected $attributeType;

	/**
	 * Returns the priority of the transform.
	 *
	 * @return int
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * Returns the Attribute this Transform can transform.
	 *
	 * @return string
	 */
	public function getAttributeType() {
		return $this->attributeType;
	}

}