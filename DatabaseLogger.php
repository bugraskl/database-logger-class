<?php

/**
 * 20.06.2023
 * 11:52
 * Prepared by Buğra Şıkel @bugraskl
 * https://www.bugra.work/
 */
class DatabaseLogger
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * @param $servername
     * @param $username
     * @param $password
     * @param $dbname
     */
    public function __construct($servername, $username, $password, $dbname)
    {
        try {
            $this->connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    /**
     * @return void
     */
    public function enableLogging()
    {
        if (!$this->isLogsTableExists()) {
            $this->createLogsTable();
        }

        $tables = $this->getAllTables();

        foreach ($tables as $table) {
            if (!$this->isTriggerExists($table, 'INSERT')) {
                $this->createTrigger($table, 'INSERT');
            }
            if (!$this->isTriggerExists($table, 'UPDATE')) {
                $this->createTrigger($table, 'UPDATE');
            }
            if (!$this->isTriggerExists($table, 'DELETE')) {
                $this->createTrigger($table, 'DELETE');
            }
        }
    }

    /**
     * @return bool
     */
    private function isLogsTableExists()
    {
        $tableName = 'logs';
        $tableQuery = "SHOW TABLES LIKE '$tableName'";
        $result = $this->connection->query($tableQuery)->fetch(PDO::FETCH_ASSOC);
        return ($result !== false);
    }

    /**
     * @return void
     */
    private function createLogsTable()
    {
        $createTableSQL = "CREATE TABLE IF NOT EXISTS logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            table_name VARCHAR(255),
            action VARCHAR(50),
            old_data JSON,
            new_data JSON,
            changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $this->connection->exec($createTableSQL);
    }

    /**
     * @param $table
     * @param $action
     * @return bool
     */
    private function isTriggerExists($table, $action)
    {
        $triggerName = $table . '_' . strtolower($action) . '_trigger';
        $triggerQuery = "SHOW TRIGGERS LIKE '$triggerName'";
        $result = $this->connection->query($triggerQuery)->fetch(PDO::FETCH_ASSOC);
        return ($result !== false);
    }

    /**
     * @param $table
     * @param $action
     * @return void
     */
    private function createTrigger($table, $action)
    {
        $triggerName = $table . '_' . strtolower($action) . '_trigger';
        $columns = $this->getTableColumns($table);
        $columnNames = implode(', ', $columns);

        $triggerSQL = "CREATE TRIGGER $triggerName AFTER $action ON $table
            FOR EACH ROW
            BEGIN
                IF OLD IS NULL THEN
                    INSERT INTO logs (table_name, action, new_data)
                    VALUES ('$table', '$action', JSON_OBJECT($columnNames));
                ELSE
                    INSERT INTO logs (table_name, action, old_data, new_data)
                    VALUES ('$table', '$action', JSON_OBJECT($columnNames), JSON_OBJECT($columnNames));
                END IF;
            END;";

        $this->connection->exec($triggerSQL);

    }
}