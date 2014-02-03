<?php
namespace Flowpack\EmberAdapter\Annotations;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class HasMany extends AbstractRelationAttribute {

	public $type = self::RELATION_HAS_MANY;

}