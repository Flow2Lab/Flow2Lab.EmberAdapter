<?php
namespace Flow2Lab\EmberAdapter\Model\Attribute;

class NumberAttribute extends AbstractAttribute {

	/**
	 * Default format set to integer.
	 *
	 * @var array
	 */
	protected $options = array('format' => 'int');

}