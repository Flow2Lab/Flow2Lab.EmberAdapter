<?php
namespace Flowpack\EmberAdapter\Model;

use Flowpack\EmberAdapter\Model\Attribute\AbstractAttribute;

class GenericEmberModel implements EmberModelInterface {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var AttributeCollection
	 */
	protected $attributes;

	/**
	 * @param string $name
	 * @param string $id
	 * @throws \InvalidArgumentException
	 */
	public function __construct($name, $id ) {
		if (is_string($name) === FALSE || $name === '' || is_string($id) === FALSE || $id === '') {
			throw new \InvalidArgumentException('The models name and identifier must not be empty.', 1390670157);
		}

		$this->name = $name;
		$this->id = $id;
		$this->attributes = new AttributeCollection();
	}

	/**
	 * @param AbstractAttribute $attribute
	 */
	public function addAttribute(AbstractAttribute $attribute) {
		$this->attributes->attach($attribute);
	}

	/**
	 * @param AbstractAttribute $attribute
	 */
	public function removeAttribute(AbstractAttribute $attribute) {
		$this->attributes->detach($attribute);
	}

	/**
	 * Must return the models name in singular.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Must return the models identifier.
	 *
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Returns the models attributes.
	 *
	 * @return AttributeCollection
	 */
	public function getAttributes() {
		return $this->attributes;
	}

}