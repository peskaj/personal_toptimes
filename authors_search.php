<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "map_website";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = isset($_POST['search']) ? $_POST['search'] : '';
$search = $conn->real_escape_string($search);

$sql = "
    SELECT 
        SUBSTRING_INDEX(a.name, ' ', 1) AS author_name, 
        COUNT(DISTINCT CASE WHEN m.passed = 1 THEN m.id END) AS total_maps_passed,
        COUNT(DISTINCT m.id) AS total_maps
    FROM 
        authors a
    LEFT JOIN 
        maps m ON m.author LIKE CONCAT('%', a.name, '%')
    WHERE 
        a.name LIKE '%$search%'
    GROUP BY 
        author_name
    HAVING total_maps > 0
    ORDER BY 
        author_name";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td><a href='author_maps.php?author=" . urlencode($row['author_name']) . "'>{$row['author_name']}</a></td>
                <td>{$row['total_maps_passed']}/{$row['total_maps']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='2'>No authors found</td></tr>";
}

$conn->close();
?>
