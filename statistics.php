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

// Query for general statistics
$total_maps_sql = "SELECT COUNT(*) as total_maps FROM maps";
$total_maps_result = $conn->query($total_maps_sql);
$total_maps = $total_maps_result->fetch_assoc()['total_maps'];

$passed_maps_sql = "SELECT COUNT(*) as passed_maps FROM maps WHERE passed=1";
$passed_maps_result = $conn->query($passed_maps_sql);
$passed_maps = $passed_maps_result->fetch_assoc()['passed_maps'];

$passed_percentage = ($total_maps > 0) ? ($passed_maps / $total_maps) * 100 : 0;

// Query for position statistics
$position_sql = "
    SELECT 
        SUM(CASE WHEN position = 1 THEN 1 ELSE 0 END) as pos_1,
        SUM(CASE WHEN position = 2 THEN 1 ELSE 0 END) as pos_2,
        SUM(CASE WHEN position = 3 THEN 1 ELSE 0 END) as pos_3,
        SUM(CASE WHEN position BETWEEN 4 AND 10 THEN 1 ELSE 0 END) as pos_4_10,
        SUM(CASE WHEN position > 10 THEN 1 ELSE 0 END) as pos_11_plus
    FROM maps
";
$position_result = $conn->query($position_sql);
$position_stats = $position_result->fetch_assoc();

$total_toptimes = array_sum($position_stats);

$pos_1_percentage = ($total_toptimes > 0) ? ($position_stats['pos_1'] / $total_toptimes) * 100 : 0;
$pos_2_percentage = ($total_toptimes > 0) ? ($position_stats['pos_2'] / $total_toptimes) * 100 : 0;
$pos_3_percentage = ($total_toptimes > 0) ? ($position_stats['pos_3'] / $total_toptimes) * 100 : 0;
$pos_4_10_percentage = ($total_toptimes > 0) ? ($position_stats['pos_4_10'] / $total_toptimes) * 100 : 0;
$pos_11_plus_percentage = ($total_toptimes > 0) ? ($position_stats['pos_11_plus'] / $total_toptimes) * 100 : 0;

// Query for date statistics
$date_sql = "
    SELECT 
        DATE_FORMAT(date, '%Y-%m-%d') as day,
        COUNT(*) as count_per_day,
        DATE_FORMAT(date, '%Y-%u') as week,
        COUNT(*) as count_per_week,
        DATE_FORMAT(date, '%Y-%m') as month,
        COUNT(*) as count_per_month,
        DATE_FORMAT(date, '%Y') as year,
        COUNT(*) as count_per_year
    FROM maps
    WHERE date >= '2006-01-01'  -- Exclude dates below 2006
    GROUP BY day, week, month, year
    ORDER BY day
";
$date_sql = "
    SELECT 
        DATE_FORMAT(date, '%Y-%m-%d') as day,
        COUNT(*) as count_per_day,
        DATE_FORMAT(date, '%Y-%u') as week,
        COUNT(*) as count_per_week,
        DATE_FORMAT(date, '%Y-%m') as month,
        COUNT(*) as count_per_month,
        DATE_FORMAT(date, '%Y') as year,
        COUNT(*) as count_per_year
    FROM maps
    WHERE date >= '2006-01-01'  -- Exclude dates below 2006
    GROUP BY day, week, month, year
    ORDER BY day
";
// Query for date statistics, excluding dates before 2006
$date_sql = "
    SELECT 
        DATE_FORMAT(date, '%Y-%m-%d') as day,
        COUNT(*) as count_per_day,
        DATE_FORMAT(date, '%Y-%u') as week,
        DATE_FORMAT(date, '%Y-%m') as month,
        DATE_FORMAT(date, '%Y') as year
    FROM maps
    WHERE date >= '2006-01-01'  -- Exclude dates below 2006
    GROUP BY day, week, month, year
    ORDER BY day
";

$date_result = $conn->query($date_sql);

$daily_counts = [];
$weekly_counts = [];
$monthly_counts = [];
$yearly_counts = [];

// Initialize counts
while ($row = $date_result->fetch_assoc()) {
    // Daily counts
    if (isset($row['day'])) {
        $daily_counts[$row['day']] = $row['count_per_day'];
    }
    // Weekly counts
    if (isset($row['week'])) {
        if (!isset($weekly_counts[$row['week']])) {
            $weekly_counts[$row['week']] = 0;
        }
        $weekly_counts[$row['week']] += $row['count_per_day']; // Sum counts for the week
    }
    // Monthly counts
    if (isset($row['month'])) {
        if (!isset($monthly_counts[$row['month']])) {
            $monthly_counts[$row['month']] = 0;
        }
        $monthly_counts[$row['month']] += $row['count_per_day']; // Sum counts for the month
    }
    // Yearly counts
    if (isset($row['year'])) {
        if (!isset($yearly_counts[$row['year']])) {
            $yearly_counts[$row['year']] = 0;
        }
        $yearly_counts[$row['year']] += $row['count_per_day']; // Sum counts for the year
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statistics</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 100%;
            height: 200px;
        }
        .stat-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-card h3 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h1>Statistics</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="stat-card">
                    <h3>Map Statistics</h3>
                    <p>Completed Maps: <?php echo $passed_maps; ?></p>
                    <p>Total Maps: <?php echo $total_maps; ?></p>
                    <p>Percentage Passed: <span class="badge badge-warning"><?php echo number_format($passed_percentage, 2); ?>%</span></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <canvas id="completionChart" class="chart-container"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="stat-card">
                    <h3>Top Times by Position</h3>
                    <p>Position 1: <?php echo $position_stats['pos_1']; ?> / <?php echo number_format($pos_1_percentage, 2); ?>%</p>
                    <p>Position 2: <?php echo $position_stats['pos_2']; ?> / <?php echo number_format($pos_2_percentage, 2); ?>%</p>
                    <p>Position 3: <?php echo $position_stats['pos_3']; ?> / <?php echo number_format($pos_3_percentage, 2); ?>%</p>
                    <p>Position 4-10: <?php echo $position_stats['pos_4_10']; ?> / <?php echo number_format($pos_4_10_percentage, 2); ?>%</p>
                    <p>Position 11+: <?php echo $position_stats['pos_11_plus']; ?> / <?php echo number_format($pos_11_plus_percentage, 2); ?>%</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <canvas id="positionChart" class="chart-container"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="stat-card">
                    <h3>Top Times by Date</h3>
                    <p>Toptimes per Day: <?php echo array_sum($daily_counts); ?></p>
                    <p>Toptimes per Week: <?php echo array_sum($weekly_counts); ?></p>
                    <p>Toptimes per Month: <?php echo array_sum($monthly_counts); ?></p>
                    <p>Toptimes per Year: <?php echo array_sum($yearly_counts); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <canvas id="dateChart" class="chart-container"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Data for map statistics
        const mapStatsData = {
            labels: ['Completed Maps', 'Remaining Maps'],
            datasets: [{
                data: [<?php echo $passed_maps; ?>, <?php echo $total_maps - $passed_maps; ?>],
                backgroundColor: ['green', 'red']
            }]
        };

        const completionChartConfig = {
            type: 'doughnut',
            data: mapStatsData,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        };

        new Chart(document.getElementById('completionChart'), completionChartConfig);

        // Data for position statistics
        const positionData = {
            labels: ['Position 1', 'Position 2', 'Position 3', 'Position 4-10', 'Position 11+'],
            datasets: [{
                data: [
                    <?php echo $position_stats['pos_1']; ?>,
                    <?php echo $position_stats['pos_2']; ?>,
                    <?php echo $position_stats['pos_3']; ?>,
                    <?php echo $position_stats['pos_4_10']; ?>,
                    <?php echo $position_stats['pos_11_plus']; ?>
                ],
                backgroundColor: ['blue', 'red', 'yellow', 'orange', 'purple']
            }]
        };

        const positionChartConfig = {
            type: 'bar',
            data: positionData,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        };

        new Chart(document.getElementById('positionChart'), positionChartConfig);

        // Data for date statistics
        const dateLabels = <?php echo json_encode(array_keys($daily_counts)); ?>;
        const dateCounts = <?php echo json_encode(array_values($daily_counts)); ?>;

        const dateChartData = {
            labels: dateLabels,
            datasets: [{
                label: 'Toptimes per Day',
                data: dateCounts,
                borderColor: 'blue',
                fill: false
            }]
        };

        const dateChartConfig = {
            type: 'line',
            data: dateChartData,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        };

        new Chart(document.getElementById('dateChart'), dateChartConfig);
    </script>
</body>
</html>
