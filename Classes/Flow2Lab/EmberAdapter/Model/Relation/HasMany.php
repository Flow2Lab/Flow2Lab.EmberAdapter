<?php
namespace Flow2Lab\EmberAdapter\Model\Relation;

use Flow2Lab\EmberAdapter\Model\EmberModelInterface;

class HasMany extends AbstractRelation {

	/**
	 * The identifiers of the related models.
	 *
	 * @var array<string>
	 */
	protected $ids;

	/**
	 * @var array<EmberModelInterface>
	 */
	protected $relatedModels;

	/**
	 * @param array $ids
	 */
	public function setIds($ids) {
		$this->ids = $ids;
	}

	/**
	 * @return array
	 */
	public function getIds() {
		return $this->ids;
	}

	/**
	 * @param array<EmberModelInterface> $relatedModels
	 */
	public function setRelatedModels($relatedModels) {
		$this->relatedModels = $relatedModels;
		$this->ids = array_map(function(EmberModelInterface $relatedModel) {
			return $relatedModel->getId();
		}, $relatedModels);
	}

	/**
	 * @return array<EmberModelInterface>
	 */
	public function getRelatedModels() {
		return $this->relatedModels;
	}

}