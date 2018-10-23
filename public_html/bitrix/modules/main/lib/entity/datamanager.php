<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

namespace Bitrix\Main\Entity;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Base entity data manager
 * @package bitrix
 * @subpackage main
 */
abstract class DataManager
{
	const EVENT_ON_BEFORE_ADD = "OnBeforeAdd";
	const EVENT_ON_ADD = "OnAdd";
	const EVENT_ON_AFTER_ADD = "OnAfterAdd";
	const EVENT_ON_BEFORE_UPDATE = "OnBeforeUpdate";
	const EVENT_ON_UPDATE = "OnUpdate";
	const EVENT_ON_AFTER_UPDATE = "OnAfterUpdate";
	const EVENT_ON_BEFORE_DELETE = "OnBeforeDelete";
	const EVENT_ON_DELETE = "OnDelete";
	const EVENT_ON_AFTER_DELETE = "OnAfterDelete";

	/** @var Base */
	protected static $entity;

	/**
	 * @static
	 * @return Base
	 */
	public static function getEntity()
	{
		$class = get_called_class();

		if (!isset(static::$entity[$class]))
		{
			static::$entity[$class] = Base::getInstance($class);
		}

		return static::$entity[$class];
	}

	/**
	 * @abstract
	 */
	public static function getFilePath()
	{
		throw new Main\NotImplementedException("Method getFilePath() must be implemented by successor.");
	}

	public static function getTableName()
	{
		return null;
	}

	public static function getConnectionName()
	{
		return 'default';
	}

	/**
	 * @abstract
	 */
	public static function getMap()
	{
		throw new Main\NotImplementedException("Method getMap() must be implemented by successor.");
	}

	public static function getUfId()
	{
		return null;
	}

	public static function isUts()
	{
		return false;
	}

	public static function isUtm()
	{
		return false;
	}

	public static function getByPrimary($primary, $parameters = array())
	{
		static::normalizePrimary($primary);
		static::validatePrimary($primary);

		$primaryFilter = array();

		foreach ($primary as $k => $v)
		{
			$primaryFilter['='.$k] = $v;
		}

		if (isset($parameters['filter']))
		{
			$parameters['filter'] = array($primaryFilter, $parameters['filter']);
		}
		else
		{
			$parameters['filter'] = $primaryFilter;
		}

		return static::getList($parameters);
	}

	public static function getById($id)
	{
		return static::getByPrimary($id);
	}

	public static function getRowById($id)
	{
		$result = static::getByPrimary($id);
		$row = $result->fetch();

		return (is_array($row)? $row : null);
	}

	public static function getRow($parameters)
	{
		$result = static::getList($parameters);
		$row = $result->fetch();

		return (is_array($row)? $row : null);
	}

	public static function getList($parameters = array())
	{
		$query = new Query(static::getEntity());

		if(!isset($parameters['select']))
		{
			$query->setSelect(array('*'));
		}

		foreach($parameters as $param => $value)
		{
			switch($param)
			{
				case 'select':
					$query->setSelect($value);
					break;
				case 'filter':
					$query->setFilter($value);
					break;
				case 'group':
					$query->setGroup($value);
					break;
				case 'order';
					$query->setOrder($value);
					break;
				case 'limit':
					$query->setLimit($value);
					break;
				case 'offset':
					$query->setOffset($value);
					break;
				case 'count_total':
					$query->countTotal($value);
					break;
				case 'options':
					$query->setOptions($value);
					break;
				case 'runtime':
					foreach ($value as $name => $fieldInfo)
					{
						$query->registerRuntimeField($name, $fieldInfo);
					}
					break;
				case 'data_doubling':
					if($value)
						$query->enableDataDoubling();
					else
						$query->disableDataDoubling();
					break;
				default:
					throw new Main\ArgumentException("Unknown parameter: ".$param, $param);
			}
		}

		return $query->exec();
	}

	public static function getCount()
	{
		$query = new Query(static::getEntity());
		$query->setSelect(array(
			'CNT' => array('expression' => array('COUNT(*)'), 'data_type'=>'integer')
		));
		$result = $query->exec()->fetch();

		return $result['CNT'];
	}

	public static function query()
	{
		return new Query(static::getEntity());
	}

	protected static function normalizePrimary(&$primary, $data = array())
	{
		$entity = static::getEntity();
		$entity_primary = $entity->getPrimaryArray();

		if ($primary === null)
		{
			$primary = array();

			// extract primary from data array
			foreach ($entity_primary as $key)
			{
				/** @var ScalarField $field  */
				$field = $entity->getField($key);
				if ($field->isAutocomplete())
				{
					continue;
				}

				if (!isset($data[$key]))
				{
					throw new \Exception(sprintf(
						'Primary `%s` was not found when trying to query %s row.', $key, static::getEntity()->getName()
					));
				}

				$primary[$key] = $data[$key];
			}
		}
		elseif (is_scalar($primary))
		{
			if (count($entity_primary) > 1)
			{
				throw new \Exception(sprintf(
					'Require multi primary {`%s`}, but one scalar value "%s" found when trying to query %s row.',
					join('`, `', $entity_primary), $primary, static::getEntity()->getName()
				));
			}

			$primary = array($entity_primary[0] => $primary);
		}
	}

	protected static function validatePrimary($primary)
	{
		if (is_array($primary))
		{
			if(empty($primary))
			{
				throw new \Exception(sprintf(
					'Empty primary found when trying to query %s row.', static::getEntity()->getName()
				));
			}

			$entity_primary = static::getEntity()->getPrimaryArray();

			foreach (array_keys($primary) as $key)
			{
				if (!in_array($key, $entity_primary, true))
				{
					throw new \Exception(sprintf(
						'Unknown primary `%s` found when trying to query %s row.',
						$key, static::getEntity()->getName()
					));
				}
			}
		}
		else
		{
			throw new \Exception(sprintf(
				'Unknown type of primary "%s" found when trying to query %s row.', gettype($primary), static::getEntity()->getName()
			));
		}

		// primary values validation
		foreach ($primary as $key => $value)
		{
			if (!is_scalar($value) && !($value instanceof Main\Type\DateTime))
			{
				throw new \Exception(sprintf(
					'Unknown value type "%s" for primary "%s" found when trying to query %s row.',
					gettype($value), $key, static::getEntity()->getName()
				));
			}
		}
	}

	/**
	 * Checks data fields before saving to DB. Result stores in $result object
	 *
	 * @param Result $result
	 * @param mixed $primary
	 * @param array $data
	 * @throws \Exception
	 */
	public static function checkFields(Result $result, $primary, array $data)
	{
		//checks required fields
		foreach (static::getEntity()->getFields() as $field)
		{
			if ($field instanceof ScalarField && $field->isRequired())
			{
				$fieldName = $field->getName();
				if (
					(empty($primary) && (!isset($data[$fieldName]) || $field->isValueEmpty($data[$fieldName])))
					|| (!empty($primary) && isset($data[$fieldName]) && $field->isValueEmpty($data[$fieldName]))
				)
				{
					$result->addError(new FieldError(
						$field,
						Loc::getMessage("MAIN_ENTITY_FIELD_REQUIRED", array("#FIELD#"=>$field->getTitle())),
						FieldError::EMPTY_REQUIRED
					));
				}
			}
		}

		// checks data - fieldname & type & strlen etc.
		foreach ($data as $k => $v)
		{
			if (static::getEntity()->hasField($k) && static::getEntity()->getField($k) instanceof ScalarField)
			{
				$field = static::getEntity()->getField($k);
			}
			elseif (static::getEntity()->hasUField($k))
			{
				// should be continue
				// checking is inside uf manager
				$field = static::getEntity()->getUField($k);
			}
			else
			{
				throw new \Exception(sprintf(
					'Field `%s` not found in entity when trying to query %s row.',
					$k, static::getEntity()->getName()
				));
			}

			$field->validateValue($v, $primary, $data, $result);
		}
	}

	/**
	 * Adds row to entity table
	 *
	 * @param array $data
	 * @return AddResult Contains ID of inserted row
	 */
	public static function add(array $data)
	{
		$entity = static::getEntity();
		$result = new AddResult();

		//event before adding
		$event = new Event($entity, self::EVENT_ON_BEFORE_ADD, array("fields"=>$data));
		$event->send();
		$event->getErrors($result);
		$data = $event->mergeFields($data);

		// check data
		static::checkFields($result, null, $data);

		if(!$result->isSuccess(true))
		{
			return $result;
		}

		//event on adding
		$event = new Event($entity, self::EVENT_ON_ADD, array("fields"=>$data));
		$event->send();

		// save data
		$connection = Main\Application::getConnection();

		$tableName = $entity->getDBTableName();
		$identity = $entity->getAutoIncrement();

		$id = $connection->add($tableName, $data, $identity);

		$result->setId($id);
		$result->setData($data);

		//TODO: save Userfields

		//event after adding
		$event = new Event($entity, self::EVENT_ON_AFTER_ADD, array("id"=>$id, "fields"=>$data));
		$event->send();

		return $result;
	}

	/**
	 * Updates row in entity table by primary key
	 *
	 * @param string|array $primary
	 * @param array $data
	 * @return UpdateResult
	 */
	public static function update($primary, array $data)
	{
		// check primary
		static::normalizePrimary($primary, $data);
		static::validatePrimary($primary);

		$entity = static::getEntity();
		$result = new UpdateResult();

		//event before update
		$event = new Event($entity, self::EVENT_ON_BEFORE_UPDATE, array("id"=>$primary, "fields"=>$data));
		$event->send();
		$event->getErrors($result);
		$data = $event->mergeFields($data);

		// check data
		static::checkFields($result, $primary, $data);

		if(!$result->isSuccess(true))
		{
			return $result;
		}

		//event on update
		$event = new Event($entity, self::EVENT_ON_UPDATE, array("id"=>$primary, "fields"=>$data));
		$event->send();

		// save data
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$tableName = $entity->getDBTableName();

		$update = $helper->prepareUpdate($tableName, $data);

		$id = array();
		foreach ($primary as $k => $v)
		{
			$id[] = $helper->prepareAssignment($tableName, $k, $v);
		}
		$where = implode(' AND ', $id);

		$sql = "UPDATE ".$tableName." SET ".$update[0]." WHERE ".$where;
		$connection->queryExecute($sql, $update[1]);

		$result->setAffectedRowsCount($connection);
		$result->setData($data);

		//TODO: save Userfields

		//event after update
		$event = new Event($entity, self::EVENT_ON_AFTER_UPDATE, array("id"=>$primary, "fields"=>$data));
		$event->send();

		return $result;
	}

	/**
	 * Deletes row in entity table by primary key
	 *
	 * @param string|array $primary
	 * @return DeleteResult
	 */
	public static function delete($primary)
	{
		// check primary
		static::normalizePrimary($primary);
		static::validatePrimary($primary);

		$entity = static::getEntity();
		$result = new DeleteResult();

		//event before delete
		$event = new Event($entity, self::EVENT_ON_BEFORE_DELETE, array("id"=>$primary));
		$event->send();
		if($event->getErrors($result))
			return $result;

		//event on delete
		$event = new Event($entity, self::EVENT_ON_DELETE, array("id"=>$primary));
		$event->send();

		// delete
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$tableName = $entity->getDBTableName();

		$id = array();
		foreach ($primary as $k => $v)
		{
			$id[] = $k." = '".$helper->forSql($v)."'";
		}
		$where = implode(' AND ', $id);

		$sql = "DELETE FROM ".$tableName." WHERE ".$where;
		$connection->queryExecute($sql);

		//event after delete
		$event = new Event($entity, self::EVENT_ON_AFTER_DELETE, array("id"=>$primary));
		$event->send();

		return $result;
	}

	/*
	An inheritor class can define the event handlers for own events.
	Why? To prevent from rewriting the add/update/delete functions.
	These handlers are triggered in the Bitrix\Main\Entity\Event::send() function
	*/
	public static function onBeforeAdd(Event $event){}
	public static function onAdd(Event $event){}
	public static function onAfterAdd(Event $event){}
	public static function onBeforeUpdate(Event $event){}
	public static function onUpdate(Event $event){}
	public static function onAfterUpdate(Event $event){}
	public static function onBeforeDelete(Event $event){}
	public static function onDelete(Event $event){}
	public static function onAfterDelete(Event $event){}
}
