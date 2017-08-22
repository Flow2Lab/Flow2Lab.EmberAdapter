<?php
namespace Flow2Lab\EmberAdapter\Annotations;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 * @Annotation
 * @Target("PROPERTY")
 */
final class BelongsTo extends AbstractRelationAttribute {

	public $type = self::RELATION_BELONGS_TO;

}