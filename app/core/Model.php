<?php


namespace App\core;


abstract class Model
{
    protected static $fillable = [];
    protected static $tableName = null;

    public static function findById($id)
    {
        $tableName = self::getTableName();
        $sql = "select * from " . $tableName . " where id = " . $id;
        $connection = DB::getConnection();
        $res = $connection->query($sql);
        if ($res->num_rows) {
            return $res->fetch_object(static::class);
        }

    }

    public static function getAll($limit = null, $offset = null)
    {
        $limitStr = null;
        if ($limit || $offset) {
            $limitStr = ' LIMIT ' . ($offset ?? 0) . ',' . ($limit ?? 100);
        }
        $tableName = self::getTableName();
        $sql = "select * from " . $tableName . $limitStr;
        $connection = DB::getConnection();
        $res = $connection->query($sql);
        $arr = [];
        if ($res->num_rows) {

            while ($obj = $res->fetch_object(static::class)) {
                $arr[] = $obj;
            }
        }
        return $arr;
    }

    public function save()
    {
        $table = static::getTableName();
        if (isset($this->id) && !empty($this->id)) {
            $sql = "UPDATE " . $table . " SET ";
            foreach (static::$fillable as $column) {
                $sql .= "`$column` = '".$this->{$column}."' ,";

            }
            $sql  = substr($sql, 0, -1);
            $sql .= "WHERE id = " . $this->id;


            $connection = DB::getConnection();
            $connection->query($sql);
            var_dump($sql);
            return static::findById($this->id);

        } else {
            var_dump($this);
            $values = [];
            foreach (static::$fillable as $column) {
                $values[$column] = $this->{$column};
            }

            return static::create($values);
        }


    }


    public static function create($arr = [])
    {
        $keys = array_keys($arr);
        $keys = '`' . implode('`,`', $keys) . '`';

        $values = array_values($arr);
        $values = "'" . implode("','", $values) . "'";

        $tableName = self::getTableName();
        $connection = DB::getConnection();
        $sql = 'INSERT into ' . $tableName . "($keys) VALUES ($values)";
        $connection->query($sql);

        return static::findById($connection->insert_id);

    }

    public static function getTableName()
    {
        if (static::$tableName) {
            return static::$tableName;
        }

        $tableName = explode('\\', static::class);
        $tableName = end($tableName);
        $tableName = mb_strtolower($tableName) . 's';
        return $tableName;
    }

    public function load(array $arr)
    {

        foreach ($arr as $key => $item){
            if (in_array($key, static::$fillable, true))
            {
                $this->{$key} = $arr[$key];
            }
        }
    }

    public static function delete($id)
    {
        $tableName = self::getTableName();
        $connection = DB::getConnection();
        $sql = "DELETE FROM ".$tableName." WHERE id = ".$id;
        return $connection->query($sql);

    }
}