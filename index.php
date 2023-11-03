<?php
$startTime = time();

// DB Config
$host = "localhost";
$user = "root";
$password = "";
$db= "test";
$dbFormat = 'utf8_unicode_ci';

// Import Config
$source = 'products1.5M.csv';
$delimiter = ',';
$targetTable = 'products';
$ignoredHeaderRowsCount = 1;


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// DB connect
$connection = new mysqli($host, $user, $password, $db);
if (!$connection)
{
    die ('Connection error:' . mysqli_error());
}
mysqli_select_db($connection, $db);


// Create columns based on .csv headers
$file = fopen($source, 'r');
$headers = fgetcsv($file);
$tableColumns = '';

foreach ($headers as $column) {
    if ($column !== 'id') {
        $tableColumns = $tableColumns . "$column varchar(256) COLLATE $dbFormat, ";
    }
}

$connection->begin_transaction();
try {
    mysqli_query($connection, "DROP TABLE `$targetTable`");
    mysqli_query($connection, "CREATE TABLE $targetTable (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     $tableColumns
     PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=$dbFormat;");

    // Import
    $load= mysqli_query($connection,"LOAD DATA LOCAL INFILE '$source' INTO TABLE $targetTable FIELDS TERMINATED BY '$delimiter' IGNORE $ignoredHeaderRowsCount ROWS");
    $endTime = time() - $startTime;
    if ($load !== FALSE)
    {
        echo("The data has been successfully loaded for $endTime seconds");
    }
    else
    {
        echo("The data has not been loaded.");
    }
} catch (mysqli_sql_exception $exception) {
    $mysqli->rollback();

    throw $exception;
}
