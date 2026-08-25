<?php
/**
 * Root Installer Forwarder
 * Automatically routes install.php to modern Laravel /installer route
 */
header('Location: /installer');
exit;
