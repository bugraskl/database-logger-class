
# Class that records all database tables in PHP MySQL

The DatabaseLogger class is designed to provide a logging system for tracking changes in a database. It allows you to automatically log the changes (inserts, updates, deletes) made to the tables in the database. By creating triggers for each table, the class captures the relevant information about the changes and stores them in a logs table. This provides a comprehensive log of all database modifications, which can be useful for auditing, debugging, or tracking data history. The class simplifies the process of implementing a database logging system and can be easily integrated into existing projects by enabling logging on initialization.




## USAGE

```php
<?php
require_once 'DatabaseLogger.php';

$servername = "your_servername";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";

$logger = new DatabaseLogger($servername, $username, $password, $dbname);
$logger->enableLogging();
```

  
