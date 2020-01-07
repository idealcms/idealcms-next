<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */
namespace Ideal\Core;

use Ideal\Core\Memcache;
use mysqli_result;

/**
 * Класс Db — обёртка над mysqli, добавляющий следующие улучшения
 * + одноразовое подключение к БД в рамках одного запуска php-интерпретатора
 * + класс сам обращается к настройкам подключения в конфигурационном файле CMS
 * + вспомогательные методы для запросов SELECT, INSERT, UPDATE, экранирующие входные параметры
 * + метод create, для создания таблиц на основе настроек CMS
 * В рамках CMS класс используется следующим образом
 *     $db = Db::getInstance();
 *     $par = ['time' = time(], 'active' = 1);
 *     $fields = ['table', 'example_table'];
 *     $rows = $db->select('SELECT * FROM &table WHERE time < :time AND is_active = :active',
 *                     $par, $fields);
 * В переменной $rows окажется ассоциативный массив записей из таблицы `example_table`,
 * у которых поле `time` меньше чем текущее время, а поле `is_active` равно 1
 */

class Db extends \mysqli
{

    /** @var array Массив для хранения подключений к разным БД */
    protected static $instance;

    /** @var Memcache Экземпляр подключения к memcache */
    protected $cache;

    /** @var bool Флаг того, что следующий запрос надо попытаться взять из кэша */
    protected $cacheEnabled = false;

    /** @var string Название используемой БД для корректного кэширования запросов */
    protected $dbName;

    /** @var string Название таблицы для запроса DELETE */
    protected $deleteTableName = '';

    /** @var array Массив для хранения явно указанных таблиц при вызове cacheMe() */
    protected $involvedTables;

    /** @var string Название таблицы для запроса UPDATE */
    protected $updateTableName = '';

    /** @var array Массив для хранения пар ключ-значение метода set() */
    protected $updateValues = [];

    /** @var array Массив для хранения пар ключ-значение метода where() */
    protected $whereParams = [];

    /** @var string Строка с where-частью запроса */
    protected $whereQuery = '';

    /** @var bool Флаг необходимости логирования ошибок, который ставится в true после каждого запроса */
    protected $logError = true;

    /**
     * Получение singleton-объекта подключённого к БД
     *
     * Если переменная $params не задана, то данные для подключения берутся из конфигурационного файла CMS
     * В массива $params должны быть следующие элементы:
     * host, login, password, name
     *
     * @param array $params Параметры подключения к БД
     * @return bool|Db объект, подключённый к БД, false — в случае невозможности подключиться к БД
     */
    public static function getInstance($params = null)
    {
        $key = md5(serialize($params));

        if (!empty(self::$instance[$key])) {
            // Если singleton этого подключения инициализирован, возвращаем его
            return self::$instance[$key];
        }

        $config = Config::getInstance();

        if ($params === null) {
            // Если параметры подключения явно не заданы, берём их из конфигурации
            $params = $config->db;
        }

        $db = new self($params['host'], $params['login'], $params['password'], $params['name']);

        if ($db->connect_errno) {
            Error::add('Не удалось подключиться к MySQL: ' . $db->connect_error);
            return false;
        }

        // Работаем только с UTF-8
        $db->query('set character set utf8');
        $db->query('set names utf8');

        $db->dbName = $params['name'];

        if (isset($config->cache['memcache']) && $config->cache['memcache']) {
            // Если в настройках site_data.php включён memcache, подключаем его
            $db->cache = Memcache::getInstance();
        }

        self::$instance[$key] = $db;

        return $db;
    }

    /**
     * Выполняет запрос к базе данных
     *
     * @link http://php.net/manual/ru/mysqli.query.php
     * @param string $query
     * @param int    $resultMode
     * @return bool|mysqli_result
     */
    public function query($query, $resultMode = MYSQLI_STORE_RESULT)
    {
        $result = parent::query($query, $resultMode);

        if ($this->logError && $error = $this->error) {
            Error::add($error . PHP_EOL . 'Query: ' . $query);
        }

        // После выполнения каждой операции - устанавливаем флаг логирования ошибок, чтобы случайно их не пропустить
        $this->logError = true;

        return $result;
    }

    /**
     * Установка флага попытки получения из кэша результатов следующего select-запроса
     *
     * @param array $involvedTables Массив с именами таблиц, участвующих в запросе.
     *                              Используется в случаях, когда SQL-запрос содержит JOIN или
     *                              вложенные подзапросы
     * @return $this
     */
    public function cacheMe($involvedTables = null): self
    {
        if ($involvedTables) {
            $this->involvedTables = $involvedTables;
        }

        $this->cacheEnabled = true;
        return $this;
    }

    /**
     * Создание таблицы $table на основе данных полей $fields
     *
     * @param string $table  Название создаваемой таблицы
     * @param array  $fields Названия создаваемых полей и описания их типа
     * @return bool|mysqli_result
     */
    public function create($table, $fields)
    {
        $sqlFields = [];

        foreach ($fields as $key => $value) {
            if (empty($value['sql'])) {
                // Пропускаем поля, которые не нужно создавать в БД
                continue;
            }
            $sqlFields[] = "`{$key}` {$value['sql']} COMMENT '{$value['label']}'";
        }

        $sql = "CREATE TABLE `{$table}` (" . implode(',', $sqlFields) . ') DEFAULT CHARSET=utf8';

        return $this->query($sql);
    }

    /**
     * Удаление одной или нескольких строк
     *
     * Пример использования:
     *     $db->delete($table)->where($sql, $params)->exec();
     * ВНИМАНИЕ: в результате выполнения этого метода сбрасывается кэш БД
     *
     * @param string $table Таблица, в которой будут удаляться строки
     * @return $this
     */
    public function delete($table): self
    {
        // Очищаем where, если он был задан ранее
        // Записываем название таблицы для DELETE

        $this->clearQueryAttributes();
        $this->deleteTableName = $table;

        return $this;
    }

    /**
     * Очистка параметров текущего update/delete запроса
     */
    protected function clearQueryAttributes(): void
    {
        $this->updateTableName = $this->deleteTableName = '';
        $this->updateValues = $this->whereParams = [];
        $this->involvedTables = null;
    }

    /**
     * Выполняет сформированный update/delete-запрос
     *
     * @param bool $exec Флаг выполнять/возвращать сформированный sql-запрос
     * @return bool|string Либо флаг успешности выполнения запроса, либо сам sql-запрос
     */
    public function exec($exec = true)
    {
        if (!$this->updateTableName && !$this->deleteTableName) {
            Error::add('Попытка вызова exec() без update() или delete().');
            return false;
        }

        $tag = $this->updateTableName ?: $this->deleteTableName;
        $sql = $this->updateTableName ? $this->getUpdateQuery() : $this->getDeleteQuery();

        if ($exec) {
            $this->clearCache($tag);
            if ($this->query($sql)) {
                // Если запрос выполнился успешно, то очистить все заданные параметры запроса, иначе не затирать их,
                // чтобы получить неправильный запрос при повторном вызове exec()
                $this->clearQueryAttributes();
            }
        } else {
            return $sql;
        }
        return true;
    }

    /**
     * Возвращает SQL-запрос для операции update() на основе значений, заданных с использованием set() и where()
     *
     * @return string UPDATE запрос
     */
    protected function getUpdateQuery(): string
    {
        $values = [];

        foreach ($this->updateValues as $column => $value) {
            $column = '`' . $this->escape_string($column) . '`';
            if (null === $value) {
                $value = 'NULL';
            } elseif (is_bool($value)) {
                $value = (int)$value;
            } else {
                $value = "'" . $this->escape_string($value) . "'";
            }
            $values[] = "{$column} = {$value}";
        }

        $values = implode(', ', $values);
        $this->updateTableName = '`' . $this->escape_string($this->updateTableName) . '`';
        $where = '';

        if ($this->whereQuery) {
            $where = 'WHERE ' . $this->prepareSql($this->whereQuery, $this->whereParams);
        }

        return 'UPDATE ' . $this->updateTableName . ' SET ' . $values . ' ' . $where . ';';
    }

    /**
     * Подготовка запроса к выполнению
     *
     * Все значения из $params экранируются и подставляются в $sql на место
     * плейсхолдеров :fieldName, имена таблиц подставляются на место
     * плейсхолдера &table
     *
     * @param string $sql    Необработанный SQL-запрос
     * @param array  $params Массив пар поле-значение, участвующих в запросе $sql
     * @param array  $fields Имена таблиц участвующих в запросе $sql
     * @return string Подготовленный SQL-запрос
     */
    protected function prepareSql($sql, $params = null, $fields = null): string
    {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                if (null === $value) {
                    $value = 'NULL';
                } else {
                    $value = "'" . $this->escape_string($value) . "'";
                }
                $sql = str_replace(":{$key}", $value, $sql);
            }
        }

        if (is_array($fields)) {
            foreach ($fields as $key => $value) {
                $field = $this->escape_string($value);
                $sql = str_replace("&{$key}", "`$field`", $sql);
            }
        }

        return $sql;
    }

    /**
     * Возвращает SQL-запрос для операции delete() на основе значений, заданных с использованием where()
     *
     * @return string DELETE запрос
     */
    protected function getDeleteQuery(): string
    {
        $this->deleteTableName = '`' . $this->escape_string($this->deleteTableName) . '`';
        $where = '';

        if ($this->whereQuery) {
            $where = 'WHERE ' . $this->prepareSql($this->whereQuery, $this->whereParams);
        }

        return 'DELETE FROM ' . $this->deleteTableName . ' ' . $where . ';';
    }

    /**
     * Очистка кэша запросов, связанных с таблицей $table
     *
     * @param string $table Название таблицы, для запросов из которой нужно очистить кэш
     */
    public function clearCache($table): void
    {
        if (isset($this->cache)) {
            $this->cache->deleteByTag($table);
        }
    }

    /**
     * Вставка новой строки в таблицу
     *
     * Пример использования:
     *     $params = array(
     *       'firstField' => 'firstValue',
     *       'secondField' => 'secondValue',
     *     )
     *     $id = $db->insert('table', $params);
     * ВНИМАНИЕ: в результате выполнения этого метода сбрасывается кэш БД
     *
     * @param string $table  Таблица, в которую необходимо вставить строку
     * @param array  $params Значения полей для вставки строки
     * @return int Id вставленной строки
     */
    public function insert($table, $params): int
    {
        $this->clearCache($table);
        $columns = $values = [];

        foreach ($params as $column => $value) {
            $columns[] = '`' . $this->escape_string($column) . '`';
            if (null === $value) {
                $values[] = 'NULL';
            } else {
                $values[] = "'" . $this->escape_string($value) . "'";
            }
        }

        $columns = implode(', ', $columns);
        $values = implode(', ', $values);
        $table = $this->escape_string($table);
        $sql = 'INSERT INTO `' . $table . '` (' . $columns . ') VALUES (' . $values . ');';
        $this->query($sql);

        return $this->insert_id;
    }

    /**
     * Вставка новых строк в таблицу
     *
     * Пример использования:
     *     $params = [
     *       '0' => [
     *              'firstField' => 'firstValue',
     *              'secondField'=> 'secondValue,
     *          ],
     *       '1' => [
     *              'firstField' => 'firstValue',
     *              'secondField'=> 'secondValue,
     *          ],
     *      ]
     *     $id = $db->insert('table', $params);
     * ВНИМАНИЕ: в результате выполнения этого метода сбрасывается кэш БД
     *
     * @param string $table  Таблица, в которую необходимо вставить строку
     * @param array  $params Значения полей для вставки строки
     * @return int количество затронутых строк
     */
    public function insertMultiple($table, $params): int
    {
        $this->clearCache($table);
        $values = $columns= [];

        $cols = array_keys(reset($params));
        // Получаем название полей
        foreach ($cols as $column) {
            $columns[] = '`' . $this->escape_string($column) . '`';
        }

        foreach ($params as $column => $item) {
            $vals = [];
            foreach ($item as $key => $value) {
                // Добавляемые значения для 1 строки
                $vals[] = "'" . $this->escape_string($value) . "'";
            }
            if (!empty($vals)) {
                // Массив всех добавляемых строк
                $values[] = '(' . implode(', ', $vals) . ')';
                unset($vals);
            }
        }

        $columns = implode(', ', $columns);
        $values = implode(', ', $values);
        $table = $this->escape_string($table);
        $sql = 'INSERT INTO `' . $table . '` (' . $columns . ') VALUES ' . $values . ';';
        $this->query($sql);

        return $this->affected_rows;
    }

    /**
     * Выборка строк из БД по заданному запросу $sql
     *
     * Пример использования:
     *     $par = ['time' => time(], active => true);
     *     $fields = ['table' => 'full_table_name'];
     *     $rows = $db->select('SELECT * FROM &table WHERE time < :time AND is_active = :active', $par, $fields);
     *
     * @param string $sql    SELECT-запрос
     * @param array  $params Параметров, которые будут экранированы и закавычены как параметры
     * @param array  $fields Названий полей и таблиц, которые будут экранированы и закавычены как названия полей
     * @return array Ассоциативный массив сделанной выборки из БД
     */
    public function select($sql, $params = null, $fields = null): array
    {
        $sql = $this->prepareSql($sql, $params, $fields);

        if (!$this->cacheEnabled || !isset($this->cache)) {
            // Если кэширование не включено, то выполняем запрос и возвращаем результат в виде ассоциативного массива
            $result = $this->query($sql);
            if ($result === false) {
                return [];
            }

            if (method_exists('mysqli_result', 'fetch_all')) {
                $res = $result->fetch_all(MYSQLI_ASSOC);
            } else {
                // Если у класса mysqli_result нет метода fetch_all (не подключен mysqlnd),
                // то считываем в массив построчно с помощью fetch_array
                for ($res = []; $tmp = $result->fetch_array(MYSQLI_ASSOC);) {
                    $res[] = $tmp;
                }
            }

            return $res;
        }

        $this->cacheEnabled = false; // т.к. кэширование включается только для одного запроса

        $cacheKey = $this->prepareCacheKey($sql);

        if ($cachedResult = $this->cache->getWithTags($cacheKey)) {
            return $cachedResult;
        }

        if (method_exists('mysqli_result', 'fetch_all')) {
            $queryResult = $this->query($sql)->fetch_all(MYSQLI_ASSOC);
        } else {
            // Если у класса mysqli_result нет метода fetch_all (не подключен mysqlnd),
            // то считываем в массив построчно с помощью fetch_array
            $result = $this->query($sql);
            for ($queryResult = []; $tmp = $result->fetch_array(MYSQLI_ASSOC);) {
                $queryResult[] = $tmp;
            }
        }

        $cacheTags = $this->prepareCacheTags($sql);
        $this->cache->setWithTags($cacheKey, $queryResult, false, $cacheTags);

        return $queryResult;
    }

    /**
     * Возвращает ключ для кеширования запроса
     *
     * @param $query string SQL-запрос
     * @return string md5 от запроса, переведенного в нижний регистр
     */
    protected function prepareCacheKey($query): string
    {
        return md5(strtolower($this->dbName . $query));
    }

    /**
     * Возвращает массив тегов, полученных на основе SQL-запроса
     *
     * Запрос разбирается в случае если теги (имена таблиц) явно не указаны при вызове cacheMe().
     *
     * @param $query string SQL-запрос
     * @return array
     */
    protected function prepareCacheTags($query): array
    {
        if ($this->involvedTables) {
            return $this->involvedTables;
        }

        // Запрос переводится в нижний регистр, после чего из него вырезаются
        // все символы до последнего ключевого слова FROM
        // и все символы начиная со следующего возможного ключевого слова

        $query = strtolower($query);
        $query = mb_eregi_replace('^(.|\n)*from\s+', '', $query);
        $pattern = '\s+(join\s+|left\s+|right\s+|where\s+|group\s+by|having\s+|order\s+by|limit\s+)(.|\n)*$';
        $query = mb_eregi_replace($pattern, '', $query);

        // Полученное значение разбивается на массив и очищается от кавычек и псевдонимов

        $queryArr = explode(',', $query);

        foreach ($queryArr as $key => $value) {
            $value = str_replace(['\'', '"', '`'], '', $value);
            $asPosition = strpos($value, ' as ');

            if ($asPosition !== false) {
                $value = substr($value, 0, $asPosition);
            }

            $value = trim($value);
            $queryArr[$key] = $value;
        }

        return array_unique($queryArr);
    }

    /**
     * В формируемый update-запрос добавляет значения полей для вставки
     *
     * @param array $values Названия и значения полей для вставки строки в таблицу
     * @return $this Db
     */
    public function set(array $values): self
    {
        $this->updateValues = $values;
        return $this;
    }

    /**
     * Обновление одной или нескольких строк
     *
     * Пример использования:
     *     $db->update($table)->set($values)->where($sql, $params)->exec();
     * ВНИМАНИЕ: в результате выполнения этого метода сбрасывается кэш БД
     *
     * @param string $table Таблица, в которой будут обновляться строки
     * @return $this
     */
    public function update($table): self
    {
        // Очищаем set и where, если они были заданы ранее
        // Записываем название таблицы для UPDATE

        $this->clearQueryAttributes();
        $this->updateTableName = $table;

        return $this;
    }

    /**
     * В формируемый update/delete-запрос добавляет where-условие
     *
     * Пример использования:
     *     $par = ['active' = 1];
     *     $db->delete('tableName')->where('is_active = :active', $par)->exec();
     *
     * @param string $sql Строка where-условия
     * @param array $params Параметры, используемые в строке where-условия
     * @return $this
     */
    public function where($sql, $params = []): self
    {
        $this->whereQuery = $sql;
        $this->whereParams = $params;

        return $this;
    }

    /**
     * Установка параметра логирования ошибок
     *
     * @param bool $bool
     */
    public function setLogError($bool): void
    {
        $this->logError = $bool;
    }
}
