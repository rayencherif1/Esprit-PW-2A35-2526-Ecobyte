<?php
$dir = new RecursiveDirectoryIterator('c:/Users/user/Documents/XAMPP/htdocs/marketplace');
$iterator = new RecursiveIteratorIterator($dir);
foreach ($iterator as $file) {
    if ($file->isFile() && in_array(strtolower($file->getExtension()), ['php', 'html'])) {
        $content = file_get_contents($file->getPathname());
        if (strpos($content, 'DT') !== false) {
            $content = str_replace('DT', 'DT', $content);
            file_put_contents($file->getPathname(), $content);
            echo "Replaced in " . $file->getPathname() . "\n";
        }
    }
}
echo "Done.\n";
?>
