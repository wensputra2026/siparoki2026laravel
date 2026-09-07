<?php
header('Content-Type: text/plain');

$appRoot = dirname(__DIR__);
$gitExe = 'C:\\Program Files\\Git\\bin\\git.exe';
$nodeExe = 'C:\\laragon\\bin\\nodejs\\node-v18\\node.exe';
$viteCli = $appRoot . '\\node_modules\\vite\\bin\\vite.js';

echo "=== 1. RESOLVE UNMERGED CONFLICT (RM database/siparoki.sql) ===\n";
echo shell_exec("cd /d \"$appRoot\" && \"$gitExe\" rm database/siparoki.sql 2>&1") . "\n";

echo "=== 2. REMOVE SCRATCH SCRIPTS ===\n";
$temp = [
    $appRoot . '/public/check_pushed_state.php',
    $appRoot . '/public/run_git_deploy.php',
];
foreach ($temp as $f) {
    if (file_exists($f)) {
        unlink($f);
        echo "Deleted: " . basename($f) . "\n";
    }
}

echo "=== 3. REBUILD VITE PRODUCTION BUNDLE ===\n";
$buildOut = shell_exec("cd /d \"$appRoot\" && \"$nodeExe\" \"$viteCli\" build 2>&1");
echo "Build result: " . substr($buildOut, -300) . "\n";

echo "=== 4. STAGE ALL UPDATED BUILD & EXPORT ASSETS ===\n";
echo shell_exec("cd /d \"$appRoot\" && \"$gitExe\" add public/build database/data/siparoki.sql database/siparoki_clean.sql 2>&1") . "\n";
echo shell_exec("cd /d \"$appRoot\" && \"$gitExe\" add -A 2>&1") . "\n";

echo "=== 5. COMMIT MERGE ===\n";
$commitOut = shell_exec("cd /d \"$appRoot\" && \"$gitExe\" commit -m \"Merge remote origin/main, update clean database dump, and sync production assets\" 2>&1");
echo $commitOut . "\n";

echo "=== 6. PUSH TO ORIGIN MAIN ===\n";
$pushMain = shell_exec("cd /d \"$appRoot\" && \"$gitExe\" push origin main 2>&1");
echo $pushMain . "\n";

echo "=== 7. PUSH TO ORIGIN PAROKI ===\n";
$pushParoki = shell_exec("cd /d \"$appRoot\" && \"$gitExe\" push origin main:paroki 2>&1");
echo $pushParoki . "\n";

echo "=== 8. VERIFY GIT LOG ===\n";
echo shell_exec("cd /d \"$appRoot\" && \"$gitExe\" log -2 --oneline 2>&1") . "\n";

unlink(__FILE__);
