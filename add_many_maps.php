<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Many Maps</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h1>Add Many Maps</h1>
        <form method="post" action="add_many_maps.php">
            <div class="form-group">
                <label for="maps">Maps (separated by ";")</label>
                <textarea class="form-control" id="maps" name="maps" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Maps</button>
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
    $maps = explode(';', $_POST['maps']);

    foreach ($maps as $map) {
        $map = trim($map);
        if (empty($map)) continue;

        // Extract authors from the map name
        $parts = explode(' - ', $map);
        if (count($parts) != 2) {
            die("Invalid map name format for '$map'. Please use 'Author(s) - Map Name'.");
        }
        $map_name = $conn->real_escape_string($parts[1]);
        $authors_str = $conn->real_escape_string($parts[0]);
        $authors = preg_split('/ ft\. |, | & /i', $authors_str);

        foreach ($authors as $author) {
            $author = $conn->real_escape_string($author);
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
        $map_insert_sql = "INSERT INTO maps (name, author, Tag) VALUES ('$map_name', '$authors_str', 'DM')";
        if ($conn->query($map_insert_sql) !== TRUE) {
            echo "Error: " . $map_insert_sql . "<br>" . $conn->error;
        }
    }

    echo "Maps added successfully";
}

$conn->close();
?>
