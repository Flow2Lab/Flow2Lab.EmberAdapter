<?php
namespace Flowpack\EmberAdapter\Model;

use Flowpack\EmberAdapter\Model\Attribute\AbstractAttribute;
use Flowpack\EmberAdapter\Model\Relation\AbstractRelation;

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