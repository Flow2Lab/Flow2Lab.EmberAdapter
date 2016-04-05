<?php
namespace Flow2Lab\EmberAdapter\Domain\Repository;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\QueryInterface;

/**
 * A repository for Content
 *
 * @Flow\Scope("singleton")
 */
class ContentRepository extends \TYPO3\Flow\Persistence\Repository {

	/**
	 * @var array
	 */
	protected $defaultOrderings = array();

	/**
	 * @var \Flow2Lab\EmberAdapter\Configuration\ModelConfigurationManager
	 * @Flow\Inject
	 */
	protected $modelConfigurationManager;

	/**
	 * Initializes a dynamic Repository.
	 */
	public function __construct() {
	}

	/**
	 * Set the classname of the entities this repository is managing.
	 * Note that anything that is an "instanceof" this class is accepted
	 * by the repository.
	 *
	 * @api
	 */
	public function setEntityClassName($entityClassName) {
		$this->entityClassName = preg_replace(array('/\\\Repository\\\/', '/Repository$/'), array('\\Model\\', ''), $entityClassName);
	}

	/**
	 * Returns the classname of the entities this repository is managing.
	 * Note that anything that is an "instanceof" this class is accepted
	 * by the repository.
	 *
	 * @return string
	 * @api
	 */
	public function getEntityClassName() {
		return $this->entityClassName;
	}

	/**
	 * @param $queryParams
	 * @return array|\TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findByQueryParams($queryParams) {
		$query = $this->createQuery();

		$limit = NULL;
		$offset = NULL;
		$page = 0;

		$constraints = array();

		foreach ($queryParams as $key => $param) {
			switch ($key) {
				case 'searchTerm':
					$constraints = $this->searchTermToConstraints($param, $query);
					break;
				case 'limit':
				case 'per_page':
				case 'page_size':
					$limit = $param;
					break;
				case 'offset':
					$offset = $param;
					break;
				case 'start':
				case 'page':
					$page = $param;
					break;
				case 'sortBy':
				case 'sortDirection':
					break;
				case 'filter':
					if ($param !== '' || $param !== NULL) {
						$constraints = $this->convertFilterParamsToConstraints($param, $query);
					}
					break;
				default:
					if ($key !== 'filter' && ($param !== '' || $param !== NULL)) {
						$constraints = $this->convertFilterParamsToConstraints(array($key => $param), $query);
					}
					break;
			}
		}

		if ($page > 1) {
			$offset = $page * $limit;
		}

		if (count($constraints) > 1) {
			return $query->matching($query->logicalAnd($constraints))->setOffset($offset)->setLimit($limit)->execute();
		} elseif (count($constraints) === 1) {
			return $query->matching($constraints[0])->setOffset($offset)->setLimit($limit)->execute();
		} else {
			return $query->setOffset($offset)->setLimit($limit)->execute();
		}
	}

	/**
	 * @param $queryParams
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function getMetaByQueryParams($queryParams) {
		$query = $this->createQuery();

		$limit = NULL;
		$offset = NULL;

		$constraints = array();

		foreach ($queryParams as $param => $value) {
			switch ($param) {
				case 'searchTerm':
					$constraints = $this->searchTermToConstraints($param, $query);
					break;
				case 'limit':
				case 'per_page':
				case 'page_size':
					$limit = $value;
					break;
				case 'start':
				case 'offset':
					break;
				case 'page':
					break;
				case 'filter':
					if ($value !== '' || $value !== NULL) {
						$constraints = $this->convertFilterParamsToConstraints($value, $query);
					}
					break;
				default:
					if ($param !== 'filter' && ($value !== '' || $value !== NULL)) {
						$constraints = $this->convertFilterParamsToConstraints(array($param => $value), $query);
					}
					break;
			}
		}

		if (count($constraints) > 1) {
			$results['meta']['total'] = $query->matching($query->logicalAnd($constraints))->execute()->count();
		} elseif (count($constraints) === 1) {
			$results['meta']['total'] = $query->matching($constraints[0])->execute()->count();
		} else {
			$results['meta']['total'] = $query->execute()->count();
		}

		if ($results['meta']['total'] !== 0 && $limit !== NULL && $limit !== '0') {
			$results['meta']['total_pages'] = ceil($results['meta']['total'] / $limit);
		} else {
			$results['meta']['total_pages'] = 1;
		}
		$results['meta']['per_page'] = $limit;

		return $results;
	}

	/**
	 * @param $filterParams
	 * @param \TYPO3\Flow\Persistence\QueryResultInterface $query
	 * @return array
	 */
	protected function convertFilterParamsToConstraints($filterParams, $query) {
		$constraints = array();
		foreach ($filterParams as $property => $filterValue) {
			$constraints[] = $this->convertFilterParamsToConstraint($property, $filterValue, $query);
		}
		 return $constraints;
	}

	/**
	 * @param $property
	 * @param $value
	 * @param \TYPO3\Flow\Persistence\QueryResultInterface $query
	 * @return array
	 */
	protected function convertFilterParamsToConstraint($property, $value, $query) {
		$constraint = array();
		$relationModel = NULL;
			// Determine attribute type
		if ($this->modelConfigurationManager->isRelation($this->getEntityClassName(), $property)) {
			$relationModel = $this->modelConfigurationManager->getRelation($this->getEntityClassName(), $property);
			if ($relationModel instanceof \Flow2Lab\EmberAdapter\Annotations\BelongsTo) {
				$type = 'belongsTo';
			} else {
				$type = 'hasMany';
			}
		} else {
			$type = $this->modelConfigurationManager->getModelAttributeType($this->getEntityClassName(), $property);
		}

		switch($type) {
			case 'date':
				if (count($value) > 2) {
					$constraintArray = array();
					foreach ($value as $arrayValue) {
						$logicalAndConstraint[] = $query->like($property, $arrayValue);
					}
					$constraint = $query->logicalAnd($constraintArray);
				} elseif (count($value) === 2) {
					$startDate = new \DateTime($value[0]);
					$firstQuery = $query->greaterThanOrEqual($property, $startDate);

					$endDate = new \DateTime($value[1]);
					$endQuery = $query->lessThanOrEqual($property, $endDate);

					$constraint = $query->logicalAnd($firstQuery, $endQuery);
				} else {
					$constraint = $query->equals($property, $value);
				}
				break;
			case 'string':
				if (count($value) > 1) {
					$constraintArray = array();
					foreach ($value as $arrayValue) {
						$logicalAndConstraint[] = $query->like($property, '%'. $arrayValue .'%');
					}
					$constraint = $query->logicalAnd($constraintArray);
				} else {
					$constraint = $query->like($property, '%'. $value .'%');
				}
				break;
			case 'belongsTo':
				if (count($value) > 1) {
					$constraintArray = array();
					foreach ($value as $arrayValue) {
						if ($arrayValue !== NULL) {
							$object = $this->persistenceManager->getObjectByIdentifier($arrayValue, $relationModel->className);
							$logicalAndConstraint[] = $query->equals($property, $object);
						} else {
							$logicalAndConstraint[] = $query->equals($property, NULL);
						}
					}
					$constraint = $query->logicalOr($constraintArray);
				} else {
					if ($value !== NULL) {
						$object = $this->persistenceManager->getObjectByIdentifier($value, $relationModel->className);
						$constraint = $query->equals($property, $object);
					} else {
						$constraint = $query->equals($property, NULL);
					}
				}
				break;
			case 'hasMany':
				if (count($value) > 1) {
					$constraintArray = array();
					foreach ($value as $arrayValue) {
						if ($arrayValue !== NULL) {
							$object = $this->persistenceManager->getObjectByIdentifier($arrayValue, $relationModel->className);
							$logicalAndConstraint[] = $query->equals($property, $object);
						} else {
							$logicalAndConstraint[] = $query->equals($property, NULL);
						}
					}
					$constraint = $query->logicalAnd($constraintArray);
				} else {
					if ($value !== NULL) {
						$object = $this->persistenceManager->getObjectByIdentifier($value, $relationModel->className);
						$constraint = $query->equals($property, $object);
					} else {
						$constraint = $query->equals($property, NULL);
					}
				}
				break;
			default:
				if (count($value) > 1) {
					$constraintArray = array();
					foreach ($value as $arrayValue) {
						$logicalAndConstraint[] = $query->equals($property, $arrayValue);
					}
					$constraint = $query->logicalAnd($constraintArray);
				} else {
					$constraint = $query->equals($property, NULL);
				}
		}

		return $constraint;
	}

	/**
	 * @param string $searchTerm
	 * @param \TYPO3\Flow\Persistence\QueryInterface $query
	 * @return mixed
	 */
	protected function searchTermToConstraints($searchTerm, $query) {
		$constraintArray = array();

		$properties = $this->modelConfigurationManager->getModelPropertyNames($this->getEntityClassName());

		foreach ($properties as $property) {
			$type = $this->modelConfigurationManager->getModelAttributeType($this->getEntityClassName(), $property);
			if ($type === 'string' && !$this->modelConfigurationManager->isRelation($this->getEntityClassName(), $property)) {
				\TYPO3\Flow\var_dump($type, $property);
				\TYPO3\Flow\var_dump($this->modelConfigurationManager->isRelation($this->getEntityClassName(), $property));

				$constraintArray[] = $query->logicalOr($query->like($property, '%'. $searchTerm .'%'));
			}
		}

		return $constraintArray;
	}

}