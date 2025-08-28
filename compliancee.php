<?php


require_once 'db.php';

// Handle recall operation
if (isset($_GET['recall'])) {
  $batchID = (int)$_GET['recall'];
  // delete distribution records for this batch
  $pdo->prepare('DELETE FROM batchretailer WHERE BatchID=?')->execute([$batchID]);
  $pdo->prepare('DELETE FROM batchwholesaler WHERE BatchID=?')->execute([$batchID]);
  $pdo->prepare('DELETE FROM batchwarehouse WHERE BatchID=?')->execute([$batchID]);
  header('Location: compliance.php');
  exit;
}

include 'header.php';

// Fetch distribution and compliance data
$sql = 'SELECT pb.BatchID, pb.Barcode, pb.PackagingDate, pb.ExpiryDate,
               pf.Name AS FactoryName, pf.Certification,
               (SELECT COUNT(*) FROM batchretailer br WHERE br.BatchID = pb.BatchID) AS RetailCount,
               (SELECT COUNT(*) FROM batchwholesaler bw WHERE bw.BatchID = pb.BatchID) AS WholesaleCount,
               (SELECT COUNT(*) FROM batchwarehouse bh WHERE bh.BatchID = pb.BatchID) AS WarehouseCount
        FROM packagedbatch pb
        LEFT JOIN packagingfactory pf ON pb.FactoryID = pf.FactoryID
        ORDER BY pb.BatchID';
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>
<h2 class="mb-4">Compliance &amp; Recall</h2>
<p class="text-muted">This section helps ensure food safety and regulatory compliance by tracking where each batch has been distributed. Batches from factories lacking certification or nearing expiry are highlighted. Use the recall button to remove a batch from all distribution channels.</p>

<div class="table-container">
  <table id="complianceTable" class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Batch ID</th>
        <th>Barcode</th>
        <th>Packaging Date</th>
        <th>Expiry Date</th>
        <th>Days Remaining</th>
        <th>Factory</th>
        <th>Certification</th>
        <th>Retailers</th>
        <th>Wholesalers</th>
        <th>Warehouses</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <?php
        $daysRemaining = '';
        if ($row['ExpiryDate']) {
          $daysRemaining = floor((strtotime($row['ExpiryDate']) - strtotime(date('Y-m-d'))) / 86400);
        }
        $rowClass = '';
        if ($daysRemaining !== '' && $daysRemaining <= 0) {
          $rowClass = 'table-danger';
        } elseif (empty($row['Certification'])) {
          $rowClass = 'table-warning';
        }
        ?>
        <tr class="<?php echo $rowClass; ?>">
          <td><?php echo htmlspecialchars($row['BatchID']); ?></td>
          <td><?php echo htmlspecialchars($row['Barcode']); ?></td>
          <td><?php echo htmlspecialchars($row['PackagingDate']); ?></td>
          <td><?php echo htmlspecialchars($row['ExpiryDate']); ?></td>
          <td><?php echo ($daysRemaining !== '' ? htmlspecialchars($daysRemaining) : ''); ?></td>
          <td><?php echo htmlspecialchars($row['FactoryName']); ?></td>
          <td><?php echo htmlspecialchars($row['Certification']); ?></td>
          <td><?php echo htmlspecialchars($row['RetailCount']); ?></td>
          <td><?php echo htmlspecialchars($row['WholesaleCount']); ?></td>
          <td><?php echo htmlspecialchars($row['WarehouseCount']); ?></td>
          <td>
            <a href="compliance.php?recall=<?php echo $row['BatchID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to recall this batch? Distribution records will be removed.');"><i class="fa fa-exclamation-triangle"></i> Recall</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
  $(document).ready(function() {
    $('#complianceTable').DataTable({
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
      <div class="card-header bg-primary text-white">Batch Distribution</div>
      <div class="card-body">
        <canvas id="distributionBarChart" height="200"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-5 mb-4">
    <div class="card">
      <div class="card-header bg-danger text-white">Certification & Expiry Status</div>
      <div class="card-body">
        <canvas id="certExpiryPieChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<?php
// Prepare data for analytics
$batch_labels = [];
$retail = [];
$wholesale = [];
$warehouse = [];
$certified = 0;
$uncertified = 0;
$expired = 0;
$not_expired = 0;
foreach ($rows as $row) {
  $batch_labels[] = 'Batch #' . $row['BatchID'];
  $retail[] = (int)$row['RetailCount'];
  $wholesale[] = (int)$row['WholesaleCount'];
  $warehouse[] = (int)$row['WarehouseCount'];
  $daysRemaining = '';
  if ($row['ExpiryDate']) {
    $daysRemaining = floor((strtotime($row['ExpiryDate']) - strtotime(date('Y-m-d'))) / 86400);
  }
  if (empty($row['Certification'])) {
    $uncertified++;
  } else {
    $certified++;
  }
  if ($daysRemaining !== '' && $daysRemaining <= 0) {
    $expired++;
  } else if ($daysRemaining !== '') {
    $not_expired++;
  }
}
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Batch Distribution Bar Chart
const distBarCtx = document.getElementById('distributionBarChart').getContext('2d');
const distBarChart = new Chart(distBarCtx, {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($batch_labels); ?>,
    datasets: [
      {
        label: 'Retailers',
        data: <?php echo json_encode($retail); ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.7)'
      },
      {
        label: 'Wholesalers',
        data: <?php echo json_encode($wholesale); ?>,
        backgroundColor: 'rgba(255, 206, 86, 0.7)'
      },
      {
        label: 'Warehouses',
        data: <?php echo json_encode($warehouse); ?>,
        backgroundColor: 'rgba(75, 192, 192, 0.7)'
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'top' },
      title: { display: false }
    },
    scales: {
      y: { beginAtZero: true, title: { display: true, text: 'Count' } },
      x: { title: { display: true, text: 'Batch' } }
    }
  }
});

// Certification & Expiry Status Pie Chart
const certExpiryPieCtx = document.getElementById('certExpiryPieChart').getContext('2d');
const certExpiryPieChart = new Chart(certExpiryPieCtx, {
  type: 'pie',
  data: {
    labels: ['Certified', 'Uncertified', 'Expired', 'Not Expired'],
    datasets: [{
      data: [<?php echo $certified; ?>, <?php echo $uncertified; ?>, <?php echo $expired; ?>, <?php echo $not_expired; ?>],
      backgroundColor: [
        'rgba(75, 192, 192, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)'
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