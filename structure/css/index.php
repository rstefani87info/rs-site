<?php
function includeAllCssInDirectory($directory)
{
    $cssFiles = glob('css/' . $directory . '/*.css');
    
    foreach ($cssFiles as $cssFile) {
        echo '<link rel="stylesheet" type="text/css" href="' . $cssFile . '">';
    }
}