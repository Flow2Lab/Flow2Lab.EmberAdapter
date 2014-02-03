<?php
namespace Flowpack\EmberAdapter\Annotations;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class BelongsTo extends AbstractRelationAttribute {

	public $type = self::RELATION_BELONGS_TO;

}