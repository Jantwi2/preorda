<?php
require_once('settings/db_cred.php');

echo "<h1>Database Connection Test</h1>";
echo "Server: " . SERVER . "<br>";
echo "Username: " . USERNAME . "<br>";
echo "Database: " . DATABASE . "<br><br>";

$conn = mysqli_connect(SERVER, USERNAME, PASSWD, DATABASE);

if (!$conn) {
    echo "<b style='color:red'>CONNECTION FAILED:</b><br>";
    echo "Error Number: " . mysqli_connect_errno() . "<br>";
    echo "Error Message: " . mysqli_connect_error() . "<br>";
} else {
    echo "<b style='color:green'>CONNECTION SUCCESSFUL!</b>";
    mysqli_close($conn);
}
?>
