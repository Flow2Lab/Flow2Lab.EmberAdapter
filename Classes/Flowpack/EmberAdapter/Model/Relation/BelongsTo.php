<?php
namespace Flowpack\EmberAdapter\Model\Relation;

use Flowpack\EmberAdapter\Model\EmberModelInterface;

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
	 * @param \Flowpack\EmberAdapter\Model\EmberModelInterface $otherModel
	 */
	public function setRelatedModel($otherModel) {
		$this->relatedModel = $otherModel;
		$this->id = $otherModel->getId();
	}

	/**
	 * @return \Flowpack\EmberAdapter\Model\EmberModelInterface
	 */
	public function getRelatedModel() {
		return $this->relatedModel;
	}

}