<?= '<?php'; ?>

namespace <?= $this->getNamespace(); ?>\Services;

use <?= $this->getNamespace(); ?>\Services\InputData;

class JsonApiList
{
    const FILTER_IN = 'in';
    const FILTER_EQUAL = 'equal';

    /**
     * @var string Model class to query and return a list of
     */
    protected $modelClass;

    /**
     * @var \<?= $this->getNamespace(); ?>\Services\InputData Input data passed from the API client
     */
    protected $input;

    /**
     * @var mixed[] Array of where statements passed to the query. Each statement consists of ['column', 'operator', 'value'] which is passed directly to Eloquent `where`
     */
    protected $wheres = [];

    /**
     * @var mixed[] Array of raw join statements passed to the query
     */
    protected $joins = [];

    /**
     * @var string[] Whitelist of columns that the API allows sorting by. Array key is the URL parameter, array value is the SQL column
     */
    protected $sortColumns = [];

    /**
     * @var string[] Whitelist of columns that the API allows searching. Array key is the URL parameter, array value is the SQL column
     */
    protected $searchColumns = [];

    /**
     * @var mixed[] @todo
     */
    protected $filters = [];

    /**
     * @var mixed[] Internally used to store the array of results
     */
    protected $results;

    /**
     * @var mixed[] Internally used to store the amount of found rows before pagination
     */
    protected $foundRows;

    public function __construct($modelClass, InputData $input)
    {
        $this->modelClass = $modelClass;
        $this->input = $input;
    }

    /**
     * Process the input data and prepares the result.
     */
    public function process()
    {
        $modelClass = $this->modelClass;
        $table = (new $modelClass())->getTable();

        // Array of bound parameters
        $bindings = [];

        // Joins
        $joins = [];
        foreach ($this->joins as list($joinSql, $joinBindings)) {
            $joins[] = $this->replaceBindings($joinSql, $joinBindings, $bindings);
        }
        if (!empty($joins)) {
            $joins = implode(PHP_EOL, $joins);
        } else {
            $joins = '';
        }

        // Wheres
        $wheres = [];
        foreach ($this->wheres as list($whereSql, $whereBindings)) {
            $wheres[] = '('.$this->replaceBindings($whereSql, $whereBindings, $bindings).')';
        }
        if (!empty($wheres)) {
            $wheres = 'WHERE '.implode(' AND '.PHP_EOL, $wheres);
        } else {
            $wheres = '';
        }

        // Search filters
        $having = [];
        if ($this->input->string('filter')) {
            foreach ($this->searchColumns as $key => $column) {
                $having[] = $this->replaceBindings($column.' LIKE :search', [
                    ':search' => '%'.$this->input->string('filter').'%',
                ], $bindings);
            }
        }
        if (!empty($having)) {
            $having = 'HAVING '.implode(' OR '.PHP_EOL, $having);
        } else {
            $having = '';
        }

        // Individual filters
        $filters = [];
        foreach ($this->filters as $key => list($name, $type, $options)) {
            $filterValue = $this->input->arr('filter')->string($name);
            if (!$filterValue) {
                continue;
            }
            switch ($type) {
                case static::FILTER_IN: {
                    $filterInBindings = [];
                    $filterInValues = explode(',', $filterValue);
                    foreach ($filterInValues as $filterInValue) {
                        $filterInBindings[':in'.(count($filterInValues) + 1000)] = $filterInValue;
                    }
                    $filters[] = $this->replaceBindings($options[0].' IN('.implode(', ', array_keys($filterInBindings)).')', $filterInBindings, $bindings);
                    break;
                }
                case static::FILTER_EQUAL: {
                    $filters[] = $this->replaceBindings($options[0].' = :filterValue', [
                        ':filterValue' => $filterValue,
                    ], $bindings);
                    break;
                }
            }
        }
        if (!empty($filters)) {
            $having = (!$having ? 'HAVING ' : ' ').implode(' AND '.PHP_EOL, $filters);
        }

        // Order By
        $orderBy = [];
        $sorts = explode(',', $this->input->string('sort'));
        foreach ($sorts as $sort) {
            if (!$sort) {
                continue;
            }
            $sortColumn = $sort[0] === '-' ? substr($sort, 1) : $sort;
            $sortDirection = $sort[0] === '-' ? 'DESC' : 'ASC';
            if (isset($this->sortColumns[$sortColumn])) {
                $orderBy[] = $this->sortColumns[$sortColumn].' '.$sortDirection;
            }
            // @fixme currently only supported single sort column
            break;
        }
        if (!empty($orderBy)) {
            $orderBy = 'ORDER BY '.implode(', ', $orderBy);
        } else {
            $orderBy = '';
        }

        // Limit/Offset
        $limit = '';
        $offset = '';
        if ($this->input->int('page.limit')) {
            $limit = $this->input->int('page.limit', 10);
            $offset = $this->input->int('page.offset', 0);
            if ($limit < 1) {
                throw new \Exception('Limit must be 1 or more');
            }
            if ($offset < 0) {
                throw new \Exception('Offset must be 0 or more');
            }
            $limit = 'LIMIT '.$limit;
            $offset = 'OFFSET '.$offset;
        }

        $query = "
            SELECT SQL_CALC_FOUND_ROWS `$table`.*
            FROM `$table`
            $joins
            $wheres
            $having
            $orderBy
            $limit
            $offset
        ";

        // Process query
        $this->results = $modelClass::hydrateRaw($query, $bindings);
        $foundRows = \DB::select('SELECT FOUND_ROWS() AS foundRows');
        $this->foundRows = $foundRows[0]->foundRows;
    }

    /**
     * @return \<?= $this->getNamespace(); ?>\Models\Model[]
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return mixed[] Meta data from the query results. Includes total amount
     *                 of found rows before pagination
     */
    public function getMeta()
    {
        return [
            'foundRows' => $this->foundRows,
        ];
    }

    /**
     * Adds a `WHERE` statement to the SQL. This should be used for filtering
     * out based on the requirements of the endpoint, and not user input.
     *
     * @param string  $sql      SQL where statement
     * @param mixed[] $bindings Parameter bindings for the query (parameters should be prefixed with `:`)
     */
    public function addWhere($sql, $bindings)
    {
        $this->wheres[] = [$sql, $bindings];
        return $this;
    }

    /**
     * Adds a `JOIN` statement to the SQL.
     *
     * @param string  $sql      SQL join statement
     * @param mixed[] $bindings Parameter bindings for the query (parameters should be prefixed with `:`)
     */
    public function addJoin($sql, array $bindings)
    {
        $this->joins[] = [$sql, $bindings];
        return $this;
    }

    /**
     * Defines the columns that are available to be sorted by.
     *
     * @param string[] $sortColumns Array of sort parameter names (that are used in the query string) mapped to SQL column names,
     */
    public function setSortColumns(array $sortColumns)
    {
        $this->sortColumns = $sortColumns;
        return $this;
    }

    /**
     * Defines the columns that are available to be searched. This is intended
     * for general search and not filtering specific values.
     *
     * @param string[] $sortColumns Array of column names that can be searched using the `filter` parameter,
     */
    public function setSearchColumns(array $searchColumns)
    {
        $this->searchColumns = $searchColumns;
        return $this;
    }

    /**
     * Defines a filter parameter that can be used for specific filtering.
     *
     * @param string  $name    Name of the filter to be used in the query string (e.g. ?filter[name]=value)
     * @param enum    $type    Type of filter query. Options include:
     *                         - JsonApiList::FILTER_IN
     *                         - JsonApiList::FILTER_EQUAL
     * @param mixed[] $options Extra options specific to the filter type:
     *                         - JsonApiList::FILTER_IN Column name to use.
     *                         - JsonApiList::FILTER_EQUAL Column name to use
     */
    public function setFilter($name, $type, ...$options)
    {
        $this->filters[$name] = [$name, $type, $options];
    }

    /**
     * Replaces bindings in a SQL statement with a unique name. This is used to allow duplicate
     * binding names that are added by different statements.
     *
     * @param string  $querySql      SQL statement
     * @param mixed[] $queryBindings Bindings for SQL statement
     * @param mixed[] $bindings      Array of existing bindings
     */
    protected function replaceBindings($querySql, array $queryBindings, array &$bindings)
    {
        foreach ($queryBindings as $key => $value) {
            $key = ':'.trim($key, ':');
            $newKey = ':binding'.(count($bindings) + 1000);
            $querySql = str_replace($key, $newKey, $querySql);
            $bindings[$newKey] = $value;
        }
        return $querySql;
    }
}
