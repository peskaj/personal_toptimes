<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "map_website";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch authors, grouping by base author name (ignoring versions)
$sql = "
    SELECT 
        TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ft.', 1), ' v', 1)) AS base_author_name,  -- Get base author name, trimming after 'ft.' or 'v'
        COUNT(DISTINCT CASE WHEN m.passed = 1 THEN m.id END) AS total_maps_passed,
        COUNT(DISTINCT m.id) AS total_maps
    FROM 
        authors a
    LEFT JOIN 
        maps m ON m.author = a.name OR m.author LIKE CONCAT(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ft.', 1), ' v', 1)), ' %')
    GROUP BY 
        base_author_name
    HAVING total_maps > 0
    ORDER BY 
        base_author_name;"; // Closing the SQL query string with a semicolon

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Authors</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .status-green { color: green; }
        .status-orange { color: orange; }
        .status-red { color: red; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 class="mt-5">Authors</h1>
        <input type="text" id="searchInput" class="form-control mb-3" placeholder="Search for authors...">
        <table class="table table-striped" id="authorTable">
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Maps Passed (Total)</th>
                </tr>
            </thead>
            <tbody id="authorBody">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $author_name = $row['base_author_name'];
                        $status_class = '';

                        if ($row['total_maps_passed'] == $row['total_maps']) {
                            $status_class = 'status-green';
                        } elseif ($row['total_maps_passed'] > 0) {
                            $status_class = 'status-orange';
                        } else {
                            $status_class = 'status-red';
                        }

                        echo "<tr>
                                <td><a href='author_maps.php?author=" . urlencode($author_name) . "'>$author_name</a></td>
                                <td class='$status_class'>{$row['total_maps_passed']}/{$row['total_maps']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>No authors found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#authorBody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
</body>
</html>
