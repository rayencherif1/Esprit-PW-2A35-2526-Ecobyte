<?php
ob_start();
?>
<div class="alert alert-warning">Programme introuvable. <a href="<?= e(BASE_URL) ?>/index.php?action=home">Retour</a></div>
<?php
$slot = ob_get_clean();
$pageTitle = 'Programme introuvable';
require VIEW_PATH . '/front/layout.php';
