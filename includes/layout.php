<?php
$theme = $_COOKIE["theme"] ?? "light";
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($theme, ENT_QUOTES, "UTF-8"); ?>">
<script>
  document.documentElement.dataset.theme = localStorage.getItem("theme") || document.documentElement.dataset.theme;
</script>
