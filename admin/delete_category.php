<?php
require_once 'check_login.php';

if (isset($_GET['slug'])) {
    $db->deleteBySlug($_GET['slug']);
}
?>
<script>
window.location.href = 'index.php?page=categories&msg=deleted';
</script>