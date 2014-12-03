<?php
namespace Flow2Lab\EmberAdapter\Model;

use Flow2Lab\EmberAdapter\Model\Attribute\AbstractAttribute;
use Flow2Lab\EmberAdapter\Model\Relation\AbstractRelation;

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

	/**
	 * @param AbstractAttribute $attribute
	 */
	public function addAttribute(AbstractAttribute $attribute);

	/**
	 * @param AbstractAttribute $attribute
	 */
	public function removeAttribute(AbstractAttribute $attribute);

	/**
	 * Returns the relations to other models.
	 *
	 * @return RelationCollection
	 */
	public function getRelations();

	/**
	 * @param AbstractRelation $relation
	 */
	public function addRelation(AbstractRelation $relation);

	/**
	 * @param AbstractRelation $relation
	 */
	public function removeRelation(AbstractRelation $relation);

}