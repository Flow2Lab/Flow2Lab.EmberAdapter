<?php
namespace Flow2Lab\EmberAdapter\Annotations;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 * @Annotation
 * @Target("PROPERTY")
 */
final class HasMany extends AbstractRelationAttribute {

	public $type = self::RELATION_HAS_MANY;

}