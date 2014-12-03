<?php
namespace Flow2Lab\EmberAdapter\Model\Transform;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class NumberTransform extends AbstractTransform {

	/**
	 * @var string
	 */
	protected $attributeType = 'Flow2Lab\\EmberAdapter\\Model\\Attribute\\Number';

	/**
	 * Serializes any the value to match the ember attributes requirements
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return mixed
	 * @throws \InvalidArgumentException if the value cannot be serialized
	 */
	public function serialize($value, array $options = NULL) {
		$format = $this->getNumberFormat($options);
		$serializedNumber = $this->serializeNumber($value, $format);
		return $serializedNumber;
	}

	/**
	 * Deserializes any value to match the flow property requirements
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return mixed
	 * @throws \InvalidArgumentException if the value cannot be deserialized
	 */
	public function deserialize($value, array $options = NULL) {
		$format = $this->getNumberFormat($options);
		$serializedNumber = $this->serializeNumber($value, $format);
		return $serializedNumber;
	}

	/**
	 * @param array $options
	 * @return string
	 */
	protected function getNumberFormat(array $options = NULL) {
		$format = 'int';
		if ($options !== NULL && isset($options['format'])) {
			$format = strtolower($options['format']);
		}
		return $format;
	}

	/**
	 * @param mixed $number
	 * @param string $format
	 * @return int|float
	 * @throws \InvalidArgumentException
	 */
	protected function serializeNumber($number, $format) {
		switch ($format) {
			case 'int':
				return (int) $number;
			case 'float':
				return (float) $number;
		}

		throw new \InvalidArgumentException('The format ' . $format . ' is not supported by the NumberTransform.', 1391465288);
	}

}