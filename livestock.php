<?php


require_once 'db.php';

// Helper: next LivestockID if none provided (dump has no AUTO_INCREMENT)
function nextLivestockId(PDO $pdo): int
{
  $max = $pdo->query('SELECT COALESCE(MAX(LivestockID), 0) AS mx FROM livestock')->fetch(PDO::FETCH_ASSOC);
  return (int)$max['mx'] + 1;
}

// Handle deletion
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $stmt = $pdo->prepare('DELETE FROM livestock WHERE LivestockID = ?');
  $stmt->execute([$id]);
  header('Location: livestock.php');
  exit;
}

// Handle creation
if (isset($_POST['create'])) {
  $livestockID = trim($_POST['livestock_id']);
  $livestockID = $livestockID === '' ? nextLivestockId($pdo) : (int)$livestockID;

  $farmerID    = (int)$_POST['farmer_id'];
  $animalType  = trim($_POST['animal_type']);
  $quantity    = (int)$_POST['quantity'];
  $date        = $_POST['date'];

  $stmt = $pdo->prepare('INSERT INTO livestock (LivestockID, FarmerID, AnimalType, Quantity, Date) VALUES (?,?,?,?,?)');
  $stmt->execute([$livestockID, $farmerID, $animalType, $quantity, $date]);

  header('Location: livestock.php');
  exit;
}

// Handle update
if (isset($_POST['update'])) {
  $livestockID = (int)$_POST['livestock_id'];
  $farmerID    = (int)$_POST['farmer_id'];
  $animalType  = trim($_POST['animal_type']);
  $quantity    = (int)$_POST['quantity'];
  $date        = $_POST['date'];
  $stmt = $pdo->prepare('UPDATE livestock SET FarmerID=?, AnimalType=?, Quantity=?, Date=? WHERE LivestockID=?');
  $stmt->execute([$farmerID, $animalType, $quantity, $date, $livestockID]);
  header('Location: livestock.php');
  exit;
}

include 'header.php';

// Fetch farmers for dropdown (+context to show in modal)
$farmers = $pdo->query('SELECT FarmerID, Name, Size, Address FROM farmer ORDER BY Name')->fetchAll(PDO::FETCH_ASSOC);

// Fetch livestock list with farmer names and processed batch info
$sql = 'SELECT ls.LivestockID, ls.FarmerID, f.Name AS FarmerName, ls.AnimalType, ls.Quantity, ls.Date,
               pb.ProcessingDate, pb.Quantity AS ProcessedQty, pb.Weight AS ProcessedWeight
        FROM livestock ls
        LEFT JOIN farmer f ON ls.FarmerID = f.FarmerID
        LEFT JOIN processedbatch pb ON ls.LivestockID = pb.LivestockID
        ORDER BY ls.LivestockID';
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>
<h2 class="mb-4">Incoming Livestock</h2>
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
  <i class="fa fa-plus"></i> Add Livestock
</button>

<div class="table-container">
  <table id="livestockTable" class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Farmer</th>
        <th>Animal Type</th>
        <th>Quantity</th>
        <th>Arrival Date</th>
        <th>Processed Date</th>
        <th>Processed Qty</th>
        <th>Processed Weight</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['LivestockID']); ?></td>
          <td><?php echo htmlspecialchars($row['FarmerName']); ?></td>
          <td><?php echo htmlspecialchars($row['AnimalType']); ?></td>
          <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
          <td><?php echo htmlspecialchars($row['Date']); ?></td>
          <td><?php echo htmlspecialchars($row['ProcessingDate']); ?></td>
          <td><?php echo htmlspecialchars($row['ProcessedQty']); ?></td>
          <td><?php echo htmlspecialchars($row['ProcessedWeight']); ?></td>
          <td>
            <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['LivestockID']; ?>"><i class="fa fa-edit"></i></a>
            <a href="livestock.php?delete=<?php echo $row['LivestockID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this entry?');"><i class="fa fa-trash"></i></a>
          </td>
        </tr>
        <!-- Edit Modal -->
        <div class="modal fade" id="editModal<?php echo $row['LivestockID']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $row['LivestockID']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $row['LivestockID']; ?>">Edit Livestock #<?php echo $row['LivestockID']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form method="post" action="livestock.php">
                <div class="modal-body">
                  <input type="hidden" name="livestock_id" value="<?php echo $row['LivestockID']; ?>">

                  <div class="mb-3">
                    <label class="form-label">Farmer</label>
                    <select name="farmer_id" class="form-select" id="edit-farmer-<?php echo $row['LivestockID']; ?>" required>
                      <?php foreach ($farmers as $far): ?>
                        <option
                          value="<?php echo $far['FarmerID']; ?>"
                          data-size="<?php echo htmlspecialchars($far['Size']); ?>"
                          data-addr="<?php echo htmlspecialchars($far['Address']); ?>"
                          <?php if ($far['FarmerID'] == $row['FarmerID']) echo 'selected'; ?>>
                          <?php echo htmlspecialchars($far['Name']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="row g-2 mb-3">
                    <div class="col">
                      <label class="form-label">Farmer Size</label>
                      <input type="text" class="form-control" id="edit-size-<?php echo $row['LivestockID']; ?>" value="<?php
                                                                                                                        $cur = array_filter($farmers, fn($f) => $f['FarmerID'] == $row['FarmerID']);
                                                                                                                        $cur = $cur ? array_values($cur)[0] : ['Size' => '', 'Address' => ''];
                                                                                                                        echo htmlspecialchars($cur['Size'] ?? '');
                                                                                                                        ?>" readonly>
                    </div>
                    <div class="col">
                      <label class="form-label">Farmer Address</label>
                      <input type="text" class="form-control" id="edit-addr-<?php echo $row['LivestockID']; ?>" value="<?php
                                                                                                                        echo htmlspecialchars($cur['Address'] ?? '');
                                                                                                                        ?>" readonly>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Animal Type</label>
                    <input type="text" name="animal_type" class="form-control" value="<?php echo htmlspecialchars($row['AnimalType']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="<?php echo htmlspecialchars($row['Quantity']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($row['Date']); ?>" required>
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
        <h5 class="modal-title" id="addModalLabel">Add Livestock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="livestock.php">
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Livestock ID <span class="text-muted">(optional)</span></label>
            <input type="number" name="livestock_id" class="form-control" placeholder="Leave empty to auto-assign next ID">
          </div>

          <div class="mb-3">
            <label class="form-label">Farmer</label>
            <select name="farmer_id" class="form-select" id="add-farmer" required>
              <?php foreach ($farmers as $far): ?>
                <option
                  value="<?php echo $far['FarmerID']; ?>"
                  data-size="<?php echo htmlspecialchars($far['Size']); ?>"
                  data-addr="<?php echo htmlspecialchars($far['Address']); ?>">
                  <?php echo htmlspecialchars($far['Name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted d-block">Selecting a farmer auto-fills their Size and Address.</small>
          </div>

          <div class="row g-2 mb-3">
            <div class="col">
              <label class="form-label">Farmer Size</label>
              <input type="text" class="form-control" id="add-size" readonly>
            </div>
            <div class="col">
              <label class="form-label">Farmer Address</label>
              <input type="text" class="form-control" id="add-addr" readonly>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Animal Type</label>
            <input type="text" name="animal_type" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="create" class="btn btn-primary">Add Livestock</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Auto-fill farmer context in ADD modal
  document.getElementById('add-farmer')?.addEventListener('change', function() {
    const opt = this.selectedOptions[0];
    if (!opt) return;
    document.getElementById('add-size').value = opt.getAttribute('data-size') || '';
    document.getElementById('add-addr').value = opt.getAttribute('data-addr') || '';
  });

  // Auto-fill farmer context in EDIT modals via event delegation
  document.addEventListener('change', function(e) {
    const sel = e.target;
    if (!sel.matches('select[id^="edit-farmer-"]')) return;
    const id = sel.id.replace('edit-farmer-', '');
    const opt = sel.selectedOptions[0];
    if (!opt) return;
    document.getElementById('edit-size-' + id).value = opt.getAttribute('data-size') || '';
    document.getElementById('edit-addr-' + id).value = opt.getAttribute('data-addr') || '';
  });

  $(document).ready(function() {
    $('#livestockTable').DataTable({
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
      <div class="card-header bg-primary text-white">Livestock Arrival Trend</div>
      <div class="card-body">
        <canvas id="arrivalTrendChart" height="200"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="card">
      <div class="card-header bg-success text-white">Animal Type Distribution</div>
      <div class="card-body">
        <canvas id="animalTypePieChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<?php
// Prepare data for analytics
// 1. Arrival trend (by month)
$trend = [];
foreach ($rows as $row) {
  $month = date('Y-m', strtotime($row['Date']));
  if (!isset($trend[$month])) $trend[$month] = 0;
  $trend[$month] += $row['Quantity'];
}
$trend_labels = array_keys($trend);
$trend_data = array_values($trend);

// 2. Animal type distribution
$type_dist = [];
foreach ($rows as $row) {
  $type = $row['AnimalType'];
  if (!isset($type_dist[$type])) $type_dist[$type] = 0;
  $type_dist[$type] += $row['Quantity'];
}
$type_labels = array_keys($type_dist);
$type_data = array_values($type_dist);
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Livestock Arrival Trend (Line Chart)
const arrivalTrendCtx = document.getElementById('arrivalTrendChart').getContext('2d');
const arrivalTrendChart = new Chart(arrivalTrendCtx, {
  type: 'line',
  data: {
    labels: <?php echo json_encode($trend_labels); ?>,
    datasets: [{
      label: 'Total Arrivals',
      data: <?php echo json_encode($trend_data); ?>,
      borderColor: 'rgba(54, 162, 235, 1)',
      backgroundColor: 'rgba(54, 162, 235, 0.2)',
      fill: true,
      tension: 0.3
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      title: { display: false }
    },
    scales: {
      y: { beginAtZero: true, title: { display: true, text: 'Quantity' } },
      x: { title: { display: true, text: 'Month' } }
    }
  }
});

// Animal Type Distribution (Pie Chart)
const animalTypePieCtx = document.getElementById('animalTypePieChart').getContext('2d');
const animalTypePieChart = new Chart(animalTypePieCtx, {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($type_labels); ?>,
    datasets: [{
      data: <?php echo json_encode($type_data); ?>,
      backgroundColor: [
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(255, 159, 64, 0.7)'
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