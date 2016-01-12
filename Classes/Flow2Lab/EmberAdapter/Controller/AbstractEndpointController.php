<?php
namespace Flow2Lab\EmberAdapter\Controller;

use Flow2Lab\EmberAdapter\Utility\EmberDataUtility;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Mvc\Routing\RouterInterface;
use TYPO3\Flow\Mvc\Routing\Route;

abstract class AbstractEndpointController extends ActionController {

	/**
	 * @var string
	 */
	protected $defaultViewObjectName = 'Flow2Lab\EmberAdapter\Mvc\View\EmberView';

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
	 * @Flow\Inject
	 * @var RouterInterface
	 */
	protected $router;

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


		if ($this->request->getHttpRequest()->getMethod() === 'OPTIONS') {
			$this->resourceOptionsAction();
		} else {
			if ($this->repository === NULL) {
				$repositoryName = str_replace(array('\\Model\\'), array('\\Repository\\'), $this->modelName) . 'Repository';
				if ($this->objectManager->isRegistered($repositoryName)) {
					$this->repository = $this->objectManager->get($repositoryName);
				} else {
					if (!$this->request->getHttpRequest()->getMethod() === 'GET' || !$this->request->hasArgument('model')) {
						$this->throwStatus(500, NULL, 'No repository found for model ' . $this->modelName . '.');
					}
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

			$this->response->setHeader('Access-Control-Allow-Origin', '*');
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
		$allowedMethods = array();

		$relativePath = $this->request->getHttpRequest()->getRelativePath();
		$requestLength = strlen($relativePath);

			// Remove object Identifier
		if ($this->request->hasArgument('model')) {
			$relativePath = str_replace('/' . $this->request->getArgument('model'), '', $relativePath);
			$requestLength = strlen($relativePath);
		}

		/** @var Route $route */
		foreach ($this->router->getRoutes() as $route) {
			if (substr_compare($relativePath, $route->getUriPattern(), 0, $requestLength) === 0) {
				$allowedMethods = array_merge($allowedMethods, $route->getHttpMethods());
			}
		}

		$this->response->setHeader('Access-Control-Allow-Origin', '*');
		$this->response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, content-type, accept, Authorization');
		$this->response->setHeader('Access-Control-Allow-Methods', implode(', ', array_unique($allowedMethods)));
		$this->response->setStatus(204);

		return '';
	}

	/**
	 * Maps arguments delivered by the request object to the local controller arguments.
	 *
	 * @return void
	 * @throws \TYPO3\Flow\Mvc\Exception\RequiredArgumentMissingException
	 * @api
	 */
	protected function mapRequestArgumentsToControllerArguments() {
		foreach ($this->arguments as $argument) {
			$argumentName = EmberDataUtility::camelize($argument->getName());
			if ($this->request->hasArgument($argumentName)) {
				$arguments = $this->request->getArgument($argumentName);
				if (is_array($arguments)) {
					foreach ($arguments as $propertyName => $propertyValue) {
						if ($propertyName !== '__identity') {
							unset($arguments[$propertyName]);
							$arguments[EmberDataUtility::camelize($propertyName)] = $propertyValue;
						}
					}
					$argument->setValue($arguments);
				} else {
					$argument->setValue($this->request->getArgument($argumentName));
				}

			} elseif ($argument->isRequired()) {
				throw new \TYPO3\Flow\Mvc\Exception\RequiredArgumentMissingException('Required argument "' . $argumentName  . '" is not set.', 1298012500);
			}
		}
	}

	/**
	 * @return string
	 * @api
	 */
	protected function errorAction() {
		$this->addErrorFlashMessage();

		return $this->getFlattenedValidationErrorMessage();
	}

	/**
	 * Returns a json object containing all validation errors.
	 *
	 * @return string
	 */
	protected function getFlattenedValidationErrorMessage() {
		$outputMessage = 'Validation failed while trying to call ' . get_class($this) . '->' . $this->actionMethodName . '().' . PHP_EOL;
		$logMessage = $outputMessage;

		$errorMessages = array();
		foreach ($this->arguments->getValidationResults()->getFlattenedErrors() as $propertyPath => $errors) {
			foreach ($errors as $error) {
				$errorMessages[$propertyPath][] = $error->render();
				$logMessage .= 'Error for ' . $propertyPath . ':  ' . $error->render() . PHP_EOL;
			}
		}
		$this->systemLogger->log($logMessage, LOG_ERR);

		return json_encode($errorMessages);
	}
}
