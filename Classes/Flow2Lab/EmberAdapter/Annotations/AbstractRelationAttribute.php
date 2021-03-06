<?php
namespace Flow2Lab\EmberAdapter\Annotations;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 * Class AbstractRelationAttribute
 * @package Flow2Lab\EmberAdapter\Annotations
 */
abstract class AbstractRelationAttribute {

	const RELATION_BELONGS_TO = 'belongsTo';
	const RELATION_HAS_MANY = 'hasMany';

	/**
	 * Name of the model this attribute relates to.
	 *
	 * @var string
	 */
	public $model;

	/**
	 * ClassName of the model this attribute relates to.
	 * @var string
	 */
	public $className;

	/**
	 * If true, the relation will be sideloaded. Value Objects are always sideloaded.
	 *
	 * @var boolean
	 */
	public $sideload = FALSE;

	/**
	 * Name of the attribute in the other model (explicit inverse).
	 *
	 * @var string
	 */
	public $inverse;

	/**
	 * Type of the relation. Has to be set by the inheriting classes.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * @param array $values
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $values) {
		if (!isset($values['model']) && !isset($values['value'])) {
			throw new \InvalidArgumentException('The relation annotation requires a model name.');
		}

		if (isset($values['model']) || isset($values['value'])) {
			$this->model = isset($values['model']) ? $values['model'] : $values['value'];
		}

		if (isset($values['sideload'])) {
			$this->sideload = (boolean) $values['sideload'];
		}

		if (isset($values['inverse'])) {
			$this->inverse = $values['inverse'];
		}
	}

}