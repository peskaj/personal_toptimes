<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Map List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .tag-circle {
            width: 46px;
            height: 29px;
            line-height: 25px;
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            vertical-align: middle;
            text-align: center;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        td.map-name {
            text-align: left; /* Align text to the left for map name */
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            color: black;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
            transition: background-color 0.3s;
        }
        .pagination a:hover {
            background-color: #ddd;
        }
        .pagination a.active {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div class="d-flex justify-content-between mb-3">
            <h1>Map List</h1>
            <div>
                <label for="recordsPerPage">Show:</label>
                <select id="recordsPerPage" class="form-control d-inline-block w-auto">
                    <option value="25" <?php if(isset($_GET['limit']) && $_GET['limit'] == 25) echo 'selected'; ?>>25</option>
                    <option value="50" <?php if(isset($_GET['limit']) && $_GET['limit'] == 50) echo 'selected'; ?>>50</option>
                    <option value="100" <?php if(isset($_GET['limit']) && $_GET['limit'] == 100) echo 'selected'; ?>>100</option>
                    <option value="500" <?php if(isset($_GET['limit']) && $_GET['limit'] == 500) echo 'selected'; ?>>500</option>
                    <option value="1000" <?php if(isset($_GET['limit']) && $_GET['limit'] == 1000) echo 'selected'; ?>>1000</option>
                </select>
            </div>
        </div>
        <input class="form-control mb-3" id="searchBar" type="text" placeholder="Search by map name...">
        <div class="table-responsive">
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

                    // Determine the current page and the limit
                    $limit = isset($_GET['limit']) ? $_GET['limit'] : 25;
                    $page = isset($_GET['page']) ? $_GET['page'] : 1;
                    $offset = ($page - 1) * $limit;

                    // Fetch and sort maps alphabetically by author and map name with pagination
                    $sql = "SELECT * FROM maps ORDER BY author ASC, name ASC LIMIT $limit OFFSET $offset";
                    $result = $conn->query($sql);

                    // Get total number of records
                    $sql_total = "SELECT COUNT(*) AS total FROM maps";
                    $result_total = $conn->query($sql_total);
                    $total_records = $result_total->fetch_assoc()['total'];
                    $total_pages = ceil($total_records / $limit);

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
                            echo "<td class='map-name'>{$map_name}</td>";
                            echo "<td>" . ($row['position'] != 0 ? $row['position'] : '') . "</td>";
                            echo "<td>{$row['time_passed']}</td>";
                            echo "<td>{$formatted_date}</td>";
                            echo "<td>" . ($row['passed'] ? "<span class='passed'>✅</span>" : "<span>❌</span>") . "</td>";
                            echo "<td>{$row['server']}</td>";
                            echo "<td><a href='edit_map.php?id={$row['id']}' class='btn btn-warning btn-sm'>✎</a> <a href='delete_map.php?id={$row['id']}' class='btn btn-danger btn-sm'>🗑️</a></td>";
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

        <!-- Pagination links -->
        <div class="pagination">
            <?php
            $visible_pages = 5; // Number of pages to display at once
            $start_page = max(1, $page - floor($visible_pages / 2));
            $end_page = min($total_pages, $start_page + $visible_pages - 1);

            if ($page > 1) {
                echo "<a href='?page=1&limit=$limit'>&laquo;</a>";
                echo "<a href='?page=".($page - 1)."&limit=$limit'>&lt;</a>";
            }

            for ($i = $start_page; $i <= $end_page; $i++) {
                $active = $i == $page ? 'active' : '';
                echo "<a href='?page=$i&limit=$limit' class='$active'>$i</a>";
            }

            if ($page < $total_pages) {
                echo "<a href='?page=".($page + 1)."&limit=$limit'>&gt;</a>";
                echo "<a href='?page=$total_pages&limit=$limit'>&raquo;</a>";
            }
            ?>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            // Search filter
            $("#searchBar").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#mapTable tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            // Records per page filter
            $("#recordsPerPage").on("change", function() {
                var limit = $(this).val();
                var currentUrl = window.location.href.split('?')[0]; // Get the base URL without parameters
                window.location.href = currentUrl + '?limit=' + limit + '&page=1'; // Reset to page 1 when limit changes
            });
        });
    </script>
</body>
</html>
