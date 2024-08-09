<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "map_website";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$map_id = isset($_GET['id']) ? $_GET['id'] : '';
$map_id = $conn->real_escape_string($map_id);

// Delete the map record
$delete_sql = "DELETE FROM maps WHERE id = $map_id";

if ($conn->query($delete_sql) === TRUE) {
    header('Location: author_maps.php?author=' . urlencode($_GET['author']));
    exit();
} else {
    echo "Error deleting record: " . $conn->error;
}

$conn->close();
?>
