<?php
require_once 'Image/Transform.php';

/**
* Image_Transform driver tests.
*
* @author Christian Weiske <cweiske@php.net>
*/
class Image_TransformTest extends PHPUnit_Framework_TestCase
{
    public function testFactoryImagick3Php5()
    {
        if (version_compare(PHP_VERSION, '5.0.0', '<')) {
            $this->markTestSkipped('PHP version is lower than php 5.0.0');
        }

        $driver = Image_Transform::factory('imagick');
        $bError = PEAR::isError($driver);
        if ($bError) {
            if ($driver->getCode() == IMAGE_TRANSFORM_ERROR_UNSUPPORTED) {
                $this->markTestSkipped($driver->getMessage());
            } else {
                $this->fail($driver->getMessage(), $driver->getCode());
            }
        }

        $this->assertInstanceOf('Image_Transform_Driver_Imagick3', $driver);
    }
}
?>
