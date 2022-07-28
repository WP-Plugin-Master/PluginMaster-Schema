<?php

namespace PluginMaster\Schema;


use Closure;
use PluginMaster\Contracts\Schema\SchemaInterface;

class Schema implements SchemaInterface
{
    private static $instance = null;
    private string $sql;
    private string $table = '';
    private string $column = '';
    private array $columns = [];
    private bool $nullable = false;
    private bool $unsigned = false;
    private bool $onUpdateTimeStamp = false;
    private string $defaultValue = '';
    private string $currentColumn = '';
    private bool $primaryKey = false;
    private bool $increment = false;
    private string $foreignData = '';
    private string $tablePrefix;
    private bool $create = false;

    public function __construct()
    {
        global $tablePrefix;
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * @param  string  $table
     * @param  Closure  $closure
     * @return Schema
     */
    public static function create(string $table, Closure $closure): self
    {
        $self = new self();
        $self->sql = 'create table';
        $self->table = $self->tablePrefix.$table;

        $self->create = true;

        if ($closure instanceof Closure) {
            call_user_func($closure, $self);
        }

        $self->sql .= " `".$self->table."`( ".implode(', ', $self->columns).")";

        return $self;
    }

    /**
     * @param  string  $sql
     * @return Schema
     */
    public static function rawSql(string $sql): self
    {
        $self = new self();
        $self->sql = $sql;
        return $self;
    }

    /**
     * @param  string  $column
     * @param  string|int  $length
     * @param  string|int  $places
     * @return Schema
     */
    public function decimal(string $column, string|int $length = 20, string|int $places = 2): self
    {
        $this->currentColumn = "`".$column."` decimal(".$length.",".$places.")";
        $this->addColumn($this->currentColumn);
        return $this;
    }

    /**
     * @param $columnData
     * @return Schema
     */
    private function addColumn($columnData): self
    {
        $this->nullable = false;
        $this->primaryKey = false;
        $this->increment = false;
        $this->unsigned = false;
        $this->onUpdateTimeStamp = false;
        $this->foreignData = '';
        $this->column = $columnData.($this->defaultValue ? ' DEFAULT "'.$this->defaultValue.'"' : '').($this->nullable ? ' NULL' : ' NOT NULL');
        $this->columns[] = $this->column;
        return $this;
    }

    /**
     * @param  string  $column
     * @param  array  $values
     * @return Schema
     */
    public function enum(string $column, array $values): self
    {
        $enumValues = '';
        foreach ($values as $k => $v) {
            $enumValues .= '"'.$v.'",';
        }

        $this->currentColumn = "`".$column."` enum(".substr($enumValues, 0, -1).")";
        $this->addColumn($this->currentColumn);
        return $this;
    }

    /**
     * @param  string  $column
     * @return Schema
     */
    public function intIncrements(string $column): self
    {
        $this->integer($column);
        $this->increment();
        $this->unsigned();
        $this->primaryKey();
        return $this;
    }

    /**
     * @param  string  $column
     * @param  string|int  $length
     * @return Schema
     */
    public function integer(string $column, string|int $length = 10): self
    {
        $this->currentColumn = "`".$column."` int(".$length.")";
        $this->addColumn($this->currentColumn);
        return $this;
    }

    /**
     * @return Schema
     */
    public function increment(): self
    {
        $this->increment = true;
        $this->updateColumn($this->currentColumn);
        return $this;
    }

    /**
     * @param $columnData
     * @return Schema
     */
    public function updateColumn($columnData): self
    {
        $defaultValue = ($this->defaultValue ? ' DEFAULT '.(strtoupper(
                $this->defaultValue
            ) === 'CURRENT_TIMESTAMP' ? strtoupper($this->defaultValue) : '"'.$this->defaultValue.'"') : '');
        $unsigned = ($this->unsigned ? ' UNSIGNED ' : '');
        $nullable = ($this->nullable ? ' NULL' : ' NOT NULL ');
        $increment = ($this->increment ? ' auto_increment ' : '');
        $primaryKey = ($this->primaryKey ? ' PRIMARY KEY' : '');
        $onUpdateTimeStamp = ($this->onUpdateTimeStamp ? ' ON UPDATE CURRENT_TIMESTAMP' : '');
        $foreignData = ($this->foreignData ? $this->foreignData : '');

        $this->column = $columnData.$unsigned.$nullable.$defaultValue.$increment.$primaryKey.$onUpdateTimeStamp.$foreignData;
        $lastIndex = count($this->columns) - 1;
        $this->columns[$lastIndex] = $this->column;
        return $this;
    }

    /**
     * @return Schema
     */
    public function unsigned(): self
    {
        $this->unsigned = true;
        $this->updateColumn($this->currentColumn);
        return $this;
    }

    /**
     * @return Schema
     */
    public function primaryKey(): self
    {
        $this->primaryKey = true;
        $this->updateColumn($this->currentColumn);
        return $this;
    }

    /**
     * @param  string  $column
     * @return Schema
     */
    public function bigIntIncrements(string $column): self
    {
        $this->bigInt($column);
        $this->increment();
        $this->unsigned();
        $this->primaryKey();
        return $this;
    }

    /**
     * @param  string  $column
     * @param  string|int  $length
     * @return Schema
     */
    public function bigInt(string $column, string|int $length = 20): self
    {
        $this->currentColumn = "`".$column."` bigint(".$length.")";
        $this->addColumn($this->currentColumn);
        return $this;
    }

    /**
     * @param  string  $column
     * @param  int|string  $length
     * @return Schema
     */
    public function string(string $column, int|string $length = 255): self
    {
        $this->currentColumn = "`".$column."` varchar(".$length.")";
        $this->addColumn($this->currentColumn);
        return $this;
    }

    /**
     * @param  string  $column
     * @return Schema
     */
    public function text(string $column): self
    {
        $this->currentColumn = "`".$column."` text";
        $this->addColumn($this->currentColumn);
        return $this;
    }

    /**
     * @param  string  $column
     * @return Schema
     */
    public function date(string $column): self
    {
        $this->currentColumn = "`".$column."` date";
        $this->addColumn($this->currentColumn);
        return $this;
    }

    /**
     * @param  string  $column
     * @return Schema
     */
    public function timestamp(string $column): self
    {
        $this->currentColumn = "`".$column."` timestamp";
        $this->addColumn($this->currentColumn);
        return $this;
    }

    /**
     * @return Schema
     */
    public function nullable(): self
    {
        $this->nullable = true;
        $this->updateColumn($this->currentColumn);
        return $this;
    }

    /**
     * @param  mixed  $value
     * @return Schema
     */
    public function default(mixed $value): self
    {
        $this->defaultValue = $value;
        $this->updateColumn($this->currentColumn);
        return $this;
    }

    /**
     * @return Schema
     */
    public function onUpdateTimeStamp(): self
    {
        $this->onUpdateTimeStamp = true;
        $this->updateColumn($this->currentColumn);
        return $this;
    }

    /**
     * @param  string  $column
     * @return Schema
     */
    public function foreign(string $column): self
    {
        $this->foreignData = ', CONSTRAINT '.$this->table.'_'.$column." FOREIGN KEY (`".$column."`) ";
        return $this;
    }

    /**
     * @param  string  $reference
     * @return Schema
     */
    public function on(string $reference): self
    {
        $data = explode('.', $reference);
        $this->foreignData .= "REFERENCES `".$this->tablePrefix.$data[0]."` (`".$data[1]."`) ";
        $this->updateColumn($this->currentColumn);
        return $this;
    }


    /**
     * process sql for execution
     */
    public function execute(): void
    {
        global $wpdb;

        $table = $this->table;
        $charset = $wpdb->get_charset_collate();
        $finalSql = '';
        if ($this->create && $table && ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) !== $table)) {
            $finalSql = $this->getSql().$charset;
        }

        if (!$this->create) {
            $finalSql = $this->getSql().$charset;
        }

        if ($finalSql) {
            $this->executeSQL($finalSql);
        }
    }

    /**
     * @return mixed
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * finally execute sql query
     * @param $sql
     */
    private function executeSQL($sql): void
    {
        require_once(ABSPATH.'/wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }


}


