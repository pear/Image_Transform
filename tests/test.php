<?php
/**
 * Unit tests for Image_Transform package
 *
 * It is rather difficult to test such a package since it manipulates
 * images. Automation is limited, and manual/visual checks are required.
 *
 * @author Philippe Jausions <Philippe.Jausions@11abacus.com>
 * @version $Id$
 */

//ini_set('include_path', '.:/home/reywob/sites/it.mapledesign.co.uk/pear:/usr/lib/php');

// Pick which drivers to test
$drivers = array(
//    'Imagick',
//    'Imagick2',
//    'Imlib',
    'NetPBM',
    'IM',
    'GD'
    );

if (!defined('IMAGE_TRANSFORM_IM_PATH')) {
    define('IMAGE_TRANSFORM_IM_PATH', 'C:\\Program Files\\ImageMagick-6.0.2-Q16\\');
}
if (!defined('IMAGE_TRANSFORM_NETPBM_PATH')) {
    define('IMAGE_TRANSFORM_NETPBM_PATH', 'C:\\cygwin\\usr\\local\\bin\\netpbm\\');
}

if (!defined('IMAGE_TRANSFORM_LIB_PATH')) {
    define('IMAGE_TRANSFORM_LIB_PATH', '/usr/bin/');
}

// Where we'll put resulting image and HTML files...
define('TEST_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('TEST_IMAGE_DIR', TEST_DIR . 'images' . DIRECTORY_SEPARATOR);
define('TEST_TMP_DIR', TEST_DIR . 'tmp' . DIRECTORY_SEPARATOR);



/**
 * You shouldn't ave to modify anything below this point
 */

if (isset($_REQUEST['drivers'])) {
    $drivers = $_REQUEST['drivers'];
}

// Basic input validation
$supportedTestDrivers = array(
    'Imagick',
    'Imagick2',
    'Imlib',
    'NetPBM',
    'IM',
    'GD');
$drivers = array_intersect($supportedTestDrivers, $drivers);

require_once('PHPUnit.php');
require_once('Image/Transform.php');
require_once('test_common.php');

/**
 * Tests one driver
 *
 * @param string $driver driver's name
 * @see Image_Transform::factory()
 */
function test_driver($driver) {
    require_once './Driver/test_' . $driver . '.php';

    // Clean previous test images
    require_once 'System.php';
    System::rm(System::find(array(TEST_TMP_DIR . $driver . DIRECTORY_SEPARATOR,
        '-type', 'f', '-name', '*.*')));

    $suite = new PHPUnit_TestSuite('Image_TransformTest' . $driver);
    $result =& PHPUnit::run($suite);
    echo $result->toHTML();
}



/*
$driver = 'GD';
$t =& Image_Transform::factory($driver);
print_r($t);
$t->load(TEST_IMAGE_DIR . 'mixed.jpg');
print_r($t->scaleByX(200));
$t->save(TEST_TMP_DIR . $driver . DIRECTORY_SEPARATOR  . 'test.jpg', 'jpeg');
exit;
*/

ob_start();
$timestamp = time();

$title = 'Regression tests for Image_Transform package';
include 'header.php';
?>

<p>View test results for:</p>
<ul>
<?php

foreach ((array) $drivers as $driver) {
    echo '<li><a href="' . $driver . '/test.html">' . $driver . '</li>';
    ob_start();

    $title = 'Regression test results for Image_Transform\'s ' . $driver . ' driver';
    include 'header.php';
?>
<pre class="elisp">
<?php
    test_driver($driver);
?>
</pre>
<table>
<thead>
<tr>
<th>Test</th>
<th><abbr title="Original">Orig.</abbr></th>
<th>Expected</th>
<th>Actual</th>
</tr>
</thead>
<tbody>
<?php
    foreach (Image_TransformTestHelper::log() as $name => $images) {
        $image    = $images['result'];
        $original = $images['original'];
?>
    <tr>
    <th><?php echo htmlspecialchars($name); ?></th>
    <td align="center"><a href="../../images/<?php echo $original; ?>" target="_blank" title="Click to view original image"><img src="../../images/icon_15x16.png" alt="Img" title="Original image" width="15" height="16" border="0" /></a></td>
    <td class="image"><img src="../../images/expected/<?php echo $image; ?>" alt="" title="Expected result" /></td>
    <td class="image"><img src="<?php echo $image . '?t=' . $timestamp; ?>" alt="" title="Actual result" /></td>
    </tr>
<?php
    }
?>
</tbody>
</table>
<?php
    include 'footer.php';

    file_put_contents(TEST_TMP_DIR . $driver . DIRECTORY_SEPARATOR . 'test.html', ob_get_clean());
}
?>
</ul>
<?php
include 'footer.php';
file_put_contents(TEST_TMP_DIR . 'test.html', ob_get_clean());

// Accessed from a live web server?
if (isset($_SERVER['REQUEST_METHOD'])) {
    readfile(TEST_TMP_DIR . 'test.html');

} elseif (count($drivers) > 1) {
    echo 'To view result open file: ' . TEST_TMP_DIR . 'test.html';
} else {
    echo 'To view result open file: ' . TEST_TMP_DIR . $driver . DIRECTORY_SEPARATOR . 'test.html';
}

?>