<?php
namespace Flow2Lab\EmberAdapter\Annotations;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class HasMany extends AbstractRelationAttribute {

	public $type = self::RELATION_HAS_MANY;

}