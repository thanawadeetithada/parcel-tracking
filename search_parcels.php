<?php
session_start();
require_once 'db.php';

$search = $_GET['search'] ?? '';
$searchSql = $conn->real_escape_string($search);
$sql = "SELECT * FROM parcels";

if (!empty($searchSql)) {
    $sql .= " WHERE item_name LIKE '%$searchSql%'";
}

$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $i = 1;
    while ($row = $result->fetch_assoc()) {
        ?>
        <tr class='text-center'>
            <td><?= $i++ ?></td>
            <td class='text-left'><?= htmlspecialchars($row['item_name']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['usage_duration']) ?></td>
            <td><?= htmlspecialchars($row['price']) ?></td>
            <td><?= htmlspecialchars($row['budget_year']) ?></td>
            <td><?= htmlspecialchars($row['start_date']) ?></td>
            <td><?= htmlspecialchars($row['end_date']) ?></td>
            <td><?= htmlspecialchars($row['user_responsible']) ?></td>
            <td><?= htmlspecialchars($row['note']) ?: '-' ?></td>
            <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'superadmin')): ?>
            <td class='btn-edit-delete'>
                <button class='btn btn-sm btn-warning btn-edit' data-bs-toggle='modal'
                    data-bs-target='#editModal'
                    data-id='<?= $row['id'] ?>'
                    data-item_name='<?= htmlspecialchars($row['item_name']) ?>'
                    data-category='<?= $row['category'] ?>'
                    data-usage_duration='<?= $row['usage_duration'] ?>'
                    data-price='<?= $row['price'] ?>'
                    data-budget_year='<?= $row['budget_year'] ?>'
                    data-start_date='<?= $row['start_date'] ?>'
                    data-end_date='<?= $row['end_date'] ?>'
                    data-user_responsible='<?= htmlspecialchars($row['user_responsible']) ?>'
                    data-note='<?= htmlspecialchars($row['note']) ?>'>
                    <i class='fa-solid fa-pen'></i>
                </button>
                <button class='btn btn-sm btn-danger btn-delete' data-bs-toggle='modal'
                    data-bs-target='#deleteModal'
                    data-id='<?= $row['id'] ?>'>
                    <i class='fa-solid fa-trash'></i>
                </button>
            </td>
            <?php endif; ?>
        </tr>
        <?php
    }
} else {
    echo "<tr><td colspan='11' class='text-center text-muted py-4'>
            <i class='fa-solid fa-circle-exclamation me-2'></i> ไม่พบข้อมูลพัสดุ
          </td></tr>";
}
?>