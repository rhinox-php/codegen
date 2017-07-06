<?php
namespace Rhino\Codegen\Model\Generated;

class Route extends AbstractModel {

    protected $id;

    // Properties
    protected $httpMethod;
    protected $urlPath;
    protected $controllerClass;
    protected $controllerMethod;

    protected $created;
    protected $updated;

    //Related entities

    // Table name
    protected static $table = 'route';

    // Columns
    protected static $columns = '
        `route`.`id`,
        `route`.`http_method` AS `httpMethod`,
        `route`.`url_path` AS `urlPath`,
        `route`.`controller_class` AS `controllerClass`,
        `route`.`controller_method` AS `controllerMethod`,
        `route`.`created`,
        `route`.`updated`
    ';

    // Datatable
    public static function getDataTable() {
        $table = new \Rhino\DataTable\MySqlDataTable(static::getPdo(), 'route');
        $table->insertColumn('actions', function($column, $row) {
            // @todo fix delete button, make post, confirm
            return '
                <a href="/route/edit/' . $row['id'] . '" class="btn btn-xs btn-default">Edit</a>
                <a href="/route/delete/' . $row['id'] . '" class="btn btn-xs btn-link text-danger">Delete</a>
            ';
        })->setLabel('Actions');
        $table->addColumn('id')->setLabel('ID');
        $table->addColumn('http_method')->setLabel('Http method');
        $table->addColumn('url_path')->setLabel('Url path');
        $table->addColumn('controller_class')->setLabel('Controller class');
        $table->addColumn('controller_method')->setLabel('Controller method');
        $table->addColumn('created')->setLabel('Created');
        $table->addColumn('updated')->setLabel('Updated');
        return $table;
    }

    // Json
    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'httpMethod' => $this->getHttpMethod(),
            'urlPath' => $this->getUrlPath(),
            'controllerClass' => $this->getControllerClass(),
            'controllerMethod' => $this->getControllerMethod(),
            'created' => $this->getCreated() ? $this->getCreated()->format(static::DATE_TIME_FORMAT) : null,
            'updated' => $this->getUpdated() ? $this->getUpdated()->format(static::DATE_TIME_FORMAT) : null,
        ];
    }

    // Save/insert/update/delete
    protected function insert() {
        $date = new \DateTimeImmutable('now', new \DateTimezone('UTC'));
        $this->setUpdated($date);
        $this->setCreated($date);
        $this->query('
            INSERT INTO `route` (
                `http_method`,
                `url_path`,
                `controller_class`,
                `controller_method`,
                `updated`,
                `created`
            ) VALUES (
                :http_method,
                :url_path,
                :controller_class,
                :controller_method,
                :updated,
                :created
            );
        ', $this->getQueryParams());

        $this->setId($this->lastInsertId());
    }

    protected function update() {
        $this->setUpdated(new \DateTimeImmutable('now', new \DateTimezone('UTC')));

        $params = $this->getQueryParams();
        $params[':id'] = $this->getId();
        $this->query('
            UPDATE `route`
            SET
                `http_method` = :http_method,
                `url_path` = :url_path,
                `controller_class` = :controller_class,
                `controller_method` = :controller_method,
                `updated` = :updated,
                `created` = :created
            WHERE `id` = :id
            LIMIT 1;
        ', $params);

    }

    protected function getQueryParams() {
       return [
            ':http_method' => $this->getHttpMethod(),
            ':url_path' => $this->getUrlPath(),
            ':controller_class' => $this->getControllerClass(),
            ':controller_method' => $this->getControllerMethod(),
			':created' => $this->formatMySqlDateTime($this->getCreated()),
			':updated' => $this->formatMySqlDateTime($this->getUpdated()),
        ];
    }

    public function delete() {
        $this->query('
            DELETE FROM `route`
            WHERE `id` = :id;
        ', [
            ':id' => $this->getId(),
        ]);
    }

    public static function hydrateFromPdoStatement($statement) {
        while (($row = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $entity = new static();
            $entity->id = $row['id'];
            $entity->httpMethod = $row['http_method'] ?? null;
            $entity->urlPath = $row['url_path'] ?? null;
            $entity->controllerClass = $row['controller_class'] ?? null;
            $entity->controllerMethod = $row['controller_method'] ?? null;
            if (isset($row['created'])) {
                $entity->setCreated(\DateTimeImmutable::createFromFormat(static::MYSQL_DATE_TIME_FORMAT, $row['created'], new \DateTimezone('UTC')));
            }
            if (isset($row['updated'])) {
                $entity->setUpdated(\DateTimeImmutable::createFromFormat(static::MYSQL_DATE_TIME_FORMAT, $row['updated'], new \DateTimezone('UTC')));
            }
            yield $entity;
        }
    }

    protected function saveRelated() {
    }


    // Find methods

    /**
     * @return Route The instance matching the ID, or null.
     */
    public static function findById($id) {
        return static::fetchRoute(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE id = :id;
        ', [
            ':id' => $id,
        ]));
    }

    // Find by attribute Http Method
    public static function findByHttpMethod($value) {
        return static::fetchRoutes(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE `http_method` = :value;
        ', [
            ':value' => $value,
        ]));
    }

    /**
     * Find the first instance matching the supplied Http Method or
     * `null` if there was no results.
     *
     * @return \Rhino\Codegen\Model\Route|null
     */
    public static function findFirstByHttpMethod($value) {
        return static::fetchRoute(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE `http_method` = :value
            LIMIT 1;
        ', [
            ':value' => $value,
        ]));
    }

    public static function countByHttpMethod($value) {
        return (int) static::query('
            SELECT COUNT(`id`)
            FROM `' . static::$table . '`
            WHERE `http_method` = :value;
        ', [
            ':value' => $value,
        ])->fetchColumn();
    }

    // Find by attribute Url Path
    public static function findByUrlPath($value) {
        return static::fetchRoutes(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE `url_path` = :value;
        ', [
            ':value' => $value,
        ]));
    }

    /**
     * Find the first instance matching the supplied Url Path or
     * `null` if there was no results.
     *
     * @return \Rhino\Codegen\Model\Route|null
     */
    public static function findFirstByUrlPath($value) {
        return static::fetchRoute(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE `url_path` = :value
            LIMIT 1;
        ', [
            ':value' => $value,
        ]));
    }

    public static function countByUrlPath($value) {
        return (int) static::query('
            SELECT COUNT(`id`)
            FROM `' . static::$table . '`
            WHERE `url_path` = :value;
        ', [
            ':value' => $value,
        ])->fetchColumn();
    }

    // Find by attribute Controller Class
    public static function findByControllerClass($value) {
        return static::fetchRoutes(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE `controller_class` = :value;
        ', [
            ':value' => $value,
        ]));
    }

    /**
     * Find the first instance matching the supplied Controller Class or
     * `null` if there was no results.
     *
     * @return \Rhino\Codegen\Model\Route|null
     */
    public static function findFirstByControllerClass($value) {
        return static::fetchRoute(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE `controller_class` = :value
            LIMIT 1;
        ', [
            ':value' => $value,
        ]));
    }

    public static function countByControllerClass($value) {
        return (int) static::query('
            SELECT COUNT(`id`)
            FROM `' . static::$table . '`
            WHERE `controller_class` = :value;
        ', [
            ':value' => $value,
        ])->fetchColumn();
    }

    // Find by attribute Controller Method
    public static function findByControllerMethod($value) {
        return static::fetchRoutes(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE `controller_method` = :value;
        ', [
            ':value' => $value,
        ]));
    }

    /**
     * Find the first instance matching the supplied Controller Method or
     * `null` if there was no results.
     *
     * @return \Rhino\Codegen\Model\Route|null
     */
    public static function findFirstByControllerMethod($value) {
        return static::fetchRoute(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE `controller_method` = :value
            LIMIT 1;
        ', [
            ':value' => $value,
        ]));
    }

    public static function countByControllerMethod($value) {
        return (int) static::query('
            SELECT COUNT(`id`)
            FROM `' . static::$table . '`
            WHERE `controller_method` = :value;
        ', [
            ':value' => $value,
        ])->fetchColumn();
    }

    /**
     * Yields an instance for every row stored in the database.
     *
     * WARNING: It is not advisable to use this method on tables with many rows
     * as it will likely be quite slow.
     *
     * @return Generator|\Rhino\Codegen\Model\Route[]
     */
    public static function iterateAll() {
        return static::fetchRoutes(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`;
        '));
    }

    /**
     * Returns an array of every instance stored in the database.
     *
     * WARNING: This method can quickly cause a out of memory error if there are
     * many rows in the database.
     *
     * @return \Rhino\Codegen\Model\Route[]
     */
    public static function getAll() {
        return iterator_to_array(static::iterateAll());
    }

    /**
     * Fetch a single instance of Route from a PDO result,
     * or `null` if there was no results.
     *
     * @return \Rhino\Codegen\Model\Route|null
     */
    protected static function fetchRoute(\PDOStatement $result) {
        $entity = $result->fetchObject(static::class);
        if (!$entity) {
            return null;
        }

        // Parse date attributes

        // Parse created/updated dates
        if ($entity->created) {
            $entity->setCreated(\DateTimeImmutable::createFromFormat(static::MYSQL_DATE_TIME_FORMAT, $entity->created, new \DateTimezone('UTC')));
        }
        if ($entity->updated) {
            $entity->setUpdated(\DateTimeImmutable::createFromFormat(static::MYSQL_DATE_TIME_FORMAT, $entity->updated, new \DateTimezone('UTC')));
        }
        return $entity;
    }

    /**
     * Yield multiple instances of Route from a PDO result.
     *
     * @return \Generator|\Rhino\Codegen\Model\Route[]
     */
    protected static function fetchRoutes(\PDOStatement $result) {
        while ($entity = static::fetchRoute($result)) {
            yield $entity;
        }
    }

    // Fetch relationships

    // Attribute accessors

    public function getHttpMethod() {
        return $this->httpMethod;
    }

    public function setHttpMethod($value) {
        $this->httpMethod = $value;
        return $this;
    }

    public function getUrlPath() {
        return $this->urlPath;
    }

    public function setUrlPath($value) {
        $this->urlPath = $value;
        return $this;
    }

    public function getControllerClass() {
        return $this->controllerClass;
    }

    public function setControllerClass($value) {
        $this->controllerClass = $value;
        return $this;
    }

    public function getControllerMethod() {
        return $this->controllerMethod;
    }

    public function setControllerMethod($value) {
        $this->controllerMethod = $value;
        return $this;
    }

}
