<?php
namespace Flowpack\EmberAdapter\Model;

interface EmberModelInterface {

	/**
	 * Must return the models name in singular.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Must return the models identifier.
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * Must return an array with the attribute names as key. If an attribute is a relation of any kind,
	 * the value must be the related models identifier(s).
	 *
	 * @return array
	 */
	public function getAttributesArray();

}