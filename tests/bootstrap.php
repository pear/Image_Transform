<?php
error_reporting(E_ALL & ~E_DEPRECATED);
if (file_exists(dirname(__FILE__) . '/../Image/Transform.php')) {
    set_include_path(
        get_include_path()
        . PATH_SEPARATOR . dirname(__FILE__) . '/../'
    );
}
?>