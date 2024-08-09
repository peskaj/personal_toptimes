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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Clear all records from maps and authors tables
    $clearMapsSql = "DELETE FROM maps";
    $clearAuthorsSql = "DELETE FROM authors";

    if ($conn->query($clearMapsSql) === TRUE && $conn->query($clearAuthorsSql) === TRUE) {
        $message = "All records have been cleared successfully!";
    } else {
        $message = "Error clearing records: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clear All Records</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 class="mt-5 text-danger">Clear All Records</h1>
        <?php if (!empty($message)) { ?>
            <div class="alert <?php echo strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php } ?>
        <form action="clear_records.php" method="post">
            <div class="form-group">
                <p class="text-danger">Are you sure you want to clear all records? This action cannot be undone.</p>
            </div>
            <button type="submit" class="btn btn-danger">Clear All Records</button>
        </form>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
