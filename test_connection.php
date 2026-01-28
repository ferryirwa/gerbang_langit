 <?php
// test_connection.php
echo "<h2>Test Connection Gerbang Langit</h2>";

// Test MySQL Connection
echo "Testing MySQL Connection...<br>";
$conn = new mysqli("localhost", "root", "", "gerbang_langit");

if ($conn->connect_error) {
    echo "<span style='color:red'>❌ FAILED: " . $conn->connect_error . "</span><br>";
    echo "Solution: <br>";
    echo "1. Buka XAMPP Control Panel<br>";
    echo "2. Klik START pada MySQL<br>";
    echo "3. Klik START pada Apache<br>";
    echo "4. Refresh halaman ini";
} else {
    echo "<span style='color:green'>✅ MySQL CONNECTED!</span><br>";
    
    // Test tables
    $tables = $conn->query("SHOW TABLES");
    echo "<br>Tables in database:<br>";
    while($row = $tables->fetch_array()) {
        echo "✅ " . $row[0] . "<br>";
    }
    
    $conn->close();
}

// Test config.php
echo "<br>Testing config.php...<br>";
if (file_exists('config.php')) {
    echo "<span style='color:green'>✅ config.php exists</span><br>";
} else {
    echo "<span style='color:red'>❌ config.php not found</span><br>";
}
?>