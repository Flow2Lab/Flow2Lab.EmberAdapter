<?php
namespace Flow2Lab\EmberAdapter\Model\Relation;

abstract class AbstractRelation {

	/**
	 * Name of the attribute in the ember model.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Whether to sideload this relation or not.
	 *
	 * @var boolean
	 */
	protected $sideloaded;

	/**
	 * @param string $name
	 * @param boolean $sideloaded
	 */
	public function __construct($name, $sideloaded = FALSE) {
		$this->name = $name;
		$this->sideloaded = $sideloaded;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $className
	 * @return void
	 */
	public function setClassName($className) {
		$this->className = $className;
	}

	/**
	 * @return string
	 */
	public function getClassName() {
		$this->className;
	}

	/**
	 * @return boolean
	 */
	public function isSideloaded() {
		return $this->sideloaded;
	}

}