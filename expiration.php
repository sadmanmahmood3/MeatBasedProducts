<?php

require_once 'db.php';

// Handle deletion
if (isset($_GET['delete'])) {
  $batchID = (int)$_GET['delete'];
  $stmt = $pdo->prepare('DELETE FROM packagedbatch WHERE BatchID = ?');
  $stmt->execute([$batchID]);
  header('Location: expiration.php');
  exit;
}

// Handle creation
if (isset($_POST['create'])) {
  $storageID  = (int)$_POST['storage_id'];
  $factoryID  = (int)$_POST['factory_id'];
  $packagingDate = $_POST['packaging_date'];
  $expiryDate    = $_POST['expiry_date'];
  $quantity   = (int)$_POST['quantity'];
  $weight     = floatval($_POST['weight']);
  $barcode    = trim($_POST['barcode']);
  $details    = trim($_POST['details']);
  $stmt = $pdo->prepare('INSERT INTO packagedbatch (StorageID, FactoryID, PackagingDate, ExpiryDate, Quantity, Weight, Barcode, PackagingDetails) VALUES (?,?,?,?,?,?,?,?)');
  $stmt->execute([$storageID, $factoryID, $packagingDate, $expiryDate, $quantity, $weight, $barcode, $details]);
  header('Location: expiration.php');
  exit;
}

// Handle update
if (isset($_POST['update'])) {
  $batchID = (int)$_POST['batch_id'];
  $storageID  = (int)$_POST['storage_id'];
  $factoryID  = (int)$_POST['factory_id'];
  $packagingDate = $_POST['packaging_date'];
  $expiryDate    = $_POST['expiry_date'];
  $quantity   = (int)$_POST['quantity'];
  $weight     = floatval($_POST['weight']);
  $barcode    = trim($_POST['barcode']);
  $details    = trim($_POST['details']);
  $stmt = $pdo->prepare('UPDATE packagedbatch SET StorageID=?, FactoryID=?, PackagingDate=?, ExpiryDate=?, Quantity=?, Weight=?, Barcode=?, PackagingDetails=? WHERE BatchID=?');
  $stmt->execute([$storageID, $factoryID, $packagingDate, $expiryDate, $quantity, $weight, $barcode, $details, $batchID]);
  header('Location: expiration.php');
  exit;
}

include 'header.php';

// Lists for selects
$storages = $pdo->query('SELECT StorageID, Name FROM coldstorage ORDER BY Name')->fetchAll(PDO::FETCH_ASSOC);
$factories = $pdo->query('SELECT FactoryID, Name FROM packagingfactory ORDER BY Name')->fetchAll(PDO::FETCH_ASSOC);

// Fetch packaged batches with join to show storage and factory names
$sql = 'SELECT pb.*, cs.Name AS StorageName, pf.Name AS FactoryName
        FROM packagedbatch pb
        LEFT JOIN coldstorage cs ON pb.StorageID = cs.StorageID
        LEFT JOIN packagingfactory pf ON pb.FactoryID = pf.FactoryID
        ORDER BY pb.BatchID';
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>
<h2 class="mb-4">Expiration &amp; Batch Tracking</h2>
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
  <i class="fa fa-plus"></i> Add Batch
</button>

<div class="table-container">
  <table id="batchTable" class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Batch ID</th>
        <th>Storage</th>
        <th>Factory</th>
        <th>Packaging Date</th>
        <th>Expiry Date</th>
        <th>Days Remaining</th>
        <th>Quantity</th>
        <th>Weight (lb)</th>
        <th>Barcode</th>
        <th>Packaging Details</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <?php
        $daysRemaining = '';
        if (!empty($row['ExpiryDate'])) {
          $expiry = strtotime($row['ExpiryDate']);
          $today  = strtotime(date('Y-m-d'));
          $daysRemaining = floor(($expiry - $today) / 86400);
        }
        // Determine row class for soon to expire items
        $rowClass = '';
        if ($daysRemaining !== '' && $daysRemaining <= 3) {
          $rowClass = 'table-danger';
        }
        ?>
        <tr class="<?php echo $rowClass; ?>">
          <td><?php echo htmlspecialchars($row['BatchID']); ?></td>
          <td><?php echo htmlspecialchars($row['StorageName']); ?></td>
          <td><?php echo htmlspecialchars($row['FactoryName']); ?></td>
          <td><?php echo htmlspecialchars($row['PackagingDate']); ?></td>
          <td><?php echo htmlspecialchars($row['ExpiryDate']); ?></td>
          <td><?php echo ($daysRemaining !== '' ? htmlspecialchars($daysRemaining) : ''); ?></td>
          <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
          <td><?php echo htmlspecialchars($row['Weight']); ?></td>
          <td><?php echo htmlspecialchars($row['Barcode']); ?></td>
          <td><?php echo htmlspecialchars($row['PackagingDetails']); ?></td>
          <td>
            <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['BatchID']; ?>"><i class="fa fa-edit"></i></a>
            <a href="expiration.php?delete=<?php echo $row['BatchID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this batch?');"><i class="fa fa-trash"></i></a>
          </td>
        </tr>
        <!-- Edit Modal -->
        <div class="modal fade" id="editModal<?php echo $row['BatchID']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $row['BatchID']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $row['BatchID']; ?>">Edit Batch #<?php echo $row['BatchID']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form method="post" action="expiration.php">
                <div class="modal-body">
                  <input type="hidden" name="batch_id" value="<?php echo $row['BatchID']; ?>">
                  <div class="mb-3">
                    <label class="form-label">Storage</label>
                    <select name="storage_id" class="form-select" required>
                      <?php foreach ($storages as $st): ?>
                        <option value="<?php echo $st['StorageID']; ?>" <?php if ($st['StorageID'] == $row['StorageID']) echo 'selected'; ?>><?php echo htmlspecialchars($st['Name']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Factory</label>
                    <select name="factory_id" class="form-select" required>
                      <?php foreach ($factories as $fc): ?>
                        <option value="<?php echo $fc['FactoryID']; ?>" <?php if ($fc['FactoryID'] == $row['FactoryID']) echo 'selected'; ?>><?php echo htmlspecialchars($fc['Name']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Packaging Date</label>
                    <input type="date" name="packaging_date" class="form-control" value="<?php echo htmlspecialchars($row['PackagingDate']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control" value="<?php echo htmlspecialchars($row['ExpiryDate']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="<?php echo htmlspecialchars($row['Quantity']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Weight (lb)</label>
                    <input type="number" step="0.01" name="weight" class="form-control" value="<?php echo htmlspecialchars($row['Weight']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Barcode</label>
                    <input type="text" name="barcode" class="form-control" value="<?php echo htmlspecialchars($row['Barcode']); ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Packaging Details</label>
                    <input type="text" name="details" class="form-control" value="<?php echo htmlspecialchars($row['PackagingDetails']); ?>">
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
        <h5 class="modal-title" id="addModalLabel">Add Batch</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="expiration.php">
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
            <label class="form-label">Factory</label>
            <select name="factory_id" class="form-select" required>
              <?php foreach ($factories as $fc): ?>
                <option value="<?php echo $fc['FactoryID']; ?>"><?php echo htmlspecialchars($fc['Name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Packaging Date</label>
            <input type="date" name="packaging_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Weight (lb)</label>
            <input type="number" step="0.01" name="weight" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Barcode</label>
            <input type="text" name="barcode" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Packaging Details</label>
            <input type="text" name="details" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="create" class="btn btn-primary">Add Batch</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('#batchTable').DataTable({
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
  <div class="col-md-7 mb-4">
    <div class="card">
      <div class="card-header bg-primary text-white">Batch Expiry Countdown</div>
      <div class="card-body">
        <canvas id="expiryBarChart" height="200"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-5 mb-4">
    <div class="card">
      <div class="card-header bg-danger text-white">Expiring Soon</div>
      <div class="card-body">
        <canvas id="expiringPieChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<?php
// Prepare data for analytics
$bar_labels = [];
$bar_data = [];
$soon = 0;
$not_soon = 0;
foreach ($rows as $row) {
  $label = 'Batch #' . $row['BatchID'];
  $days = '';
  if (!empty($row['ExpiryDate'])) {
    $expiry = strtotime($row['ExpiryDate']);
    $today  = strtotime(date('Y-m-d'));
    $days = floor(($expiry - $today) / 86400);
  }
  $bar_labels[] = $label;
  $bar_data[] = is_numeric($days) ? (int)$days : null;
  if ($days !== '' && $days <= 3) {
    $soon++;
  } elseif ($days !== '') {
    $not_soon++;
  }
}
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Batch Expiry Countdown Bar Chart
const expiryBarCtx = document.getElementById('expiryBarChart').getContext('2d');
const expiryBarChart = new Chart(expiryBarCtx, {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($bar_labels); ?>,
    datasets: [{
      label: 'Days Remaining',
      data: <?php echo json_encode($bar_data); ?>,
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
      y: { beginAtZero: true, title: { display: true, text: 'Days Remaining' } },
      x: { title: { display: true, text: 'Batch' } }
    }
  }
});

// Expiring Soon Pie Chart
const expiringPieCtx = document.getElementById('expiringPieChart').getContext('2d');
const expiringPieChart = new Chart(expiringPieCtx, {
  type: 'pie',
  data: {
    labels: ['Expiring ≤ 3 days', 'More than 3 days'],
    datasets: [{
      data: [<?php echo $soon; ?>, <?php echo $not_soon; ?>],
      backgroundColor: [
        'rgba(255, 99, 132, 0.7)',
        'rgba(75, 192, 192, 0.7)'
      ],
      borderColor: '#fff',
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' },
      title: { display: false }
    }
  }
});
</script>

<?php include 'footer.php'; ?>