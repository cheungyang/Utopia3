<?php
namespace Utopia\Components\DataAccess;

interface IDataSource
{
    public function connect();
    public function disconnect();
	public function begin();
	public function commit();
	public function rollback();

	public function insert($entity, array $inputs);
	public function update($entity, array $criteria, array $updates, $update_count=1);
	public function delete($entity, array $criteria, $realdelete=false, $delete_count=1);
	public function fetch(DataQuery $q);
}