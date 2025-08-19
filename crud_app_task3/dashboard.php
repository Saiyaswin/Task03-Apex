<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$records_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_query = "";
if (!empty($search)) {
    $search_query = "WHERE title LIKE ?";
    $search_term = "%$search%";
}

$sql_count = "SELECT COUNT(*) FROM posts $search_query";
$stmt_count = $conn->prepare($sql_count);
if (!empty($search)) {
    $stmt_count->bind_param("s", $search_term);
}
$stmt_count->execute();
$stmt_count->bind_result($total_records);
$stmt_count->fetch();
$stmt_count->close();

$total_pages = ceil($total_records / $records_per_page);

$sql = "SELECT * FROM posts $search_query ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $stmt->bind_param("sii", $search_term, $offset, $records_per_page);
} else {
    $stmt->bind_param("ii", $offset, $records_per_page);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<link rel="stylesheet" href="style.css">
<div class="dashboard-container">
    <div class="dashboard-header">
        <h2>Welcome, <?= $_SESSION['username'] ?></h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>
    <a href="create.php" class="add-btn">+ Add Post</a>
    <table>
        <thead>
            <tr>
                <th>Title</th><th>Content</th><th>Created</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['content']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>
                    <a href="delete.php?id=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Delete?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>" class="page-link">Prev</a>
        <?php endif; ?>
        <?php for ($i=1; $i<=$total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="page-link <?= ($i==$page)?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>" class="page-link">Next</a>
        <?php endif; ?>
    </div>
</div>
