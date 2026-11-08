<?php
$flashSuccess = $flash['success'] ?? null;
$flashError = $flash['error'] ?? null;
?>
<?php if (is_string($flashSuccess) && $flashSuccess !== ''): ?>
    <div class="alert alert--success" role="status"><?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if (is_string($flashError) && $flashError !== ''): ?>
    <div class="alert alert--danger" role="alert"><?= e($flashError) ?></div>
<?php endif; ?>
