<?php

require_once 'db.php';

// Handle deletion
if (isset($_GET['delete'])) {
  $uid = (int)$_GET['delete'];
  $stmt = $pdo->prepare('DELETE FROM offcuts WHERE UniqueID = ?');
  $stmt->execute([$uid]);
  header('Location: yieldanalysis.php');
  exit;
}

// Determine next ID for new record
$nextID = $pdo->query('SELECT IFNULL(MAX(UniqueID),0)+1 FROM offcuts')->fetchColumn();

// Handle creation
if (isset($_POST['create'])) {
  $uid  = (int)$_POST['unique_id'];
  $batchID = (int)$_POST['batch_id'];
  $type = trim($_POST['type']);
  $qty  = (int)$_POST['quantity'];
  $weight = floatval($_POST['weight']);
  $date = $_POST['date'];
  $method = trim($_POST['method']);
  $stmt = $pdo->prepare('INSERT INTO offcuts (UniqueID, BatchID, Type, Quantity, Weight, Date, DispositionMethod) VALUES (?,?,?,?,?,?,?)');
  $stmt->execute([$uid, $batchID, $type, $qty, $weight, $date, $method]);
  header('Location: yieldanalysis.php');
  exit;
}

// Handle update
if (isset($_POST['update'])) {
  $uid  = (int)$_POST['unique_id'];
  $batchID = (int)$_POST['batch_id'];
  $type = trim($_POST['type']);
  $qty  = (int)$_POST['quantity'];
  $weight = floatval($_POST['weight']);
  $date = $_POST['date'];
  $method = trim($_POST['method']);
  $stmt = $pdo->prepare('UPDATE offcuts SET BatchID=?, Type=?, Quantity=?, Weight=?, Date=?, DispositionMethod=? WHERE UniqueID=?');
  $stmt->execute([$batchID, $type, $qty, $weight, $date, $method, $uid]);
  header('Location: yieldanalysis.php');
  exit;
}

include 'header.php';

// Fetch offcuts with batch date for listing
$sql = 'SELECT oc.*, pb.ProcessingDate
        FROM offcuts oc
        LEFT JOIN processedbatch pb ON oc.BatchID = pb.BatchID
        ORDER BY oc.UniqueID';
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Aggregate totals by type
$summary = $pdo->query('SELECT Type, SUM(Weight) AS TotalWeight, SUM(Quantity) AS TotalQty FROM offcuts GROUP BY Type')->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for Chart.js
$chartLabels = [];
$chartData   = [];
foreach ($summary as $s) {
  $chartLabels[] = $s['Type'];
  $chartData[]   = $s['TotalWeight'];
}

?>
<h2 class="mb-4">Yield Analysis</h2>
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
  <i class="fa fa-plus"></i> Add Offcut
</button>

<!-- Chart -->
<div class="mb-4">
  <canvas id="yieldChart" height="100"></canvas>
</div>

<div class="table-container">
  <table id="offcutsTable" class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Unique ID</th>
        <th>Batch ID</th>
        <th>Processing Date</th>
        <th>Type</th>
        <th>Quantity</th>
        <th>Weight (lb)</th>
        <th>Date</th>
        <th>Disposition Method</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['UniqueID']); ?></td>
          <td><?php echo htmlspecialchars($row['BatchID']); ?></td>
          <td><?php echo htmlspecialchars($row['ProcessingDate']); ?></td>
          <td><?php echo htmlspecialchars($row['Type']); ?></td>
          <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
          <td><?php echo htmlspecialchars($row['Weight']); ?></td>
          <td><?php echo htmlspecialchars($row['Date']); ?></td>
          <td><?php echo htmlspecialchars($row['DispositionMethod']); ?></td>
          <td>
            <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['UniqueID']; ?>"><i class="fa fa-edit"></i></a>
            <a href="yieldanalysis.php?delete=<?php echo $row['UniqueID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this offcut?');"><i class="fa fa-trash"></i></a>
          </td>
        </tr>
        <!-- Edit Modal -->
        <div class="modal fade" id="editModal<?php echo $row['UniqueID']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $row['UniqueID']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $row['UniqueID']; ?>">Edit Offcut #<?php echo $row['UniqueID']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form method="post" action="yieldanalysis.php">
                <div class="modal-body">
                  <input type="hidden" name="unique_id" value="<?php echo $row['UniqueID']; ?>">
                  <div class="mb-3">
                    <label class="form-label">Batch ID</label>
                    <input type="number" name="batch_id" class="form-control" value="<?php echo htmlspecialchars($row['BatchID']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Type</label>
                    <input type="text" name="type" class="form-control" value="<?php echo htmlspecialchars($row['Type']); ?>" required>
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
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($row['Date']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Disposition Method</label>
                    <input type="text" name="method" class="form-control" value="<?php echo htmlspecialchars($row['DispositionMethod']); ?>">
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
        <h5 class="modal-title" id="addModalLabel">Add Offcut</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="yieldanalysis.php">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Unique ID</label>
            <input type="number" name="unique_id" class="form-control" value="<?php echo $nextID; ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Batch ID</label>
            <input type="number" name="batch_id" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Type</label>
            <input type="text" name="type" class="form-control" required>
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
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Disposition Method</label>
            <input type="text" name="method" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="create" class="btn btn-primary">Add Offcut</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('yieldChart').getContext('2d');
    const chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
          label: 'Total Offcut Weight (lb)',
          data: <?php echo json_encode($chartData); ?>,
          backgroundColor: 'rgba(54, 162, 235, 0.6)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Weight (lb)'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Offcut Type'
            }
          }
        }
      }
    });
  });
</script>

<script>
  $(document).ready(function() {
    $('#offcutsTable').DataTable({
      responsive: true,
      pageLength: 10,
      lengthChange: false,
      order: [
        [0, 'asc']
      ]
    });
  });
</script>

<?php include 'footer.php'; ?>