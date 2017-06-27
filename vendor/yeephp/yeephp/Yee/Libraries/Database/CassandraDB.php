<?php
namespace Yee\Libraries\Database;
use Cassandra;
class CassandraDB
{

    /**
     * @var bool Echo errors
     */
    public $showErrors = true;

    /**
     * @var array All information required for the table
     */
    protected $_tableInfo = array();

    /**
     * @var string Last generated uuid
     */
    protected $_generatedUuid;

    /**
     * @var string Query to be executed
     */
    protected $_query;

    /**
     * @var string Last executed query
     */
    protected $_lastQuery;

    /**
     * @var array Arguments which are binded
     */
    protected $_argumentsToBind = array();

    /**
     * @var array Where arguments (columns, values, operators)
     */
    protected $_where = array();

    /**
     * @var array If arguments (columns, values, operators)
     */
    protected $_if = array();

    /**
     * @var null|array TTL query option
     */
    protected $_ttl = null;

    /**
     * @var null|array Timestamp query option
     */
    protected $_timestamp = null;

    /**
     * @var null|boolean IF EXISTS query option
     */
    protected $_exists = null;

    /**
     * @var null|boolean IF NOT EXISTS query option
     */
    protected $_notExists = null;

    /**
     * @var null|string ORDER BY query option
     */
    protected $_orderBy = null;

    /**
     * @var CassandraDB
     */
    protected static $_instance;

    /**
     * @var object
     */
    protected $_cluster;

    /**
     * @var object
     */
    protected $_session;

    /**
     * @var object
     */
    protected $_keyspace;

    /**
     * @var bool
     */
    protected $isConnected = false;

    /**
     * @var string|mixed Connection details
     */
    protected $seeds;
    protected $username;
    protected $password;
    protected $port;
    protected $keyspace;

    /**
     * CassandraDB constructor.
     * @param $seedNodes
     * @param string $username
     * @param string $password
     * @param null $port
     * @param $keyspace
     */
    public function __construct($seedNodes, $username = '', $password = '', $port = null, $keyspace)
    {
        $this->seeds = is_array($seedNodes) ? implode(',', $seedNodes) : $seedNodes;
        $this->username = $username;
        $this->password = $password;
        $this->port = empty($port) === false ? (int)$port : 9042;
        $this->keyspace = $keyspace;

        self::$_instance = $this;
    }

    /**
     * Connects to Cassandra database and initializes necessary objects.
     *
     * @return bool
     */
    public function connect()
    {
        try {

            $this->_cluster = Cassandra::cluster()->withContactPoints($this->seeds);

            if ($this->username != '' && $this->password != '')
                $this->_cluster = $this->_cluster->withCredentials($this->username, $this->password);


            $this->_cluster = $this->_cluster->withPort($this->port);
            $this->_session = $this->_cluster->build()->connect($this->keyspace);
            $this->_keyspace = $this->_session->schema()->keyspace($this->keyspace);

            $this->isConnected = true;

        } catch (Cassandra\Exception $e) {
            $this->_getError($e, __LINE__);
            return false;
        }
    }

    /**
     * A method of returning the static instance to allow access to the
     * instantiated object from within another class.
     * Inheriting this class would require reloading connection info.
     *
     * @return CassandraDB
     */
    public function getInstance()
    {
        return self::$_instance;
    }

    /**
     * Resets all parameters after query execution.
     */
    protected function reset()
    {
        $this->_lastQuery = $this->_query;

        $this->_query = null;
        $this->_argumentsToBind = array();
        $this->_where = array();
        $this->_if = array();
        $this->_ttl = null;
        $this->_orderBy = null;
        $this->_timestamp = null;
        $this->_exists = null;
        $this->_notExists = null;
    }

    /**
     * A method which returns the last generated uuid.
     *
     * @return null|string
     */
    public function generatedUuid()
    {
        return $this->_generatedUuid->uuid() ?? null;
    }

    /**
     * A method which returns the last executed query.
     *
     * @return string
     */
    public function getLastQuery()
    {
        return $this->_lastQuery;
    }

    /**
     * Executes a passed raw query and returns the result.
     *
     * @param string $query
     * @param null $data
     * @param int $itemsPerPage
     * @return array|bool|mixed|null
     */
    public function rawQuery($query, $data = null, $itemsPerPage = 0)
    {
        if (!$this->isConnected)
            $this->connect();

        if ($data !== null) {
            $tableName = $this->_getRawTable($query);

            if ($this->_getTableInfo($tableName, $data) === false)
                return false;

            foreach ($data as $column => $value) {

                if (is_numeric($column)) {
                    $this->_argumentsToBind[] = $value;
                } else {
                    $this->_argumentsToBind[] = $this->_convertToCassandra($column, $value);
                }
            }
        }

        $this->_query = $query;

        $result = $this->_executeSimpleQuery($itemsPerPage);

        $this->reset();

        if (stripos($query, 'select') !== false)
            return $this->_extractRows($result, false, $itemsPerPage);

        return $this->_extractResult($result);
    }

    /**
     * A convenient method for SELECT query.
     *
     * @param $tableName
     * @param null $numRows
     * @param string $columns
     * @param bool $allowFiltering
     * @param int $itemsPerPage
     * @return array|bool|mixed|null
     */
    public function get($tableName, $numRows = null, $columns = '*', $allowFiltering = false, $itemsPerPage = 0)
    {
        return $this->_get($tableName, $numRows, $columns, $allowFiltering, $itemsPerPage);
    }

    /**
     * A convenient method for SELECT query with a single result.
     *
     * @param $tableName
     * @param string $columns
     * @param bool $allowFiltering
     * @return array|bool|mixed|null
     */
    public function getOne($tableName, $columns = '*', $allowFiltering = false)
    {
        return $this->_get($tableName, 1, $columns, $allowFiltering, 0, false, true);
    }

    /**
     * A convenient method for SELECT query which returns JSON results.
     *
     * @param $tableName
     * @param null $numRows
     * @param string $columns
     * @param bool $allowFiltering
     * @param int $itemsPerPage
     * @return array|bool|mixed|null
     */
    public function getAsJson($tableName, $numRows = null, $columns = '*', $allowFiltering = false, $itemsPerPage = 0)
    {
        return $this->_get($tableName, $numRows, $columns, $allowFiltering, $itemsPerPage, true);
    }

    /**
     * A convenient method for INSERT query.
     *
     * @param $tableName
     * @param $insertData
     * @return bool
     */
    public function insert($tableName, $insertData)
    {
        return $this->_insert($tableName, $insertData);
    }

    /**
     * A convenient method for INSERT query which inserts data as JSON encoded.
     *
     * @param $tableName
     * @param $insertData
     * @return bool
     */
    public function insertAsJson($tableName, $insertData)
    {
        return $this->_insert($tableName, $insertData, true);
    }

    /**
     * A convenient method for UPDATE query. Be sure to first call the "where" method.
     *
     * @param $tableName
     * @param $updateData
     * @return bool
     */
    public function update($tableName, $updateData)
    {
        return $this->_update($tableName, $updateData);
    }

    /**
     * A convenient method for DELETE query. Be sure to first call the "where" method.
     *
     * @param $tableName
     * @param null $columns
     * @return bool
     */
    public function delete($tableName, $columns = null)
    {
        return $this->_delete($tableName, $columns);
    }

    /**
     * This method allows you to specify multiple (method chaining optional) AND WHERE statements for CQL queries.
     *
     * @param $column
     * @param $value
     * @param string $operator
     * @return $this
     */
    public function where($column, $value, $operator = '=')
    {
        $this->_where['column'][] = $column;
        $this->_where['value'][] = $value;
        $this->_where['operator'][] = strtoupper($operator);
        return $this;
    }

    /**
     * This method adds an option to the query itself (method chaining optional).
     * It can be called multiple times or pass all options as an array.
     *
     * @param string|mixed $options
     * @return $this
     */
    public function withOptions($options)
    {
        foreach ((array)$options as $option => $value) {
            if (is_numeric($option))
                $option = $value;

            $option = strtoupper($option);

            switch ($option) {
                case 'TTL':
                    $this->_ttl = $value;
                    break;
                case 'TIMESTAMP':
                    $this->_timestamp = $value;
                    break;
                case 'IF EXISTS':
                    $this->_exists = true;
                    break;
                case 'IF NOT EXISTS':
                    $this->_notExists = true;
                    break;
            }
        }

        return $this;
    }

    /**
     * This method adds an ORDER BY option to SELECT queries (method chaining optional).
     *
     * @param string $column
     * @param string $order
     * @return $this
     */
    public function orderBy($column, $order)
    {
        $this->_orderBy = $column . ' ' . strtoupper($order);
        return $this;
    }

    /**
     * This method adds an IF condition to INSERT and DELETE queries (method chaining optional).
     *
     * @param string $column
     * @param mixed $value
     * @param string $operator
     * @return $this
     */
    public function if ($column, $value, $operator = '=')
    {
        $this->_if[] = [$column, $value, $operator];
        return $this;
    }

    /**
     * Method which detects the table name of a raw query
     *
     * @param string $query
     * @return string
     */
    protected function _getRawTable($query)
    {
        $isSelect = stripos($query, 'select');
        if ($isSelect !== false) {
            $table = trim(substr($query, stripos($query, 'from') + 4));
            return substr($table, 0, strpos($table, ' '));
        }

        $isInsert = stripos($query, 'insert');
        if ($isInsert !== false) {
            $table = trim(substr($query, stripos($query, 'into') + 4));
            return substr($table, 0, strpos($table, ' '));
        }

        $isDelete = stripos($query, 'delete');
        if ($isDelete !== false) {
            $table = trim(substr($query, stripos($query, 'from') + 4));
            return substr($table, 0, strpos($table, ' '));
        }

        $isUpdate = stripos($query, 'update');
        if ($isUpdate !== false) {
            $table = trim(substr($query, stripos($query, 'update') + 6));
            return substr($table, 0, strpos($table, ' '));
        }
    }

    /**
     * Method which does all necessary steps to build a SELECT query, execute it and return all results.
     *
     * @param $tableName
     * @param null $numRows
     * @param string $columns
     * @param bool $allowFiltering
     * @param int $itemsPerPage
     * @param bool $asJson
     * @param bool $singleRow
     * @return array|bool|mixed|null
     */
    protected function _get($tableName, $numRows = null, $columns = '*', $allowFiltering = false, $itemsPerPage = 0, $asJson = false, $singleRow = false)
    {
        if (!$this->isConnected)
            $this->connect();

        if ($this->_getTableInfo($tableName) === false)
            return false;

        if( is_array( $columns ) )
            $columns = implode( ',', $columns );
            
        if (empty($columns))
            $columns = '*';

        if (is_string($allowFiltering))
            $allowFiltering = strtoupper(trim($allowFiltering)) == 'ALLOW FILTERING';

        if ($itemsPerPage === null)
            $itemsPerPage = 0;

        $this->_buildSelectQuery($tableName, $columns, $numRows, $allowFiltering, $asJson);

        $result = $this->_executeSimpleQuery($itemsPerPage);
		
        $this->reset();

        return $this->_extractRows($result, $asJson, $itemsPerPage, $singleRow);
    }

    /**
     * Method which does all necessary steps to build an INSERT query, execute it and return it's status.
     *
     * @param $tableName
     * @param $insertData
     * @param bool $isJson
     * @return bool
     */
    protected function _insert($tableName, $insertData, $isJson = false)
    {
        if (!$this->isConnected)
            $this->connect();

        if ($this->_getTableInfo($tableName) === false)
            return false;

        $this->_buildInsertQuery($tableName, $insertData, $isJson);

        $result = $this->_executeSimpleQuery();

        $this->reset();

        return $this->_extractResult($result);
    }

    /**
     * Method which does all necessary steps to build an UPDATE query, execute it and return it's status.
     *
     * @param $tableName
     * @param $updateData
     * @return bool
     */
    protected function _update($tableName, $updateData)
    {
        if (!$this->isConnected)
            $this->connect();

        if ($this->_getTableInfo($tableName) === false)
            return false;

        $this->_buildUpdateQuery($tableName, $updateData);

        $result = $this->_executeSimpleQuery();

        $this->reset();

        return $this->_extractResult($result);
    }

    /**
     * Method which does all necessary steps to build a DELETE query, execute it and return it's status.
     *
     * @param $tableName
     * @param $columns
     * @return bool
     */
    protected function _delete($tableName, $columns)
    {
        if (!$this->isConnected)
            $this->connect();

        if ($this->_getTableInfo($tableName) === false)
            return false;

        $this->_buildDeleteQuery($tableName, $columns);

        $result = $this->_executeSimpleQuery();

        $this->reset();

        return $this->_extractResult($result);
    }

    /**
     * Method which builds SELECT query and WHERE clause.
     *
     * @param $tableName
     * @param $columns
     * @param $rows
     * @param $allowFiltering
     * @param $asJson
     */
    protected function _buildSelectQuery($tableName, $columns, $rows, $allowFiltering, $asJson)
    {
        if ($asJson) {
            $this->_query = 'SELECT JSON ' . $columns . ' FROM ' . $tableName;
        } else {
            $this->_query = 'SELECT ' . $columns . ' FROM ' . $tableName;
        }

        $this->_buildWhere();

        if ($this->_orderBy != null)
            $this->_query .= ' ORDER BY ' . $this->_orderBy;

        if ($rows != null)
            $this->_query .= ' LIMIT ' . (int)$rows;

        if ($allowFiltering === true)
            $this->_query .= ' ALLOW FILTERING';
    }

    /**
     * Method which builds INSERT query.
     * This method will put placeholders (?) for binding arguments.
     * During creation all arguments are added for binding.
     *
     * @param $tableName
     * @param $insertData
     * @param $isJson
     */
    protected function _buildInsertQuery($tableName, $insertData, $isJson)
    {
        $this->_query = 'INSERT INTO ' . $tableName;

        if ($isJson) {
            $this->_query .= ' JSON ?';
            $this->_argumentsToBind[] = $insertData;
        } else {
			
			if(!is_array($insertData))
				return;
			
            $columnNames = array_keys($insertData);

            foreach ($columnNames as $column) {
                $value = $insertData[$column];
                $this->_argumentsToBind[] = $this->_convertToCassandra($column, $value);
            }

            $this->_query .= ' (' . implode(', ', $columnNames);
            $this->_query .= ') VALUES (';
            $this->_query .= rtrim(str_repeat('?,', count($columnNames)), ',') . ')';
        }

        if ($this->_notExists === true)
            $this->_query .= ' IF NOT EXISTS';

        $this->_buildTtlTimestamp();
    }

    /**
     * Method which builds UPDATE query, WHERE clause and IF conditions.
     * This method will put placeholders (?) for binding arguments.
     * During creation all arguments are added for binding.
     *
     * @param $tableName
     * @param $updateData
     */
    protected function _buildUpdateQuery($tableName, $updateData)
    {
        $this->_query .= 'UPDATE ' . $tableName;

        $this->_buildTtlTimestamp();

        $this->_query .= ' SET';

        foreach ($updateData as $column => $value) {

            if ($this->_tableInfo['columns'][$column] == 'counter') {
                $this->_query .= ' ' . $column . ' = ' . $column . ' + ?,';
            } else {
                $this->_query .= ' ' . $column . ' = ?,';
            }

            $this->_argumentsToBind[] = $this->_convertToCassandra($column, $value);
        }
        $this->_query = rtrim($this->_query, ',');

        $this->_buildWhere();

        if ($this->_exists)
            $this->_query .= ' IF EXISTS';

        if ($this->_notExists)
            $this->_query .= ' IF NOT EXISTS';

        $this->_buildIf();
    }

    /**
     * Method which builds DELETE query, WHERE clause and IF conditions.
     *
     * @param $tableName
     * @param null $columns
     */
    protected function _buildDeleteQuery($tableName, $columns = null)
    {
        $this->_query .= 'DELETE ';

        if ($columns !== null) {
            $columns = is_array($columns) ? implode(',', $columns) : $columns;
            $this->_query .= $columns . ' ';
        }

        $this->_query .= 'FROM ' . $tableName;

        $this->_buildTtlTimestamp();

        $this->_buildWhere();

        if ($this->_exists)
            $this->_query .= ' IF EXISTS';

        $this->_buildIf();
    }

    /**
     * Method which builds the WHERE clause to the query.
     * This method will put placeholders (?) for binding arguments.
     * During build parameters are added for binding.
     */
    protected function _buildWhere()
    {
        if (empty($this->_where))
            return;

        for ($i = 0; $i < count($this->_where['column']); $i++) {

            $column = $this->_where['column'][$i];
            $value = $this->_where['value'][$i];
            $operator = $this->_where['operator'][$i];

            $this->_query .= $i == 0 ? ' WHERE ' : ' AND ';

            if (!is_array($column) && !is_array($value)) {

                $this->_argumentsToBind[] = $this->_convertToCassandra($column, $value);

                if ($operator != '=' && in_array($column, $this->_tableInfo['partitionKey'])) {

                    $this->_query .= 'token(' . $column . ') ' . $operator . ' token(?)';
                    continue;
                }

                $this->_query .= $column . ' ' . $operator . ' ?';

            } else {

                $this->_query .= $this->_buildArrayWhere($column);
                $this->_query .= ' ' . $operator . ' ';
                $this->_query .= $this->_buildArrayWhere($value, true, $i);

            }
        }
    }

    /**
     * Method responsible for building WHERE conditions with placeholders.
     * This method will put placeholders (?) for binding arguments.
     * During build parameters are added for binding.
     *
     * @param $array
     * @param bool $withPlaceholders
     * @param int $i
     * @param int $columnIndex
     * @return string
     */
    protected function _buildArrayWhere($array, $withPlaceholders = false, $i = 0, $columnIndex = 0)
    {
        $where = '';

        if (is_array($array)) {

            $where .= '(';

            foreach ($array as $item) {

                if (is_array($item)) {

                    $where .= $this->_buildArrayWhere($item, $withPlaceholders, $i, $columnIndex) . ',';
                    $columnIndex++;
                } else {

                    if ($withPlaceholders) {

                        $column = is_array($this->_where['column'][$i]) ? $this->_where['column'][$i][$columnIndex] : $this->_where['column'][$i];
                        $this->_argumentsToBind[] = $this->_convertToCassandra($column, $item);
                        $where .= '?,';
                    } else {
                        $where .= $item . ',';
                    }
                }
            }

            $where = rtrim($where, ',') . ')';
        } else {
            $where .= $array;
        }

        return $where;
    }

    /**
     * Method which builds the IF conditions to the query.
     */
    protected function _buildIf()
    {
        if (empty($this->_if))
            return;

        $length = count($this->_if);

        for ($i = 0; $i < $length; $i++) {
            list($column, $value, $operator) = $this->_if[$i];

            if ($i == 0) {
                $this->_query .= ' IF ' . $column . ' ' . $operator . ' ' . $value;
            } else {
                $this->_query .= ' AND ' . $column . ' ' . $operator . ' ' . $value;
            }
        }
    }

    /**
     * Method which builds TTL and TIMESTAMP options to the query.
     */
    protected function _buildTtlTimestamp()
    {
        if ($this->_ttl !== null && $this->_timestamp !== null) {

            $this->_query .= ' USING TTL ' . $this->_ttl . ' AND TIMESTAMP ' . $this->_timestamp;

        } else if ($this->_ttl !== null) {

            $this->_query .= ' USING TTL ' . $this->_ttl;

        } else if ($this->_timestamp !== null) {

            $this->_query .= ' USING TIMESTAMP ' . $this->_timestamp;
        }
    }

    /**
     * Method which builds the Cassandra ExecutionOptions object
     *
     * @param array $arguments
     * @param int $itemsPerPage
     * @return bool|\Cassandra\ExecutionOptions
     */
    protected function _buildExecutionOptions($arguments = array(), $itemsPerPage = 0)
    {
        $executionOptions = array();

        if (!empty($arguments))
            $executionOptions['arguments'] = $arguments;

        if ($itemsPerPage > 0)
            $executionOptions['page_size'] = $itemsPerPage;

        try {
            return new Cassandra\ExecutionOptions($executionOptions);
        } catch (Cassandra\Exception $e) {
            $this->_getError($e, __LINE__);
            return false;
        }
    }

    /**
     * Method which generates a statement and returns the result of the executed statement.
     *
     * @param int $itemsPerPage
     * @return bool
     */
    protected function _executeSimpleQuery($itemsPerPage = 0)
    {
        if (!$this->isConnected)
            $this->connect();

        $executionOptions = $this->_buildExecutionOptions($this->_argumentsToBind, $itemsPerPage);

        try {
            $statement = new Cassandra\SimpleStatement($this->_query);

            if ($statement === false || $executionOptions === false)
                return false;

            return $this->_session->execute($statement, $executionOptions);

        } catch (Cassandra\Exception $e) {
            $this->_getError($e, __LINE__);
            return false;
        }
    }

    /**
     * Method which returns a Cassandra prepared statement object.
     *
     * @return mixed
     */
    protected function _getPreparedStatement()
    {
        return $this->_session->prepare($this->_query);
    }

    /**
     * Method which executes a prepared statement.
     *
     * @param $statement
     * @return bool
     */
    protected function _executePreparedQuery($statement)
    {
        if (!$this->isConnected)
            $this->connect();

        try {
            $executionOptions = $this->_buildExecutionOptions($this->_argumentsToBind);

            if ($statement === false || $executionOptions === false)
                return false;

            return $this->_session->execute($statement, $executionOptions);
        } catch (Cassandra\Exception $e) {
            $this->_getError($e, __LINE__);
            return false;
        }
    }

    /**
     * Method which gets the entire table information.
     *
     * @param $tableName
     * @param array $columns
     * @return bool
     */
    protected function _getTableInfo($tableName, $columns = array())
    {
        if (!$this->isConnected)
            $this->connect();

        if ($this->_keyspace === false || $this->_keyspace === null)
            return false;

        $cassandraTable = $this->_keyspace->table($tableName);
		
		if($cassandraTable === false)
			$cassandraTable = $this->_keyspace->materializedView($tableName);

        if ($cassandraTable === false)
            return false;

        foreach ($cassandraTable->partitionKey() as $pk) {
            $this->_tableInfo['partitionKey'][] = $pk->name();
        }

        foreach ($cassandraTable->clusteringKey() as $ck) {
            $this->_tableInfo['clusteringKey'][] = $ck->name();
        }

        foreach ($cassandraTable->columns() as $column) {

            $name = $column->name();
            $type = $column->type()->name();

            if ($type == 'set' || $type == 'list') {

                $collectionType = $column->type()->valueType()->name();
                $this->_tableInfo['collection'][$name] = $collectionType;
            } else if ($type == 'map') {

                $collectionKeyType = $column->type()->keyType()->name();
                $collectionValType = $column->type()->valueType()->name();
                $this->_tableInfo['collection'][$name] = [$collectionKeyType, $collectionValType];
            }

            $this->_tableInfo['columns'][$name] = $type;
        }

        return true;
    }

    /**
     * Method which extracts all rows from the Cassandra Rows object after a SELECT query
     *
     * @param $executionResult
     * @param $asJson
     * @param int $itemsPerPage
     * @param bool $singleRow
     * @return array|bool|mixed|null
     */
    protected function _extractRows($executionResult, $asJson, $itemsPerPage = 0, $singleRow = false)
    {
        if ($executionResult == false)
            return false;

        if ($executionResult->count() == 0)
            return null;

        $extractedResult = array();

        if ($itemsPerPage > 0) {

            do {
                $extracted = $this->_extractRow($executionResult, $asJson);
                if ($extracted == null || empty($extracted))
                    break;
                array_push($extractedResult, $extracted);
            } while (($executionResult = $executionResult->nextPage()) != null);
        } else {

            $extractedResult = $this->_extractRow($executionResult, $asJson);

            if ($singleRow)
                return $extractedResult[0];
        }

        return $extractedResult;
    }

    /**
     * Method which extracts all values from a row and converts them if necessary.
     *
     * @param $result
     * @param $asJson
     * @return array
     */
    protected function _extractRow($result, $asJson)
    {
        $results = array();

        if ($asJson) {

            foreach ($result as $row) {
                array_push($results, $row['[json]']);
            }

        } else {

            foreach ($result as $row) {
                $convertedRow = array();
                foreach ($row as $column => $value) {
                    if ($value instanceof Cassandra\Value) {
                        $value = $this->_convertFromCassandra($value);
                    }
                    $convertedRow[$column] = $value;
                }
                array_push($results, $convertedRow);
            }
        }

        return $results;
    }

    /**
     * Method which extracts the result from INSERT|UPDATE|DELETE query.
     *
     * @param $result
     * @return bool
     */
    protected function _extractResult($result)
    {
        if ($result == false)
            return false;

        if ($result->count() == 0)
            return true;

        if ($result->count() == 1 && isset($result[0]["[applied]"]))
            return $result[0]["[applied]"];
    }

    /**
     * Method which converts a Cassandra object to regular value.
     *
     * @param $value
     * @return int
     */
    protected function _convertFromCassandra($value)
    {
        $type = $value->type()->name();

        switch ($type) {
			case 'bigint':
            case 'smallint':
            case 'tinyint':
            case 'varint':
                return (int)$value->value();
            case 'decimal':
            case 'float':
                return $value->toDouble();
            case 'blob':
                return $value->toBinaryString();
            case 'date':
            case 'timestamp':
                return $value->toDateTime();
            case 'inet':
                return $value->address();
            case 'time':
                return $value->seconds();
            case 'timeuuid':
            case 'uuid':
                return $value->uuid();
            default:
                return $value;
        }
    }

    /**
     * Method which converts a value to Cassandra object.
     *
     * @param $columnName
     * @param $value
     * @return \Cassandra\Timeuuid|\Cassandra\Tinyint|\Cassandra\Uuid|\Cassandra\Varint|DateTime|false|string
     */
    protected function _convertToCassandra($columnName, $value)
    {
        $columnType = $this->_tableInfo['columns'][$columnName] ?? false;

		try{
			switch ($columnType) {
				case 'bigint':
				case 'counter':
					return new Cassandra\Bigint($value);
				case 'decimal':
					return new Cassandra\Decimal(strval($value));
				case 'float':
					return new Cassandra\Float(strval($value));
				case 'timestamp':
					return new Cassandra\Timestamp(is_int($value) ? $value : strtotime($value));
				case 'uuid':
					$this->_generatedUuid = $value != null
						? new Cassandra\Uuid($value)
						: new Cassandra\Uuid();
					return $this->_generatedUuid;
				case 'boolean':
					return (boolean)$value;
				case 'blob':
					return new Cassandra\Blob($value);
				case 'date':
					if (is_int($value)) {
						return new Cassandra\Date($value);
					} else if (is_string($value)) {
						$date = date_create_from_format('Y-m-d', $value);
						if($date !== false){
							return Cassandra\Date::fromDateTime($date);
						}
					} else {
						return Cassandra\Date::fromDateTime($value);
					}
				case 'inet':
					return new Cassandra\Inet($value);
				case 'smallint':
					return new Cassandra\Smallint(strval($value));
				case 'time':
					if (is_int($value)) {
						return new Cassandra\Time($value * 1000000000);
					} else if (is_string($value)) {
						if (strpos($value, '.') === false) {
							$value = date_create_from_format('H:i:s', $value);
							if($value !== false){
								return Cassandra\Time::fromDateTime($value);								
							}
						}
						$value = date_create_from_format('H:i:s.uu', $value);
						if($value !== false){
							return Cassandra\Time::fromDateTime($value);
						}
					} else {
						return Cassandra\Time::fromDateTime($value);
					}
				case 'timeuuid':
					if ($value == null) {
						$this->_generatedUuid = new Cassandra\Timeuuid();
					} else {
						$this->_generatedUuid = new Cassandra\Uuid($value);
					}
					return $this->_generatedUuid;
				case 'tinyint':
					return new Cassandra\Tinyint($value);
				case 'varint':
					return new Cassandra\Varint($value);
				default:
					return $value;
			}
		} catch (Cassandra\Exception $e){
			$this->_getError($e, __LINE__);
							return false;
		}
    }

    /**
     * Method which echos an error.
     *
     * @param $e
     * @param $line
     */
    protected function _getError($e, $line)
    {
        if ($this->showErrors)
			echo '<pre style="font-size: large">Line: ' . $line . ' Cassandra Error: ' . $e->getMessage() . '</pre>';				
    }
}