<?php
require_once __DIR__ . '/db_config.php';

// Ambil data transaksi dari database
$sql = "SELECT * FROM `transaksi` ORDER BY `timestamp` DESC";
$result = $conn->query($sql);

$totalTransaksi = 0;
$totalNominal = 0;
$dataList = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dataList[] = $row;
        $totalTransaksi++;
        $totalNominal += (int)$row['payAmount'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Transaksi - Aiyo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }
        body {
            background-color: #f4f7fc;
            color: #2d3748;
            padding: 24px;
        }
        .container {
            max-width: 1300px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1a202c;
        }
        .header .btn-refresh {
            background-color: #3b82f6;
            color: #ffffff;
            padding: 9px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .header .btn-refresh:hover {
            background-color: #2563eb;
        }
        /* Stats Widget */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
        }
        .stat-label {
            font-size: 13px;
            color: #718096;
            font-weight: 500;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 22px;
            font-weight: 700;
            color: #1a202c;
        }
        /* Table Card */
        .table-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13.5px;
        }
        th {
            background-color: #f8fafc;
            color: #4a5568;
            font-weight: 600;
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        td {
            padding: 14px 16px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }
        tr:hover td {
            background-color: #fcfdfe;
        }
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .badge-paid {
            background-color: #def7ec;
            color: #03543f;
        }
        .badge-new {
            background-color: #fef08a;
            color: #713f12;
        }
        .badge-expired {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-other {
            background-color: #e2e8f0;
            color: #334155;
        }
        /* Links & Buttons */
        .btn-invoice {
            display: inline-block;
            background: #e0e7ff;
            color: #4338ca;
            padding: 4px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.2s;
        }
        .btn-invoice:hover {
            background: #c7d2fe;
        }
        .empty-state {
            text-align: center;
            padding: 48px 16px;
            color: #a0aec0;
            font-size: 15px;
        }
        .text-mono {
            font-family: monospace;
            font-size: 12px;
            color: #4a5568;
        }
        .customer-info .name {
            font-weight: 600;
            color: #1a202c;
        }
        .customer-info .meta {
            font-size: 12px;
            color: #718096;
            margin-top: 2px;
        }
        .items-badge {
            max-width: 250px;
            font-size: 12px;
            color: #4a5568;
            line-height: 1.4;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>Daftar Transaksi Aiyo</h1>
            <p style="color: #718096; font-size: 13px; margin-top: 4px;">Kelola dan monitor status pembayaran invoice.</p>
        </div>
        <div>
            <a href="lihat.php" class="btn-refresh">↻ Refresh Data</a>
        </div>
    </div>

    <!-- Statistik Ringkas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Transaksi</div>
            <div class="stat-value"><?= number_format($totalTransaksi, 0, ',', '.') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Nominal</div>
            <div class="stat-value">Rp <?= number_format($totalNominal, 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- Tabel Data Transaksi -->
    <div class="table-card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Reference ID</th>
                        <th>Pelanggan</th>
                        <th>Nominal</th>
                        <th>Item</th>
                        <th>Status</th>
                        <th>Waktu</th>
                        <th>Invoice URL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($dataList) > 0): ?>
                        <?php $no = 1; foreach ($dataList as $row): 
                            $status = strtoupper(trim($row['status']));
                            $badgeClass = 'badge-other';
                            if ($status === 'PAID') {
                                $badgeClass = 'badge-paid';
                            } elseif ($status === 'NEW') {
                                $badgeClass = 'badge-new';
                            } elseif ($status === 'EXPIRED') {
                                $badgeClass = 'badge-expired';
                            }

                            // Format deskripsi items jika berupa JSON
                            $itemsText = $row['items'];
                            $itemsDecoded = json_decode($row['items'], true);
                            if (is_array($itemsDecoded)) {
                                $itemsNames = [];
                                foreach ($itemsDecoded as $it) {
                                    $cnt = isset($it['itemCount']) ? "({$it['itemCount']}x)" : "";
                                    $name = $it['itemName'] ?? 'Barang';
                                    $itemsNames[] = htmlspecialchars($name) . " " . $cnt;
                                }
                                $itemsText = implode(', ', $itemsNames);
                            }
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <span class="text-mono"><?= htmlspecialchars($row['referenceId']) ?></span><br>
                                    <small class="text-mono" style="color:#94a3b8;">Inv: <?= htmlspecialchars($row['invoiceId']) ?></small>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <div class="name"><?= htmlspecialchars($row['userName']) ?></div>
                                        <div class="meta"><?= htmlspecialchars($row['userEmail']) ?> | <?= htmlspecialchars($row['userPhone']) ?></div>
                                    </div>
                                </td>
                                <td style="font-weight: 600; color: #1e293b;">
                                    Rp <?= number_format($row['payAmount'], 0, ',', '.') ?>
                                </td>
                                <td>
                                    <div class="items-badge">
                                        <?= $itemsText ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['status']) ?></span>
                                </td>
                                <td style="color: #64748b; white-space: nowrap;">
                                    <?= date('d M Y, H:i', strtotime($row['timestamp'])) ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['invoiceURL'])): ?>
                                        <a href="<?= htmlspecialchars($row['invoiceURL']) ?>" target="_blank" class="btn-invoice">Buka Invoice ↗</a>
                                    <?php else: ?>
                                        <span style="color:#cbd5e1;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                Belum ada data transaksi yang tersimpan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
<?php
$conn->close();
?>
