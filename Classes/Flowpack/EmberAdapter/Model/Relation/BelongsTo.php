<?php
namespace Flowpack\EmberAdapter\Model\Relation;

use Flowpack\EmberAdapter\Model\EmberModelInterface;

class BelongsTo extends AbstractRelation {

	/**
	 * @var EmberModelInterface
	 */
	protected $otherModel;

	/**
	 * @param \Flowpack\EmberAdapter\Model\EmberModelInterface $otherModel
	 */
	public function setOtherModel($otherModel) {
		$this->otherModel = $otherModel;
	}

	/**
	 * @return \Flowpack\EmberAdapter\Model\EmberModelInterface
	 */
	public function getOtherModel() {
		return $this->otherModel;
	}

}