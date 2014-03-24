<?php
namespace Flowpack\EmberAdapter\Utility;

use TYPO3\Flow\Annotations as Flow;

/**
 * This class is meant for utility methods needed to match the ember-data
 * conventions, like string manipulation for propertynames or converting
 * ember-data json objects to usable objects.
 */
class EmberDataUtility {

	/**
	 * This method underscores attributes camelCased properties
	 *
	 * @param string $string
	 * @return string
	 * @see http://emberjs.com/guides/models/the-rest-adapter/#toc_underscored-attribute-names
	 */
	static public function uncamelize($string) {
		return strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $string));
	}

	/**
	 * This method camelCases attributes underscored properties
	 *
	 * @param string $string
	 * @return string
	 * @see http://emberjs.com/guides/models/the-rest-adapter/#toc_underscored-attribute-names
	 */
	static public function camelize($string) {
		return preg_replace_callback('/_([a-z])/', function($string) {
			return strtoupper($string[1]);
		}, $string);
	}

	/**
	 * Converts an underscored classname to UpperCamelCased
	 *
	 * @param string $className
	 * @return string
	 */
	static public function camelizeClassName($className) {
		return ucfirst(
			preg_replace_callback('/_([a-z0-9]{1})/i', function ($matches) {
				return '\\' . strtoupper($matches[1]);
			}, $className)
		);
	}

	/**
	 * Converts a UpperCamelCased classname to underscored className
	 * @param string $className
	 * @return string
	 */
	static public function uncamelizeClassName($className) {
		if ($className[0] === '\\') {
			$className = substr($className, 1);
		}
		$className = preg_replace_callback('/\\\\([a-z0-9]{1})/i', function ($matches) {
			return '_' . lcfirst($matches[1]);
		}, $className);

			// Prevent malformed vendor namespace
		$classParts = explode('_', $className, 2);
		if (strtoupper($classParts[0]) === $classParts[0]) {
			return $className;
		}

		return lcfirst($className);
	}

}