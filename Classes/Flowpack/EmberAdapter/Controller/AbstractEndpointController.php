<?php
namespace Flowpack\EmberAdapter\Controller;

use Flowpack\EmberAdapter\Utility\EmberDataUtility;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;

abstract class AbstractEndpointController extends ActionController {

	/**
	 * @var string
	 */
	protected $defaultViewObjectName = 'Flowpack\EmberAdapter\Mvc\View\EmberView';

	/**
	 * @var string
	 */
	protected $modelName;

	/**
	 * @var object
	 */
	protected $repository;

	/**
	 * @Flow\Inject
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * @var string
	 */
	protected $resourceArgumentName;

	/**
	 * @var object
	 */
	protected $receivedData;

	/**
	 * @return void
	 */
	protected function initializeAction() {
		if (!$this->request->hasArgument('modelName') && $this->modelName === '') {
			$this->throwStatus(500, NULL, 'No modelName configuration found in request.');
		}

		// Used for customized controllers
		if (!$this->request->hasArgument('modelName')) {
			$this->request->setArgument('modelName', $className = EmberDataUtility::camelizeClassName($this->modelName));
		}

		$this->receivedData = json_decode($this->request->getHttpRequest()->getContent());

		$arguments = $this->request->getArguments();
		$this->modelName = $arguments['modelName'];
		$repositoryName = str_replace(array('\\Model\\'), array('\\Repository\\'), $this->modelName) . 'Repository';

		if ($this->objectManager->isRegistered($repositoryName)) {
			$this->repository = $this->objectManager->get($repositoryName);
		} else {
			if (!$this->request->getHttpRequest()->getMethod() === 'GET' || !$this->request->hasArgument('model')) {
				$this->throwStatus(500, NULL, 'No repository found for model ' . $this->modelName . '.');
			}
		}

		$lowerUnderScoredModelName = EmberDataUtility::uncamelizeClassName($this->modelName);
		if (isset($this->receivedData->$lowerUnderScoredModelName)) {
			$this->arguments->getArgument('model')->setDataType($this->modelName);
			$arguments['model'] = (array)$this->receivedData->$lowerUnderScoredModelName;
			if (isset($arguments['id'])) {
				$arguments['model']['__identity'] = $arguments['id'];
				unset($arguments['id']);
			}

			// HACK: for some reason ember sends <model:ember<uid>:identifier> to the server
			$arguments['model'] = array_map(function($value) {
				if (substr($value, 0, 1) === '<' && substr($value, -1) === '>') {
					return array('__identity' => substr($value, strrpos($value, ':') + 1, 36));
				}
				if ($value !== NULL) {
					return $value;
				}
			}, $arguments['model']);

			// HACK: we should find another way to skip empty values
			foreach ($arguments['model'] as $key => $value) {
				if ($arguments['model'][$key] === NULL) {
					unset($arguments['model'][$key]);
				}
			}
		}

		if ($this->request->hasArgument('model')) {
			$arguments[$this->resourceArgumentName] = array('__identity' => $arguments['model']);
			$this->arguments->getArgument($this->resourceArgumentName)->setDataType($this->modelName);

			// Add properties to arguments
			if ($this->request->getHttpRequest()->getMethod() === 'PUT') {
				$this->arguments->getArgument($this->resourceArgumentName)->setDataType($this->modelName);
				$resourceArgumentName = $this->resourceArgumentName;
				$arguments[$this->resourceArgumentName] = (array)$this->receivedData->$resourceArgumentName;
				$arguments[$this->resourceArgumentName]['__identity'] = $arguments['model'];
			}
		}

		unset($arguments['modelName']);
		$this->request->setArguments($arguments);
	}


	/**
	 * Allow creation of resources in createAction()
	 *
	 * @return void
	 */
	public function initializeCreateAction() {
		/** @var \TYPO3\Flow\Mvc\Controller\MvcPropertyMappingConfiguration $propertyMappingConfiguration */
		$propertyMappingConfiguration = $this->arguments[$this->resourceArgumentName]->getPropertyMappingConfiguration();
		$propertyMappingConfiguration->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
		$propertyMappingConfiguration->allowAllProperties();
	}

	/**
	 * Allow modification of resources in updateAction()
	 *
	 * @return void
	 */
	public function initializeUpdateAction() {
		/** @var \TYPO3\Flow\Mvc\Controller\MvcPropertyMappingConfiguration $propertyMappingConfiguration */
		$propertyMappingConfiguration = $this->arguments[$this->resourceArgumentName]->getPropertyMappingConfiguration();
		$propertyMappingConfiguration->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, TRUE);
		$propertyMappingConfiguration->allowAllProperties();
	}

	/**
	 * Detect the supported request methods for a single order and set the "Allow" header accordingly (This is invoked on OPTION requests)
	 *
	 * @return string An empty string in order to prevent the view from rendering the action
	 */
	public function resourceOptionsAction() {
		$allowedMethods = array('GET');
		$uuid = $this->request->getArgument('model');
		$model = $this->repository->findByIdentifier($uuid);
		if ($model === NULL) {
			$this->throwStatus(404, NULL, 'The model "' . $uuid . '" does not exist');
		}
		$this->response->setHeader('Allow', implode(', ', $allowedMethods));
		$this->response->setStatus(204);
		return '';
	}
}
