<?php

namespace PluginMaster\Schema\base;


interface SchemaBase
{
    /**
     * @param $table
     * @param $closure
     * @return mixed
     */
    public static function create($table, $closure);

    /**
     * @param $column
     * @param int $length
     * @param int $places
     * @return mixed
     */
    public function decimal($column, $length, $places);

    /**
     * @param $column
     * @param $values
     * @return mixed
     */
    public function enum($column, $values);

    /**
     * @param $column
     * @return mixed
     */
    public function intIncrements($column);

    /**
     * @param $column
     * @param int $length
     * @return mixed
     */
    public function integer($column, $length);

    /**
     * @return mixed
     */
    public function increment();

    /**
     * @param $columnData
     * @return mixed
     */
    public function updateColumn($columnData);

    /**
     * @return mixed
     */
    public function unsigned();

    /**
     * @return mixed
     */
    public function primaryKey();

    /**
     * @param $column
     * @return mixed
     */
    public function bigIntIncrements($column);

    /**
     * @param $column
     * @param int $length
     * @return mixed
     */
    public function bigInt($column, $length);

    /**
     * @param $column
     * @param int $length
     * @return mixed
     */
    public function string($column, $length);

    /**
     * @param $column
     * @return mixed
     */
    public function text($column);

    /**
     * @param $column
     * @return mixed
     */
    public function date($column);

    /**
     * @param $column
     * @return mixed
     */
    public function timestamp($column);

    /**
     * @return mixed
     */
    public function nullable();

    /**
     * @param $value
     * @return mixed
     */
    public function default($value);

    /**
     * @return mixed
     */
    public function onUpdateTimeStamp();

    /**
     * @param $column
     * @return mixed
     */
    public function foreign($column);

    /**
     * @param $reference
     * @return mixed
     */
    public function on($reference);


}
