<?php
ini_set('include_path', '.:/home/reywob/sites/it.mapledesign.co.uk/pear:/usr/lib/php');
// Unit tests for Image_Transform package
//
// Note: It is rather difficult to test such a package since it manipulates
//       images. Automation is limited, and manual/visual checks are required.

require_once('PHPUnit.php');
require_once('Image/Transform.php');

// Where we'll put resulting image and HTML files...
define('TEST_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('TEST_IMAGE_DIR', TEST_DIR . 'images' . DIRECTORY_SEPARATOR);
define('TEST_TMP_DIR', TEST_DIR . 'tmp' . DIRECTORY_SEPARATOR);

require_once('test_common.php');

function test_driver($driver) {
    require_once('./Driver/test_' . $driver . '.php');
    $suite = new PHPUnit_TestSuite('Image_TransformTest' . $driver);
    $result =& PHPUnit::run($suite);
    echo $result->toString();
}

if (!defined('IMAGE_TRANSFORM_LIB_PATH')) {
    define('IMAGE_TRANSFORM_LIB_PATH', '/usr/bin/');
}

/*
$t =& Image_Transform::factory('IM');
$t->load(TEST_IMAGE_DIR . 'imageinfo_96x32.png');
$t->save(TEST_TMP_DIR . 'test.png', 'png');
exit;
*/

test_driver('IM');
test_driver('GD');
test_driver('Imagick2');
?>