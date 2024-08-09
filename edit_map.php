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

$id = $_GET['id'];

// Fetch the map details
$sql = "SELECT * FROM maps WHERE id='$id'";
$result = $conn->query($sql);
$map = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $authors = $conn->real_escape_string($_POST['authors']);
    $tag = $conn->real_escape_string($_POST['tag']);
    $date = $conn->real_escape_string($_POST['date']);
    $position = $conn->real_escape_string($_POST['position']);
    $time_passed = $conn->real_escape_string($_POST['time_passed']);
    $passed = isset($_POST['passed']) ? 1 : 0;
    $server = $conn->real_escape_string($_POST['server']);

    // Update the map details in the maps table
    $map_update_sql = "UPDATE maps SET name='$name', author='$authors', Tag='$tag', date='$date', position='$position', time_passed='$time_passed', passed='$passed', server='$server' WHERE id='$id'";
    if ($conn->query($map_update_sql) === TRUE) {
        echo "Map updated successfully";
    } else {
        echo "Error: " . $map_update_sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Map</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h1>Edit Map</h1>
        <form method="post" action="edit_map.php?id=<?php echo $id; ?>">
            <div class="form-group">
                <label for="name">Map Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $map['name']; ?>" required>
            </div>
            <div class="form-group">
                <label for="authors">Authors</label>
                <input type="text" class="form-control" id="authors" name="authors" value="<?php echo $map['author']; ?>" required>
            </div>
            <div class="form-group">
                <label for="tag">Tag</label>
                <select class="form-control" id="tag" name="tag" required>
                    <option value="DM" <?php if ($map['Tag'] == 'DM') echo 'selected'; ?>>DM</option>
                    <option value="OS" <?php if ($map['Tag'] == 'OS') echo 'selected'; ?>>OS</option>
                    <option value="HDM" <?php if ($map['Tag'] == 'HDM') echo 'selected'; ?>>HDM</option>
                </select>
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo $map['date']; ?>">
            </div>
            <div class="form-group">
                <label for="position">Position</label>
                <input type="number" class="form-control" id="position" name="position" value="<?php echo $map['position']; ?>">
            </div>
            <div class="form-group">
                <label for="time_passed">Time Passed (MM:SS:SSS)</label>
                <input type="text" class="form-control" id="time_passed" name="time_passed" value="<?php echo $map['time_passed']; ?>">
            </div>
            <div class="form-group">
                <label for="passed">Passed</label>
                <input type="checkbox" id="passed" name="passed" value="1" <?php if ($map['passed'] == 1) echo 'checked'; ?>>
            </div>
            <div class="form-group">
                <label for="server">Server</label>
                <input type="text" class="form-control" id="server" name="server" value="<?php echo $map['server']; ?>">
            </div>
            <button type="submit" class="btn btn-primary">Update Map</button>
        </form>
    </div>
</body>
</html>
