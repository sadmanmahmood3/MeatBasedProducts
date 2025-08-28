<?php

require_once 'db.php';

// Handle deletion
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $stmt = $pdo->prepare('DELETE FROM meatproduct WHERE ProductID = ?');
  $stmt->execute([$id]);
  header('Location: meatproduct.php');
  exit;
}

// Helper: next ProductID if none provided (MySQL dump has no AUTO_INCREMENT)
function nextProductId(PDO $pdo): int
{
  $max = $pdo->query('SELECT COALESCE(MAX(ProductID), 0) AS mx FROM meatproduct')->fetch(PDO::FETCH_ASSOC);
  return (int)$max['mx'] + 1;
}

// Handle creation
if (isset($_POST['create'])) {
  $productID = trim($_POST['product_id']);
  $productID = $productID === '' ? nextProductId($pdo) : (int)$productID;

  $batchID = !empty($_POST['batch_id']) ? (int)$_POST['batch_id'] : null;
  $cutType = trim($_POST['cut_type']);
  $storageReq = trim($_POST['storage_requirements']);
  $shelfLife = (int)$_POST['shelf_life'];

  $stmt = $pdo->prepare('INSERT INTO meatproduct (ProductID, BatchID, CutType, StorageRequirements, ShelfLife) VALUES (?,?,?,?,?)');
  $stmt->execute([$productID, $batchID, $cutType, $storageReq, $shelfLife]);

  header('Location: meatproduct.php');
  exit;
}

// Handle update
if (isset($_POST['update'])) {
  $productID = (int)$_POST['product_id'];
  $batchID   = !empty($_POST['batch_id']) ? (int)$_POST['batch_id'] : null;
  $cutType   = trim($_POST['cut_type']);
  $storageReq = trim($_POST['storage_requirements']);
  $shelfLife = (int)$_POST['shelf_life'];

  $stmt = $pdo->prepare('UPDATE meatproduct SET BatchID=?, CutType=?, StorageRequirements=?, ShelfLife=? WHERE ProductID=?');
  $stmt->execute([$batchID, $cutType, $storageReq, $shelfLife, $productID]);

  header('Location: meatproduct.php');
  exit;
}

include 'header.php';

// Retrieve product data with joins to display context
$sql = 'SELECT mp.ProductID, mp.BatchID, mp.CutType, mp.StorageRequirements, mp.ShelfLife, 
               pb.ProcessingDate, lf.AnimalType, pb.Quantity AS ProcessedQty, pb.Weight AS ProcessedWeight,
               pk.PackagingDetails, pf.Name AS FactoryName
        FROM meatproduct mp
        LEFT JOIN processedbatch pb ON mp.BatchID = pb.BatchID
        LEFT JOIN livestock lf ON pb.LivestockID = lf.LivestockID
        LEFT JOIN packagedbatch pk ON mp.BatchID = pk.BatchID
        LEFT JOIN packagingfactory pf ON pk.FactoryID = pf.FactoryID
        ORDER BY mp.ProductID';
$products = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Retrieve batch choices with joined context for the Add/Edit modal
$sqlBatches = 'SELECT pb.BatchID, pb.ProcessingDate, lf.AnimalType,
                      pk.PackagingDetails, pf.Name AS FactoryName
               FROM processedbatch pb
               LEFT JOIN livestock lf ON pb.LivestockID = lf.LivestockID
               LEFT JOIN packagedbatch pk ON pk.BatchID = pb.BatchID
               LEFT JOIN packagingfactory pf ON pk.FactoryID = pf.FactoryID
               ORDER BY pb.BatchID';
$batches = $pdo->query($sqlBatches)->fetchAll(PDO::FETCH_ASSOC);

// --- Analytics precompute ---
$tz = new DateTimeZone('Asia/Dhaka');        // ensure consistent days
$today = new DateTimeImmutable('today', $tz);

$total = count($products);
$withBatch = 0;
$sumShelf = 0;
$countShelf = 0;
$expiringSoon = 0; // <= 7 days
$cutCounts = [];
$cutShelfSum = [];
$cutShelfCnt = [];

foreach ($products as &$r) {
  if (!empty($r['BatchID'])) $withBatch++;

  if (is_numeric($r['ShelfLife'])) {
    $sumShelf += (int)$r['ShelfLife'];
    $countShelf++;
  }

  $daysLeft = null;
  if (!empty($r['ExpiryDate'])) {
    $exp = DateTimeImmutable::createFromFormat('Y-m-d', $r['ExpiryDate'], $tz)
      ?: new DateTimeImmutable($r['ExpiryDate'], $tz);
    $diff = $today->diff($exp);                      // <-- reversed order
    $daysLeft = (int)$diff->format('%r%a');          // negative if expired
    if ($daysLeft <= 7 && $daysLeft >= 0) $expiringSoon++;
  }
  $r['_daysLeft'] = $daysLeft;

  $cut = $r['CutType'] ?? 'Unknown';
  $cutCounts[$cut] = ($cutCounts[$cut] ?? 0) + 1;
  if (is_numeric($r['ShelfLife'])) {
    $cutShelfSum[$cut] = ($cutShelfSum[$cut] ?? 0) + (int)$r['ShelfLife'];
    $cutShelfCnt[$cut] = ($cutShelfCnt[$cut] ?? 0) + 1;
  }
}
$avgShelf = $countShelf ? round($sumShelf / $countShelf, 1) : 0;
$batchPct = $total ? round(($withBatch / $total) * 100, 1) : 0;

// Arrays for charts (unchanged)
$cutLabels = array_keys($cutCounts);
$cutValues = array_values($cutCounts);
$cutAvgValues = [];
foreach ($cutLabels as $lab) {
  $cutAvgValues[] = isset($cutShelfCnt[$lab]) && $cutShelfCnt[$lab] > 0
    ? round($cutShelfSum[$lab] / $cutShelfCnt[$lab], 1)
    : 0;
}


?>
<h2 class="mb-4">Meat Product Data</h2>
<div class="row g-3 mb-3">
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="fw-semibold text-muted mb-1">Total Products</div>
        <div class="h4 mb-0"><?php echo $total; ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="fw-semibold text-muted mb-1">Avg Shelf Life (days)</div>
        <div class="h4 mb-0"><?php echo $avgShelf; ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="fw-semibold text-muted mb-1">% Linked to Batch</div>
        <div class="h4 mb-0"><?php echo $batchPct; ?>%</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="fw-semibold text-muted mb-1">Expiring ≤ 7 days</div>
        <div class="h4 mb-0"><?php echo $expiringSoon; ?></div>
      </div>
    </div>
  </div>
</div>


<!-- Button trigger modal for adding product -->
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
  <i class="fa fa-plus"></i> Add Product
</button>

<div class="table-container">
  <table id="productTable" class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Batch ID</th>
        <th>Animal Type</th>
        <th>Cut Type</th>
        <th>Processing Date</th>
        <th>Storage Requirements</th>
        <th>Shelf Life (days)</th>
        <th>Packaging Details</th>
        <th>Supplier</th>
        <th>Days Left</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['ProductID']); ?></td>
          <td><?php echo htmlspecialchars($row['BatchID']); ?></td>
          <td><?php echo htmlspecialchars($row['AnimalType']); ?></td>
          <td><?php echo htmlspecialchars($row['CutType']); ?></td>
          <td><?php echo htmlspecialchars($row['ProcessingDate']); ?></td>
          <td><?php echo htmlspecialchars($row['StorageRequirements']); ?></td>
          <td><?php echo htmlspecialchars($row['ShelfLife']); ?></td>
          <td><?php echo htmlspecialchars($row['PackagingDetails']); ?></td>
          <td><?php echo htmlspecialchars($row['FactoryName']); ?></td>
          <td>
            <?php
            if ($row['_daysLeft'] === null) {
              echo '<span class="badge bg-secondary">N/A</span>';
            } else {
              $dl = (int)$row['_daysLeft'];
              $cls = $dl < 0 ? 'bg-danger' : ($dl <= 7 ? 'bg-warning text-dark' : 'bg-success');
              echo '<span class="badge ' . $cls . '">' . $dl . '</span>';
            }
            ?>
          </td>

          <td>
            <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['ProductID']; ?>"><i class="fa fa-edit"></i></a>
            <a href="meatproduct.php?delete=<?php echo $row['ProductID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fa fa-trash"></i></a>
          </td>
        </tr>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal<?php echo $row['ProductID']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $row['ProductID']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $row['ProductID']; ?>">Edit Product #<?php echo $row['ProductID']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form method="post" action="meatproduct.php">
                <div class="modal-body">
                  <input type="hidden" name="product_id" value="<?php echo $row['ProductID']; ?>">

                  <div class="mb-3">
                    <label class="form-label">Batch</label>
                    <select name="batch_id" class="form-select" id="edit-batch-<?php echo $row['ProductID']; ?>">
                      <option value="">— None —</option>
                      <?php foreach ($batches as $b): ?>
                        <option
                          value="<?php echo (int)$b['BatchID']; ?>"
                          data-animal="<?php echo htmlspecialchars($b['AnimalType']); ?>"
                          data-procdate="<?php echo htmlspecialchars($b['ProcessingDate']); ?>"
                          data-pack="<?php echo htmlspecialchars($b['PackagingDetails']); ?>"
                          data-factory="<?php echo htmlspecialchars($b['FactoryName']); ?>"
                          <?php echo ((string)$row['BatchID'] === (string)$b['BatchID']) ? 'selected' : ''; ?>>
                          #<?php echo (int)$b['BatchID']; ?> — <?php echo htmlspecialchars($b['AnimalType']); ?> (<?php echo htmlspecialchars($b['ProcessingDate']); ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <small class="text-muted d-block">Choose a Batch to auto-fill the context below.</small>
                  </div>

                  <div class="row g-2 mb-2">
                    <div class="col">
                      <label class="form-label">Animal Type</label>
                      <input type="text" class="form-control" id="edit-animal-<?php echo $row['ProductID']; ?>" value="<?php echo htmlspecialchars($row['AnimalType']); ?>" readonly>
                    </div>
                    <div class="col">
                      <label class="form-label">Processing Date</label>
                      <input type="text" class="form-control" id="edit-procdate-<?php echo $row['ProductID']; ?>" value="<?php echo htmlspecialchars($row['ProcessingDate']); ?>" readonly>
                    </div>
                  </div>
                  <div class="row g-2 mb-3">
                    <div class="col">
                      <label class="form-label">Packaging Details</label>
                      <input type="text" class="form-control" id="edit-pack-<?php echo $row['ProductID']; ?>" value="<?php echo htmlspecialchars($row['PackagingDetails']); ?>" readonly>
                    </div>
                    <div class="col">
                      <label class="form-label">Supplier (Factory)</label>
                      <input type="text" class="form-control" id="edit-factory-<?php echo $row['ProductID']; ?>" value="<?php echo htmlspecialchars($row['FactoryName']); ?>" readonly>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Cut Type</label>
                    <input type="text" name="cut_type" class="form-control" value="<?php echo htmlspecialchars($row['CutType']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Storage Requirements</label>
                    <input type="text" name="storage_requirements" class="form-control" value="<?php echo htmlspecialchars($row['StorageRequirements']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Shelf Life (days)</label>
                    <input type="number" name="shelf_life" class="form-control" value="<?php echo htmlspecialchars($row['ShelfLife']); ?>" required>
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
  <div class="row g-3 my-3">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="fw-semibold mb-2">Distribution by Cut Type</div>
          <canvas id="cutDistChart" height="200"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="fw-semibold mb-2">Average Shelf Life by Cut Type</div>
          <canvas id="cutAvgChart" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addModalLabel">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="meatproduct.php">
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Product ID <span class="text-muted">(optional)</span></label>
            <input type="number" name="product_id" class="form-control" placeholder="Leave empty to auto-assign next ID">
          </div>

          <div class="mb-3">
            <label class="form-label">Batch</label>
            <select name="batch_id" class="form-select" id="add-batch">
              <option value="">— None —</option>
              <?php foreach ($batches as $b): ?>
                <option
                  value="<?php echo (int)$b['BatchID']; ?>"
                  data-animal="<?php echo htmlspecialchars($b['AnimalType']); ?>"
                  data-procdate="<?php echo htmlspecialchars($b['ProcessingDate']); ?>"
                  data-pack="<?php echo htmlspecialchars($b['PackagingDetails']); ?>"
                  data-factory="<?php echo htmlspecialchars($b['FactoryName']); ?>">
                  #<?php echo (int)$b['BatchID']; ?> — <?php echo htmlspecialchars($b['AnimalType']); ?> (<?php echo htmlspecialchars($b['ProcessingDate']); ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted d-block">Choosing a Batch will populate the read-only context below.</small>
          </div>

          <div class="row g-2 mb-2">
            <div class="col">
              <label class="form-label">Animal Type</label>
              <input type="text" class="form-control" id="add-animal" readonly>
            </div>
            <div class="col">
              <label class="form-label">Processing Date</label>
              <input type="text" class="form-control" id="add-procdate" readonly>
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col">
              <label class="form-label">Packaging Details</label>
              <input type="text" class="form-control" id="add-pack" readonly>
            </div>
            <div class="col">
              <label class="form-label">Supplier (Factory)</label>
              <input type="text" class="form-control" id="add-factory" readonly>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Cut Type</label>
            <input type="text" name="cut_type" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Storage Requirements</label>
            <input type="text" name="storage_requirements" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Shelf Life (days)</label>
            <input type="number" name="shelf_life" class="form-control" required>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="create" class="btn btn-primary">Add Product</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Populate context fields when selecting a batch (Add)
  document.getElementById('add-batch')?.addEventListener('change', function() {
    const opt = this.selectedOptions[0];
    if (!opt) return;
    document.getElementById('add-animal').value = opt.getAttribute('data-animal') || '';
    document.getElementById('add-procdate').value = opt.getAttribute('data-procdate') || '';
    document.getElementById('add-pack').value = opt.getAttribute('data-pack') || '';
    document.getElementById('add-factory').value = opt.getAttribute('data-factory') || '';
  });

  // Populate context fields when selecting a batch (Edit) — one handler per row using event delegation
  document.addEventListener('change', function(e) {
    const sel = e.target;
    if (!sel.matches('select[id^=\"edit-batch-\"]')) return;
    const pid = sel.id.replace('edit-batch-', '');
    const opt = sel.selectedOptions[0];
    if (!opt) return;
    document.getElementById('edit-animal-' + pid).value = opt.getAttribute('data-animal') || '';
    document.getElementById('edit-procdate-' + pid).value = opt.getAttribute('data-procdate') || '';
    document.getElementById('edit-pack-' + pid).value = opt.getAttribute('data-pack') || '';
    document.getElementById('edit-factory-' + pid).value = opt.getAttribute('data-factory') || '';
  });

  $(document).ready(function() {
    $('#productTable').DataTable({
      responsive: true,
      pageLength: 10,
      lengthChange: false,
      ordering: true,
      order: [
        [0, 'asc']
      ]
    });
  });
</script>
<script>
  const dt = $('#productTable').DataTable({
    responsive: true,
    pageLength: 10,
    lengthChange: false,
    ordering: true,
    order: [
      [0, 'asc']
    ],

    dom: 'Bfrtip',
    buttons: [{
        extend: 'csvHtml5',
        title: 'meat_products',
        exportOptions: {
          columns: ':visible'
        }
      },
      {
        extend: 'print',
        title: 'Meat Product Data'
      }
    ]
  })


  function applyFilters() {
    const showSoon = document.getElementById('filterSoon').checked;
    const showUnbatched = document.getElementById('filterUnbatched').checked;

    dt.rows().every(function() {
      const tr = this.node();
      const daysLeft = tr.getAttribute('data-daysleft');
      const hasBatch = tr.getAttribute('data-batch') === '1';

      let visible = true;
      if (showSoon) {
        visible = visible && daysLeft !== '' && parseInt(daysLeft, 10) <= 7 && parseInt(daysLeft, 10) >= 0;
      }
      if (showUnbatched) {
        visible = visible && !hasBatch;
      }
      if (visible) $(tr).show();
      else $(tr).hide();
    });
  }

  document.getElementById('filterSoon').addEventListener('change', applyFilters);
  document.getElementById('filterUnbatched').addEventListener('change', applyFilters);
</script>
<script>
  const cutLabels = <?php echo json_encode($cutLabels); ?>;
  const cutValues = <?php echo json_encode($cutValues); ?>;
  const cutAvgValues = <?php echo json_encode($cutAvgValues); ?>;

  new Chart(document.getElementById('cutDistChart'), {
    type: 'doughnut',
    data: {
      labels: cutLabels,
      datasets: [{
        data: cutValues
      }]
    }
  });

  new Chart(document.getElementById('cutAvgChart'), {
    type: 'bar',
    data: {
      labels: cutLabels,
      datasets: [{
        label: 'Avg Shelf Life (days)',
        data: cutAvgValues
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
</script>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>


<?php include 'footer.php'; ?>