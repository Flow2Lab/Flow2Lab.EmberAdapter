<?php
namespace Flowpack\EmberAdapter\Model\Transform;

interface TransformInterface {

	/**
	 * Returns the priority of the transform.
	 *
	 * @return int
	 */
	public function getPriority();

	/**
	 * Returns the Attribute this Transform can transform.
	 *
	 * @return string
	 */
	public function getAttributeType();

	/**
	 * Serializes any the value to match the ember attributes requirements
	 *
	 * @todo: create an exception in Transform\Exceptions
	 * @param mixed $value
	 * @return mixed
	 * @throws \InvalidArgumentException if the value cannot be serialized
	 */
	public function serialize($value);

	/**
	 * Deserializes any value to match the flow property requirements
	 *
	 * @todo: create an exception in Transform\Exceptions
	 * @param mixed $value
	 * @return mixed
	 * @throws \InvalidArgumentException if the value cannot be deserialized
	 */
	public function deserialize($value);

}