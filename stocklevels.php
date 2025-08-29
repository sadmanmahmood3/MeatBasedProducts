<?php


require_once 'db.php';

// Handle deletion of a storage location
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $stmt = $pdo->prepare('DELETE FROM coldstorage WHERE StorageID = ?');
  $stmt->execute([$id]);
  header('Location: stocklevels.php');
  exit;
}

// Handle creation
if (isset($_POST['create'])) {
  $productID = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
  $name      = trim($_POST['name']);
  $capacity  = (int)$_POST['capacity'];
  $temp      = floatval($_POST['temperature']);
  $humidity  = floatval($_POST['humidity']);
  $monitor   = trim($_POST['monitor']);
  $stmt = $pdo->prepare('INSERT INTO coldstorage (ProductID, Name, Capacity, CurrentTemperature, CurrentHumidity, MonitoringSystemID) VALUES (?,?,?,?,?,?)');
  $stmt->execute([$productID, $name, $capacity, $temp, $humidity, $monitor]);
  header('Location: stocklevels.php');
  exit;
}

// Handle update
if (isset($_POST['update'])) {
  $storageID = (int)$_POST['storage_id'];
  $productID = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
  $name      = trim($_POST['name']);
  $capacity  = (int)$_POST['capacity'];
  $temp      = floatval($_POST['temperature']);
  $humidity  = floatval($_POST['humidity']);
  $monitor   = trim($_POST['monitor']);
  $stmt = $pdo->prepare('UPDATE coldstorage SET ProductID=?, Name=?, Capacity=?, CurrentTemperature=?, CurrentHumidity=?, MonitoringSystemID=? WHERE StorageID=?');
  $stmt->execute([$productID, $name, $capacity, $temp, $humidity, $monitor, $storageID]);
  header('Location: stocklevels.php');
  exit;
}

include 'header.php';

// Fetch list of products for assignment (optional)
$productList = $pdo->query('SELECT ProductID, CutType FROM meatproduct ORDER BY ProductID')->fetchAll(PDO::FETCH_ASSOC);

// Fetch cold storage with aggregated occupancy
$sql = 'SELECT cs.*, mp.CutType,
               IFNULL(SUM(pb.Weight),0) AS OccupiedWeight,
               ROUND(IFNULL(SUM(pb.Weight),0) / cs.Capacity * 100.0, 2) AS OccupancyPercent
        FROM coldstorage cs
        LEFT JOIN meatproduct mp ON cs.ProductID = mp.ProductID
        LEFT JOIN packagedbatch pb ON cs.StorageID = pb.StorageID
        GROUP BY cs.StorageID
        ORDER BY cs.StorageID';
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>
<h2 class="mb-4">Stock Levels</h2>
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
  <i class="fa fa-plus"></i> Add Storage
</button>

<div class="table-container">
  <table id="storageTable" class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Assigned Product</th>
        <th>Capacity (lb)</th>
        <th>Occupied Weight (lb)</th>
        <th>Occupancy %</th>
        <th>Temp (°C)</th>
        <th>Humidity (%)</th>
        <th>Monitor ID</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['StorageID']); ?></td>
          <td><?php echo htmlspecialchars($row['Name']); ?></td>
          <td><?php echo htmlspecialchars($row['CutType']); ?></td>
          <td><?php echo htmlspecialchars($row['Capacity']); ?></td>
          <td><?php echo htmlspecialchars($row['OccupiedWeight']); ?></td>
          <td><?php echo htmlspecialchars($row['OccupancyPercent']); ?>%</td>
          <td><?php echo htmlspecialchars($row['CurrentTemperature']); ?></td>
          <td><?php echo htmlspecialchars($row['CurrentHumidity']); ?></td>
          <td><?php echo htmlspecialchars($row['MonitoringSystemID']); ?></td>
          <td>
            <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['StorageID']; ?>"><i class="fa fa-edit"></i></a>
            <a href="stocklevels.php?delete=<?php echo $row['StorageID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this storage location?');"><i class="fa fa-trash"></i></a>
          </td>
        </tr>
        <!-- Edit Modal -->
        <div class="modal fade" id="editModal<?php echo $row['StorageID']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $row['StorageID']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $row['StorageID']; ?>">Edit Storage #<?php echo $row['StorageID']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form method="post" action="stocklevels.php">
                <div class="modal-body">
                  <input type="hidden" name="storage_id" value="<?php echo $row['StorageID']; ?>">
                  <div class="mb-3">
                    <label class="form-label">Assigned Product</label>
                    <select name="product_id" class="form-select">
                      <option value="">-- None --</option>
                      <?php foreach ($productList as $prod): ?>
                        <option value="<?php echo $prod['ProductID']; ?>" <?php if ($prod['ProductID'] == $row['ProductID']) echo 'selected'; ?>>
                          <?php echo htmlspecialchars($prod['CutType']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['Name']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Capacity (lb)</label>
                    <input type="number" name="capacity" class="form-control" value="<?php echo htmlspecialchars($row['Capacity']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Current Temperature (°C)</label>
                    <input type="number" step="0.1" name="temperature" class="form-control" value="<?php echo htmlspecialchars($row['CurrentTemperature']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Current Humidity (%)</label>
                    <input type="number" step="0.1" name="humidity" class="form-control" value="<?php echo htmlspecialchars($row['CurrentHumidity']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Monitoring System ID</label>
                    <input type="text" name="monitor" class="form-control" value="<?php echo htmlspecialchars($row['MonitoringSystemID']); ?>">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addModalLabel">Add Storage</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="stocklevels.php">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Assigned Product</label>
            <select name="product_id" class="form-select">
              <option value="">-- None --</option>
              <?php foreach ($productList as $prod): ?>
                <option value="<?php echo $prod['ProductID']; ?>"><?php echo htmlspecialchars($prod['CutType']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Capacity (lb)</label>
            <input type="number" name="capacity" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Current Temperature (°C)</label>
            <input type="number" step="0.1" name="temperature" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Current Humidity (%)</label>
            <input type="number" step="0.1" name="humidity" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Monitoring System ID</label>
            <input type="text" name="monitor" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="create" class="btn btn-primary">Add Storage</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('#storageTable').DataTable({
      responsive: true,
      pageLength: 10,
      lengthChange: false,
      order: [
        [0, 'asc']
      ]
    });
  });
</script>


<!-- Analytical Tools Section -->
<div class="row mt-5">
  <div class="col-md-6 mb-4">
    <div class="card">
      <div class="card-header bg-primary text-white">Storage Occupancy (%)</div>
      <div class="card-body">
        <canvas id="occupancyBarChart" height="200"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="card">
      <div class="card-header bg-success text-white">Temperature & Humidity</div>
      <div class="card-body">
        <canvas id="tempHumidityScatter" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<?php
// Prepare data for analytics
// 1. Storage Occupancy
$occ_labels = [];
$occ_data = [];
foreach ($rows as $row) {
  $occ_labels[] = $row['Name'];
  $occ_data[] = floatval($row['OccupancyPercent']);
}

// 2. Temperature & Humidity
$th_labels = [];
$th_data = [];
foreach ($rows as $row) {
  $th_labels[] = $row['Name'];
  $th_data[] = [
    'x' => floatval($row['CurrentTemperature']),
    'y' => floatval($row['CurrentHumidity']),
    'label' => $row['Name']
  ];
}
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Storage Occupancy Bar Chart
const occBarCtx = document.getElementById('occupancyBarChart').getContext('2d');
const occBarChart = new Chart(occBarCtx, {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($occ_labels); ?>,
    datasets: [{
      label: 'Occupancy %',
      data: <?php echo json_encode($occ_data); ?>,
      backgroundColor: 'rgba(54, 162, 235, 0.7)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      title: { display: false }
    },
    scales: {
      y: { beginAtZero: true, max: 100, title: { display: true, text: 'Occupancy (%)' } },
      x: { title: { display: true, text: 'Storage Name' } }
    }
  }
});

// Temperature & Humidity Scatter Plot
const thScatterCtx = document.getElementById('tempHumidityScatter').getContext('2d');
const thData = <?php echo json_encode($th_data); ?>;
const thScatterChart = new Chart(thScatterCtx, {
  type: 'scatter',
  data: {
    datasets: [{
      label: 'Storage',
      data: thData,
      backgroundColor: 'rgba(255, 99, 132, 0.7)',
      pointRadius: 6
    }]
  },
  options: {
    responsive: true,
    plugins: {
      tooltip: {
        callbacks: {
          label: function(context) {
            const d = context.raw;
            return `${d.label}: (${d.x}°C, ${d.y}%)`;
          }
        }
      },
      legend: { display: false },
      title: { display: false }
    },
    scales: {
      x: { title: { display: true, text: 'Temperature (°C)' } },
      y: { title: { display: true, text: 'Humidity (%)' } }
    }
  }
});
</script>

<?php include 'footer.php'; ?>