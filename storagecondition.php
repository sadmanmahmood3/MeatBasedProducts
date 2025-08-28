<?php


require_once 'db.php';

// Handle deletion
if (isset($_GET['delete'])) {
  $readingID = (int)$_GET['delete'];
  $stmt = $pdo->prepare('DELETE FROM sensorreadings WHERE ReadingID = ?');
  $stmt->execute([$readingID]);
  header('Location: storageconditions.php');
  exit;
}

// Handle creation
if (isset($_POST['create'])) {
  $storageID = (int)$_POST['storage_id'];
  $locationType = trim($_POST['location_type']);
  $temp     = floatval($_POST['temperature']);
  $humidity = floatval($_POST['humidity']);
  $oxygen   = floatval($_POST['oxygen']);
  $datetime = $_POST['reading_datetime'];
  $stmt = $pdo->prepare('INSERT INTO sensorreadings (StorageID, LocationType, Temperature, Humidity, OxygenLevel, ReadingDateTime) VALUES (?,?,?,?,?,?)');
  $stmt->execute([$storageID, $locationType, $temp, $humidity, $oxygen, $datetime]);
  header('Location: storageconditions.php');
  exit;
}

// Handle update
if (isset($_POST['update'])) {
  $readingID = (int)$_POST['reading_id'];
  $storageID = (int)$_POST['storage_id'];
  $locationType = trim($_POST['location_type']);
  $temp     = floatval($_POST['temperature']);
  $humidity = floatval($_POST['humidity']);
  $oxygen   = floatval($_POST['oxygen']);
  $datetime = $_POST['reading_datetime'];
  $stmt = $pdo->prepare('UPDATE sensorreadings SET StorageID=?, LocationType=?, Temperature=?, Humidity=?, OxygenLevel=?, ReadingDateTime=? WHERE ReadingID=?');
  $stmt->execute([$storageID, $locationType, $temp, $humidity, $oxygen, $datetime, $readingID]);
  header('Location: storageconditions.php');
  exit;
}

include 'header.php';

// Fetch list of cold storage names for dropdown
$storages = $pdo->query('SELECT StorageID, Name, ProductID FROM coldstorage ORDER BY Name')->fetchAll(PDO::FETCH_ASSOC);

// Create a mapping of recommended temperature from meatproduct.StorageRequirements
$recTemp = [];
foreach ($storages as $st) {
  if (!empty($st['ProductID'])) {
    $res = $pdo->prepare('SELECT StorageRequirements FROM meatproduct WHERE ProductID=?');
    $res->execute([$st['ProductID']]);
    $req = $res->fetchColumn();
    // attempt to extract numeric temperature from requirement string
    if ($req && preg_match('/(-?\d+\.?\d*)°?F/', $req, $m)) {
      // convert F to C
      $f = floatval($m[1]);
      $c = ($f - 32) * 5 / 9;
      $recTemp[$st['StorageID']] = $c;
    } else {
      $recTemp[$st['StorageID']] = null;
    }
  }
}

// Fetch sensor readings with storage name
$sql = 'SELECT sr.*, cs.Name AS StorageName FROM sensorreadings sr
        LEFT JOIN coldstorage cs ON sr.StorageID = cs.StorageID
        ORDER BY sr.ReadingDateTime DESC';
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>
<h2 class="mb-4">Storage Conditions</h2>
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
  <i class="fa fa-plus"></i> Record Reading
</button>

<div class="table-container">
  <table id="sensorTable" class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Storage</th>
        <th>Location Type</th>
        <th>Temperature (°C)</th>
        <th>Humidity (%)</th>
        <th>Oxygen Level (%)</th>
        <th>Date &amp; Time</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <?php
        // Determine if temperature deviation beyond +/-3°C of recommended
        $tempClass = '';
        $storageID = $row['StorageID'];
        if (isset($recTemp[$storageID]) && $recTemp[$storageID] !== null) {
          $diff = abs($row['Temperature'] - $recTemp[$storageID]);
          if ($diff > 3) {
            $tempClass = 'table-warning';
          }
        }
        ?>
        <tr class="<?php echo $tempClass; ?>">
          <td><?php echo htmlspecialchars($row['ReadingID']); ?></td>
          <td><?php echo htmlspecialchars($row['StorageName']); ?></td>
          <td><?php echo htmlspecialchars($row['LocationType']); ?></td>
          <td><?php echo htmlspecialchars($row['Temperature']); ?></td>
          <td><?php echo htmlspecialchars($row['Humidity']); ?></td>
          <td><?php echo htmlspecialchars($row['OxygenLevel']); ?></td>
          <td><?php echo htmlspecialchars($row['ReadingDateTime']); ?></td>
          <td>
            <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['ReadingID']; ?>"><i class="fa fa-edit"></i></a>
            <a href="storageconditions.php?delete=<?php echo $row['ReadingID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this reading?');"><i class="fa fa-trash"></i></a>
          </td>
        </tr>
        <!-- Edit Modal -->
        <div class="modal fade" id="editModal<?php echo $row['ReadingID']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $row['ReadingID']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $row['ReadingID']; ?>">Edit Reading #<?php echo $row['ReadingID']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form method="post" action="storageconditions.php">
                <div class="modal-body">
                  <input type="hidden" name="reading_id" value="<?php echo $row['ReadingID']; ?>">
                  <div class="mb-3">
                    <label class="form-label">Storage</label>
                    <select name="storage_id" class="form-select" required>
                      <?php foreach ($storages as $st): ?>
                        <option value="<?php echo $st['StorageID']; ?>" <?php if ($st['StorageID'] == $row['StorageID']) echo 'selected'; ?>><?php echo htmlspecialchars($st['Name']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Location Type</label>
                    <input type="text" name="location_type" class="form-control" value="<?php echo htmlspecialchars($row['LocationType']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Temperature (°C)</label>
                    <input type="number" step="0.1" name="temperature" class="form-control" value="<?php echo htmlspecialchars($row['Temperature']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Humidity (%)</label>
                    <input type="number" step="0.1" name="humidity" class="form-control" value="<?php echo htmlspecialchars($row['Humidity']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Oxygen Level (%)</label>
                    <input type="number" step="0.1" name="oxygen" class="form-control" value="<?php echo htmlspecialchars($row['OxygenLevel']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Reading Date &amp; Time</label>
                    <input type="datetime-local" name="reading_datetime" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($row['ReadingDateTime'])); ?>" required>
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
        <h5 class="modal-title" id="addModalLabel">Record Sensor Reading</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="storageconditions.php">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Storage</label>
            <select name="storage_id" class="form-select" required>
              <?php foreach ($storages as $st): ?>
                <option value="<?php echo $st['StorageID']; ?>"><?php echo htmlspecialchars($st['Name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Location Type</label>
            <input type="text" name="location_type" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Temperature (°C)</label>
            <input type="number" step="0.1" name="temperature" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Humidity (%)</label>
            <input type="number" step="0.1" name="humidity" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Oxygen Level (%)</label>
            <input type="number" step="0.1" name="oxygen" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Reading Date &amp; Time</label>
            <input type="datetime-local" name="reading_datetime" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="create" class="btn btn-primary">Record</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('#sensorTable').DataTable({
      responsive: true,
      pageLength: 10,
      lengthChange: false,
      order: [
        [6, 'desc']
      ]
    });
  });
</script>


<!-- Analytical Tools Section -->
<div class="row mt-5">
  <div class="col-md-7 mb-4">
    <div class="card">
      <div class="card-header bg-primary text-white">Temperature Over Time</div>
      <div class="card-body">
        <canvas id="tempLineChart" height="200"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-5 mb-4">
    <div class="card">
      <div class="card-header bg-success text-white">Humidity vs. Oxygen</div>
      <div class="card-body">
        <canvas id="humOxyScatter" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<?php
// Prepare data for analytics
// 1. Temperature over time (group by storage)
$temp_series = [];
foreach ($rows as $row) {
  $sid = $row['StorageName'] ?: 'Unknown';
  if (!isset($temp_series[$sid])) $temp_series[$sid] = [];
  $temp_series[$sid][] = [
    'x' => $row['ReadingDateTime'],
    'y' => floatval($row['Temperature'])
  ];
}

// 2. Humidity vs Oxygen scatter
$hum_oxy = [];
foreach ($rows as $row) {
  $hum_oxy[] = [
    'x' => floatval($row['Humidity']),
    'y' => floatval($row['OxygenLevel']),
    'label' => ($row['StorageName'] ?: 'Unknown') . ' @ ' . $row['ReadingDateTime']
  ];
}
?>


<!-- Chart.js CDN and Luxon adapter for time axis support -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.3.1/dist/chartjs-adapter-luxon.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Temperature Over Time Line Chart
  const tempLineCtx = document.getElementById('tempLineChart').getContext('2d');
  const tempSeries = <?php echo json_encode($temp_series); ?>;
  const tempDatasets = Object.keys(tempSeries).map((name, i) => {
    const colors = [
      'rgba(54, 162, 235, 0.7)',
      'rgba(255, 99, 132, 0.7)',
      'rgba(255, 206, 86, 0.7)',
      'rgba(75, 192, 192, 0.7)',
      'rgba(153, 102, 255, 0.7)',
      'rgba(255, 159, 64, 0.7)'
    ];
    return {
      label: name,
      data: tempSeries[name],
      borderColor: colors[i % colors.length],
      backgroundColor: colors[i % colors.length],
      fill: false,
      tension: 0.3
    };
  });
  new Chart(tempLineCtx, {
    type: 'line',
    data: {
      datasets: tempDatasets
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' },
        title: { display: false }
      },
      parsing: false,
      scales: {
        x: {
          type: 'time',
          time: { unit: 'day', tooltipFormat: 'yyyy-MM-dd HH:mm' },
          title: { display: true, text: 'Date & Time' }
        },
        y: { title: { display: true, text: 'Temperature (°C)' } }
      }
    }
  });

  // Humidity vs. Oxygen Scatter Plot
  const humOxyCtx = document.getElementById('humOxyScatter').getContext('2d');
  const humOxyData = <?php echo json_encode($hum_oxy); ?>;
  new Chart(humOxyCtx, {
    type: 'scatter',
    data: {
      datasets: [{
        label: 'Reading',
        data: humOxyData,
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
              return `${d.label}: (${d.x}% Humidity, ${d.y}% O₂)`;
            }
          }
        },
        legend: { display: false },
        title: { display: false }
      },
      scales: {
        x: { title: { display: true, text: 'Humidity (%)' } },
        y: { title: { display: true, text: 'Oxygen Level (%)' } }
      }
    }
  });
});
</script>

<?php include 'footer.php'; ?>