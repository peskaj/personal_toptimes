<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Map List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .tag-circle {
            width: 46px;
            height: 31px;
            text-align: center;
            border-radius: 25%;
            font-weight: bold;
            background-color: transparent;
            display: inline-block;
        }
        .tag-dm { border: 2px solid #00C865; color: #00C865; }
        .tag-os { border: 2px solid orange; color: orange; }
        .tag-hdm { border: 2px solid red; color: red; }
        .passed { font-size: 21px; }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h1>Map List</h1>
        <input class="form-control mb-3" id="searchBar" type="text" placeholder="Search by map name...">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Tag</th>
                    <th>Map Name</th>
                    <th>Position</th>
                    <th>Time Passed</th>
                    <th>Date</th>
                    <th>Passed</th>
                    <th>Server</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="mapTable">
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

                // Fetch and sort maps alphabetically by author and map name
                $sql = "SELECT * FROM maps ORDER BY author ASC, name ASC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $tag_class = '';
                        if ($row['Tag'] == 'DM') {
                            $tag_class = 'tag-dm';
                        } elseif ($row['Tag'] == 'OS') {
                            $tag_class = 'tag-os';
                        } elseif ($row['Tag'] == 'HDM') {
                            $tag_class = 'tag-hdm';
                        }

                        $map_name = $row['author'] . ' - ' . $row['name'];
                        $date = new DateTime($row['date']);
                        $formatted_date = ($row['date'] && $date->format('Y') >= 2006) ? $date->format('d.m.Y') : '';

                        echo "<tr>";
                        echo "<td><span class='tag-circle $tag_class'>{$row['Tag']}</span></td>";
                        echo "<td>{$map_name}</td>";
                        echo "<td align='center'>" . ($row['position'] != 0 ? $row['position'] : '') . "</td>";
                        echo "<td align='center'>{$row['time_passed']}</td>";
                        echo "<td align='center'>{$formatted_date}</td>";
                        echo "<td align='center'>" . ($row['passed'] ? "<span class='passed'>✅</span>" : "<span>❌</span>") . "</td>";
                        echo "<td align='center'>{$row['server']}</td>";
                        echo "<td align='center'><a href='edit_map.php?id={$row['id']}' class='btn btn-warning btn-sm'>✎</a> <a href='delete_map.php?id={$row['id']}' class='btn btn-danger btn-sm'>🗑️</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No maps found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function(){
            $("#searchBar").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#mapTable tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>
</html>
