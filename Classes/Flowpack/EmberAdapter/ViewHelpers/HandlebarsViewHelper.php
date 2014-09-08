<?php
namespace Flowpack\EmberAdapter\ViewHelpers;

use TYPO3\Flow\Annotations as Flow;

class HandlebarsViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var array
	 */
	protected $handlebarTemplates = array();

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @param $templateName
	 * @param string $templateNamePrefix
	 * @return string
	 */
	protected function getPrefixedTemplateName($templateName, $templateNamePrefix = NULL) {
		$templateName = preg_replace_callback(
			'/(?<!_)([A-Z])/',
			function($matches) {
				return strtolower($matches[1]);
			}, lcfirst($templateName)
		);

		$prefixedTemplateName = strtolower($templateName);
		if ($templateNamePrefix !== NULL) {
			$prefixedTemplateName = $templateNamePrefix . '_' . $prefixedTemplateName;
		}
		return $prefixedTemplateName;
	}

	protected function getPrefixedResourceTemplateName($templateName, $templateNamePrefix = NULL) {
		$templateName = preg_replace_callback(
			'/\/(.*)/',
			function($matches) {
				return strtolower($matches[0]);
			},
			$templateName
		);

		if ($templateNamePrefix !== NULL) {
			$templateName = $templateNamePrefix . $templateName;
		}

		return \Radmiraal\Emberjs\Utility\EmberDataUtility::uncamelize($templateName);
	}

	/**
	 * @param array $paths
	 * @param string $templateNamePrefix
	 * @param string $suffix
	 * @return string
	 */
	public function render(array $paths, $templateNamePrefix = NULL, $suffix = '.hbs') {
		foreach ($paths as $path) {
			$templates = \TYPO3\Flow\Utility\Files::readDirectoryRecursively($path, $suffix);

			foreach ($templates as $template) {
				if (substr($template, 0, strlen($path) + 10) === $path . '/Resources') {
					$templateName = str_replace(array($path . '/Resources/', $suffix), '', $template);
					$this->handlebarTemplates[$this->getPrefixedResourceTemplateName($templateName, $templateNamePrefix)] = $template;
				} elseif (substr($template, 0, strlen($path) + 9) === $path . '/Partials') {
					$templateName = str_replace(array($path . '/Partials/', $suffix), '', $template);
					$this->handlebarTemplates['_'. $this->getPrefixedResourceTemplateName($templateName, $templateNamePrefix)] = $template;
				} else {
					$templateName = str_replace(array($path . '/', $suffix, '/'), array('', '', '/'), $template);
					$this->handlebarTemplates[$this->getPrefixedTemplateName($templateName, $templateNamePrefix)] = $template;
				}
			}
		}

		foreach ($this->handlebarTemplates as $templateName => $template) {
			$handlebarView = new \TYPO3\Fluid\View\StandaloneView();

			$handlebarView->setTemplateSource(\TYPO3\Flow\Utility\Files::getFileContents($template));

			$assignHelpers = \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($this->settings, 'handlebar.view.assignHelpers.' . $templateName);

			if (is_array($assignHelpers)) {
				foreach ($assignHelpers as $variable => $helper) {
					if (!isset($helper['class']) || !isset($helper['method'])) {
						continue;
					}

					$helperInstance = $this->objectManager->get($helper['class']);
					$value = call_user_func_array(array($helperInstance, $helper['method']), isset($helper['arguments']) ? $helper['arguments'] : array());

					$handlebarView->assign($variable, $value);
				}
			}

			$this->handlebarTemplates[$templateName] = sprintf(
				'<script type="text/x-handlebars" data-template-name="%s">%s</script>',
				$templateName,
				$handlebarView->render()
			);
		}

		return implode('', $this->handlebarTemplates);
	}

}

?>