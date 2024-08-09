<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Map</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function toggleFields() {
            var passedCheckbox = document.getElementById('passed');
            var additionalFields = document.getElementById('additionalFields');
            additionalFields.style.display = passedCheckbox.checked ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h1>Add Map</h1>
        <form method="post" action="add_map.php">
            <div class="form-group">
                <label for="name">Map Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="tag">Tag</label>
                <select class="form-control" id="tag" name="tag" required>
                    <option value="DM">DM</option>
                    <option value="OS">OS</option>
                    <option value="HDM">HDM</option>
                </select>
            </div>
            <div class="form-group">
                <label for="passed">Passed</label>
                <input type="checkbox" id="passed" name="passed" value="1" onclick="toggleFields()">
            </div>
            <div id="additionalFields" style="display:none;">
                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="number" class="form-control" id="position" name="position">
                </div>
                <div class="form-group">
                    <label for="time_passed">Time Passed (MM:SS:SSS)</label>
                    <input type="text" class="form-control" id="time_passed" name="time_passed">
                </div>
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" class="form-control" id="date" name="date">
                </div>
                <div class="form-group">
                    <label for="server">Server</label>
                    <input type="text" class="form-control" id="server" name="server">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Add Map</button>
        </form>
    </div>
</body>
</html>

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $tag = $conn->real_escape_string($_POST['tag']);
    $date = $conn->real_escape_string($_POST['date']);
    $position = $conn->real_escape_string($_POST['position']);
    $time_passed = $conn->real_escape_string($_POST['time_passed']);
    $passed = isset($_POST['passed']) ? 1 : 0;
    $server = $conn->real_escape_string($_POST['server']);

    // Extract authors from the map name
    $parts = explode(' - ', $name);
    if (count($parts) != 2) {
        die("Invalid map name format. Please use 'Author(s) - Map Name'.");
    }
    $map_name = $parts[1];
    $authors_str = $parts[0];
    $authors = preg_split('/ ft\. |, | & /i', $authors_str);

    foreach ($authors as $author) {
        // Check if author exists in the database
        $author_check_sql = "SELECT * FROM authors WHERE name='$author'";
        $author_check_result = $conn->query($author_check_sql);
        if ($author_check_result->num_rows == 0) {
            // Add new author to the authors table
            $author_insert_sql = "INSERT INTO authors (name) VALUES ('$author')";
            $conn->query($author_insert_sql);
        }
    }

    // Insert the new map into the maps table
    $map_insert_sql = "INSERT INTO maps (name, author, time_passed, passed, server, Tag, date, position) VALUES ('$map_name', '$authors_str', '$time_passed', '$passed', '$server', '$tag', '$date', '$position')";
    if ($conn->query($map_insert_sql) === TRUE) {
        echo "New map added successfully";
    } else {
        echo "Error: " . $map_insert_sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
