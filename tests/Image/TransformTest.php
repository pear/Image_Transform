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

        try {
            $driver = Image_Transform::factory('imagick');
        } catch (Image_Transform_Exception $ite) {
            if ($ite->getCode() == IMAGE_TRANSFORM_ERROR_UNSUPPORTED) {
                $this->markTestSkipped($ite->getMessage());
            }

            throw $ite;
        }

        $this->assertInstanceOf('Image_Transform_Driver_Imagick3', $driver);
    }
}
?>
