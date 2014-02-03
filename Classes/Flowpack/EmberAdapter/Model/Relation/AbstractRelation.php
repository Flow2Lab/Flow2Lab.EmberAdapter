<?php
namespace Flowpack\EmberAdapter\Model\Relation;

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
	protected $sideload;

	/**
	 * @param string $name
	 * @param boolean $sideload
	 */
	public function __construct($name, $sideload = FALSE) {
		$this->name = $name;
		$this->sideload = $sideload;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return boolean
	 */
	public function getSideload() {
		return $this->sideload;
	}

}