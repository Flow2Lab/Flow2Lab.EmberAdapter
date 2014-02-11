<?php
namespace Flowpack\EmberAdapter\Routing;

use Flowpack\EmberAdapter\Utility\EmberDataUtility;
use TYPO3\Flow\Annotations as Flow;

/**
 */
class ModelNameRoutePart extends \TYPO3\Flow\Mvc\Routing\DynamicRoutePart {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
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

?>