<?php
header('Content-Type: text/plain');

$appRoot = dirname(__DIR__);
$gitExe = 'C:\\Program Files\\Git\\bin\\git.exe';

echo "=== 1. REMOVE SCRATCH SCRIPTS ===\n";
$tempFiles = [
    $appRoot . '/public/test_exec.php',
    $appRoot . '/public/run_vite_build.php',
    $appRoot . '/public/inspect_uskup_logo.php',
    $appRoot . '/public/sync_kuasi.php',
    $appRoot . '/public/export_clean_db.php',
];
foreach ($tempFiles as $tf) {
    if (file_exists($tf)) {
        unlink($tf);
        echo "Deleted: " . basename($tf) . "\n";
    }
}

echo "\n=== 2. GIT STATUS BEFORE ADD ===\n";
echo shell_exec("cd /d \"$appRoot\" && \"$gitExe\" status -s 2>&1") . "\n";

echo "\n=== 3. GIT ADD ===\n";
$addOut = shell_exec("cd /d \"$appRoot\" && \"$gitExe\" add . 2>&1");
echo ($addOut ?: 'OK') . "\n";

echo "\n=== 4. GIT STATUS STAGED ===\n";
echo shell_exec("cd /d \"$appRoot\" && \"$gitExe\" status -s 2>&1") . "\n";

echo "\n=== 5. GIT COMMIT ===\n";
$msg = "feat: update database clean terbaru, logo & foto keuskupan/paroki, pemetaan kuasi paroki, halaman statistik & kesiapan hosting cPanel";
$commitOut = shell_exec("cd /d \"$appRoot\" && \"$gitExe\" commit -m \"$msg\" 2>&1");
echo $commitOut . "\n";

echo "\n=== 6. GIT PUSH TO ORIGIN MAIN ===\n";
$pushOut = shell_exec("cd /d \"$appRoot\" && \"$gitExe\" push origin main 2>&1");
echo $pushOut . "\n";

echo "\n=== 7. GIT LOG -1 ===\n";
echo shell_exec("cd /d \"$appRoot\" && \"$gitExe\" log -1 --stat 2>&1") . "\n";
