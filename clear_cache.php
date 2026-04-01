<?php
$files = glob(__DIR__ . '/../storage/framework/views/*.php');
foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}
echo "View cache cleared successfully!";
?>
