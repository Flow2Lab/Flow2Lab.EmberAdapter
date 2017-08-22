<?php
namespace Flow2Lab\EmberAdapter\Annotations;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class BelongsTo extends AbstractRelationAttribute {

	public $type = self::RELATION_BELONGS_TO;

}