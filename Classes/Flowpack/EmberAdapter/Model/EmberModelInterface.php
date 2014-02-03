<?php
namespace Flowpack\EmberAdapter\Model;

interface EmberModelInterface {

	/**
	 * Returns the models name in singular.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Returns the models identifier.
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * Returns the models attributes.
	 *
	 * @return AttributeCollection
	 */
	public function getAttributes();

}