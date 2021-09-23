<?php
namespace  PluginMaster\Schema;


use Closure;
use PluginMaster\Contracts\Schema\SchemaInterface;

class Schema implements SchemaInterface
{
    private static $instance = null;
    private $sql;
    private $table = '';
    private $column = '';
    private $columns = [];
    private $nullable = false;
    private $unsigned = false;
    private $onUpdateTimeStamp = false;
    private $defaultValue = '';
    private $currentColumn = '';
    private $primaryKey = false;
    private $increment = false;
    private $foreignData = '';
    private $tableName = '';
    private $table_prefix;
    private $create = false;

    public function __construct() {
        global $table_prefix;
        $this->table_prefix = $table_prefix;
    }

    /**
     * @param $table
     * @param $closure
     * @return Schema|mixed
     */
    public static function create( $table, $closure ) {
        $self        = new self();
        $self->sql   = 'create table';
        $self->table = $self->table_prefix . $table;

        $self->create = true;

        if ( $closure instanceof Closure ) {
            call_user_func( $closure, $self );
        }

        $self->sql .= " `" . $self->table . "`( " . implode( ', ', $self->columns ) . ")";

        return $self;
    }

    /**
     * @param $sql
     * @return mixed
     */
    public static function rawSql( $sql ) {
        $self      = new self();
        $self->sql = $sql;
        return $self;
    }

    /**
     * @param $column
     * @param int $length
     * @param int $places
     * @return $this|mixed
     */
    public function decimal( $column, $length = 20, $places = 2 ) {

        $this->currentColumn = "`" . $column . "` decimal(" . $length . "," . $places . ")";
        $this->addColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @param $columnData
     * @return $this
     */
    private function addColumn( $columnData ) {
        $this->nullable          = false;
        $this->primaryKey        = false;
        $this->increment         = false;
        $this->unsigned          = false;
        $this->onUpdateTimeStamp = false;
        $this->defaultValue      = '';
        $this->foreignData       = '';
        $this->column            = $columnData . ($this->defaultValue ? ' DEFAULT "' . $this->defaultValue . '"' : '') . ($this->nullable ? ' NULL' : ' NOT NULL');
        array_push( $this->columns, $this->column );
        return $this;
    }

    /**
     * @param $column
     * @param $values
     * @return $this|mixed
     */
    public function enum( $column, $values ) {
        $enumValues = '';
        foreach ( $values as $k => $v ) {
            $enumValues .= '"' . $v . '",';
        }

        $this->currentColumn = "`" . $column . "` enum(" . substr( $enumValues, 0, -1 ) . ")";
        $this->addColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @param $column
     * @return $this|mixed
     */
    public function intIncrements( $column ) {
        $this->integer( $column );
        $this->increment();
        $this->unsigned();
        $this->primaryKey();
        return $this;
    }

    /**
     * @param $column
     * @param int $length
     * @return $this|mixed
     */
    public function integer( $column, $length = 10 ) {

        $this->currentColumn = "`" . $column . "` int(" . $length . ")";
        $this->addColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @return $this|mixed
     */
    public function increment() {
        $this->increment = true;
        $this->updateColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @param $columnData
     * @return $this|mixed
     */
    public function updateColumn( $columnData ) {
        $defaultValue      = ($this->defaultValue ? ' DEFAULT ' . (strtoupper( $this->defaultValue ) === 'CURRENT_TIMESTAMP' ? strtoupper( $this->defaultValue ) : '"' . $this->defaultValue . '"') : '');
        $unsigned          = ($this->unsigned ? ' UNSIGNED ' : '');
        $nullable          = ($this->nullable ? ' NULL' : ' NOT NULL ');
        $increment         = ($this->increment ? ' auto_increment ' : '');
        $primaryKey        = ($this->primaryKey ? ' PRIMARY KEY' : '');
        $onUpdateTimeStamp = ($this->onUpdateTimeStamp ? ' ON UPDATE CURRENT_TIMESTAMP' : '');
        $foreignData       = ($this->foreignData ? $this->foreignData : '');

        $this->column                = $columnData . $unsigned . $nullable . $defaultValue . $increment . $primaryKey . $onUpdateTimeStamp . $foreignData;
        $lastIndex                   = count( $this->columns ) - 1;
        $this->columns[ $lastIndex ] = $this->column;
        return $this;
    }

    /**
     * @return $this|mixed
     */
    public function unsigned() {
        $this->unsigned = true;
        $this->updateColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @return $this|mixed
     */
    public function primaryKey() {
        $this->primaryKey = true;
        $this->updateColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @param $column
     * @return $this|mixed
     */
    public function bigIntIncrements( $column ) {
        $this->bigInt( $column );
        $this->increment();
        $this->unsigned();
        $this->primaryKey();
        return $this;
    }

    /**
     * @param $column
     * @param int $length
     * @return $this|mixed
     */
    public function bigInt( $column, $length = 20 ) {

        $this->currentColumn = "`" . $column . "` bigint(" . $length . ")";
        $this->addColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @param $column
     * @param int $length
     * @return $this|mixed
     */
    public function string( $column, $length = 255 ) {

        $this->currentColumn = "`" . $column . "` varchar(" . $length . ")";
        $this->addColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @param $column
     * @return $this|mixed
     */
    public function text( $column ) {

        $this->currentColumn = "`" . $column . "` text";
        $this->addColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @param $column
     * @return $this|mixed
     */
    public function date( $column ) {

        $this->currentColumn = "`" . $column . "` date";
        $this->addColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @param $column
     * @return $this|mixed
     */
    public function timestamp( $column ) {
        $this->currentColumn = "`" . $column . "` timestamp";
        $this->addColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @return $this|mixed
     */
    public function nullable() {
        $this->nullable = true;
        $this->updateColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @param $value
     * @return $this|mixed
     */
    public function default( $value ) {
        $this->defaultValue = $value;
        $this->updateColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @return $this|mixed
     */
    public function onUpdateTimeStamp() {
        $this->onUpdateTimeStamp = true;
        $this->updateColumn( $this->currentColumn );
        return $this;
    }

    /**
     * @param $column
     * @return $this|mixed
     */
    public function foreign( $column ) {
        $this->foreignData = ', CONSTRAINT ' . $this->table . '_' . $column . " FOREIGN KEY (`" . $column . "`) ";
        return $this;
    }

    /**
     * @param $reference
     * @return $this|mixed
     */
    public function on( $reference ) {
        $data              = explode( '.', $reference );
        $this->foreignData .= "REFERENCES `" . $this->table_prefix . $data[0] . "` (`" . $data[1] . "`) ";
        $this->updateColumn( $this->currentColumn );
        return $this;
    }


    /**
     * process sql for execution
     */
    public function execute() {

        global $wpdb;

        $table    = $this->table;
        $charset  = $wpdb->get_charset_collate();
        $finalSql = '';
        if ( $this->create && $table && ($wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table) ) {
            $finalSql = $this->getSql() . $charset;
        }

        if ( !$this->create ) {
            $finalSql = $this->getSql() . $charset;
        }

        if ( $finalSql ) $this->executeSQL( $finalSql );
    }

    /**
     * @return mixed
     */
    public function getSql() {
        return $this->sql;
    }

    /**
     * finally execute sql query
     * @param $sql
     */
    private function executeSQL( $sql ) {
        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
        dbDelta( $sql );
    }


}


?>
