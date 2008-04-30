<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Image_Transform_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';


require_once 'Image/TransformTest.php';


class Image_Transform_AllTests
{
    public static function main()
    {

        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Image_Transform Tests');
        /** Add testsuites, if there is. */
        $suite->addTestSuite('Image_TransformTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Image_Transform_AllTests::main') {
    Image_Transform_AllTests::main();
}
?>