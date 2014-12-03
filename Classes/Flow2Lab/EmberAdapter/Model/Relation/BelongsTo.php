<?php
namespace Flow2Lab\EmberAdapter\Model\Relation;

use Flow2Lab\EmberAdapter\Model\EmberModelInterface;

class BelongsTo extends AbstractRelation {

	/**
	 * The identifier of the related model.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * @var EmberModelInterface
	 */
	protected $relatedModel;

	/**
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param \Flow2Lab\EmberAdapter\Model\EmberModelInterface $otherModel
	 */
	public function setRelatedModel($otherModel) {
		$this->relatedModel = $otherModel;
		$this->id = $otherModel->getId();
	}

	/**
	 * @return \Flow2Lab\EmberAdapter\Model\EmberModelInterface
	 */
	public function getRelatedModel() {
		return $this->relatedModel;
	}

}