<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared successfully!<br>";
} else {
    echo "⚠️ OPcache not available.<br>";
}

clearstatcache(true);
echo "✅ Realpath cache cleared!<br>";
echo "<br><strong>Done! Now delete this file and test assignment creation.</strong>";
?>
