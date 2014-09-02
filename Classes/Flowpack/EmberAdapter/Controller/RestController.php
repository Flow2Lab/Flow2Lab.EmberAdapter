<?php
namespace Flowpack\EmberAdapter\Controller;

use Flowpack\EmberAdapter\Utility\EmberDataUtility;
use TYPO3\Flow\Annotations as Flow;

/**
 * This REST controller is made for handling ember-data DS.RESTAdapter calls.
 * It's only capable of handling 1 argument for an action, named 'resource'.
 */
class RestController extends AbstractEndpointController {

	protected $defaultViewObjectName = 'Flowpack\EmberAdapter\Mvc\View\EmberView';

	/**
	 * List all $resources in repository
	 *
	 * @return void
	 */
	public function listAction() {
		$resourceRecords = $this->repository->findAll();
		$this->view->assign('content', array(EmberDataUtility::uncamelizeClassName($this->modelName) => $resourceRecords));
	}

	/**
	 * Shows a single task object
	 *
	 * @param object $model The task to show
	 * @return void
	 */
	public function showAction($model) {
		$this->view->assign('content', array(EmberDataUtility::uncamelizeClassName($this->modelName) => $model));
	}

	/**
	 * Adds the given new task object to the task repository
	 *
	 * @param object $model A new task to add
	 * @return void
	 */
	public function createAction($model) {
		$this->repository->add($model);
		$this->persistenceManager->persistAll();
		$this->response->setStatus(201);
		$this->view->assign('content', array(EmberDataUtility::uncamelizeClassName($this->modelName) => $model));
	}

	/**
	 * Updates the given task object
	 *
	 * @param object $model The task to update
	 * @return void
	 */
	public function updateAction($model) {
		$this->repository->update($model);
		$this->response->setStatus(204);
	}

	/**
	 * Removes the given task object from the task repository
	 *
	 * @param object $model The task to delete
	 * @return string
	 */
	public function deleteAction($model) {
		$this->repository->remove($model);
		$this->response->setStatus(204);
		return '';
	}

}

?>