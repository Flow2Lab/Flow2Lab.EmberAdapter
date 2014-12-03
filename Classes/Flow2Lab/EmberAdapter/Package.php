<?php
namespace Flow2Lab\EmberAdapter;

use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;
use TYPO3\Flow\Configuration\ConfigurationManager;

/**
 * The Flow2Lab EmberAdapter Package
 */
class Package extends BasePackage {

	/**
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();

		$dispatcher->connect('TYPO3\Flow\Configuration\ConfigurationManager', 'configurationManagerReady',
			function (ConfigurationManager $configurationManager) {
				$configurationManager->registerConfigurationType(
					'EmberModels',
					ConfigurationManager::CONFIGURATION_PROCESSING_TYPE_DEFAULT,
					TRUE
				);
			}
		);
	}

}