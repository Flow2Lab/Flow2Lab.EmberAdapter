<?php
namespace Flowpack\EmberAdapter\Model\Factory;

use Flowpack\EmberAdapter\Model\EmberModelInterface;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class EmberModelFactory {

	/**
	 * @param object $domainModel
	 * @return EmberModelInterface
	 */
	public function create($domainModel) {
		// todo: this is where the rest of the EmberView goes :)
	}

}