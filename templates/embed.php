<?php
include __DIR__ . '/../src/db.php';
$query = $db->query("SELECT * FROM prayers WHERE approved = 1 ORDER BY created_at DESC");
$posts = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Prayer Wall</title>
  <style>
    body { font-family: sans-serif; }
    .prayer { margin-bottom: 1em; padding: 0.5em; border-bottom: 1px solid #ccc; }
  </style>
</head>
<body>
  <h2>Prayer Wall</h2>
  <?php foreach($posts as $post): ?>
    <div class="prayer">
      <strong><?= ucfirst($post['type']) ?></strong><br>
      <?= htmlspecialchars($post['message']) ?><br>
      <small>Posted <?= $post['created_at'] ?></small>
    </div>
  <?php endforeach; ?>
</body>
</html>
