<?php
namespace Flowpack\EmberAdapter\Routing;

use Flowpack\EmberAdapter\Utility\EmberDataUtility;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Routing\DynamicRoutePart;
use TYPO3\Flow\Object\ObjectManagerInterface;

class ModelNameRoutePart extends DynamicRoutePart {

	/**
	 * @Flow\Inject
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @param string $value
	 * @return boolean
	 */
	protected function matchValue($value) {
		if ($value === NULL || $value === '') {
			return FALSE;
		}

		$className = EmberDataUtility::camelizeClassName($value);

		if ($this->objectManager->isRegistered($className)) {
			$this->setName('modelName');
			$this->value = $className;
			return TRUE;
		}

		return FALSE;
	}

}