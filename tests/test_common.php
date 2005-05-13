<?php

/**
 * Unit tests for Image_Transform package
 *
 * It is rather difficult to test such a package since it manipulates
 * images. Automation is limited, and manual/visual checks are required.
 *
 * @author Philippe Jausions <Philippe .dot. Jausions @at@ 11abacus .dot. com>
 * @version $Id$
 */

/**
 * This class is to log test and image names created during the tests
 *
 * Should make it a Singleton???
 **/
class Image_TransformTestHelper {

    /**
     * Log test info or retrieve all infos
     *
     * @param string $name Name of test
     * @param string $image Name of image generated/expected
     * @param string $original Name of original image
     * @return mixed Void or array
     **/
    function log($name = null, $image = null, $original = null)
    {
        static $images = array();
        if (!is_null($name) && !is_null($image)) {
            $images[$name] = array(
                'result'   => $image,
                'original' => $original);
        } else {
            $return = $images;
            $images = array();
            return $return;
        }
    }
}

class Image_TransformTest extends PHPUnit_TestCase
{
    /**
     * To hold the image transformer
     *
     * @var object
     * @access protected
     **/
    var $imager = null;

    /**
     * Prepend each result image file by this
     *
     * This is use to differentiate output from various drivers
     *
     * @var string
     * @access protected
     **/
    var $prepend = null;

    /**
     * List of image formats all drivers should support
     **/
    var $formats = array(
        'gif'  => 'image/gif',
        'jpeg' => 'image/jpeg',
        'bmp'  => 'image/bmp',
        'png'  => 'image/png',
        'wbmp' => 'image/vnd.wap.wbmp');

    /**
     * Map of image formats with IMAGETYPE_* constants
     **/
    var $formatIMAGETYPE = array(
        'gif'  => IMAGETYPE_GIF,
        'jpeg' => IMAGETYPE_JPEG,
        'bmp'  => IMAGETYPE_BMP,
        'png'  => IMAGETYPE_PNG,
        'wbmp' => IMAGETYPE_WBMP);


    /**
     * Whether the constructor worked
     *
     * @var boolean
     **/
    var $valid = false;

    /**
     * Constructor
     *
     * @var string $name
     **/
    function __construct($name)
    {
        $this->PHPUnit_TestCase($name);
    }

    function setUp()
    {
        error_reporting(E_ALL);
        // Save result images in the driver's temp folder
        $this->prepend = $this->driver . DIRECTORY_SEPARATOR;
        $this->imager =& Image_Transform::factory($this->driver);
        $this->valid = (bool) !PEAR::isError($this->imager);
    }

    /**
     * Tests the image formats supported
     *
     * To ensure the driver supports the minimum basic image formats
     */
    function testSupportLoadingBasicImageFormats()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        $result = array();
        foreach ($this->formats as $format => $mime) {
            $file = 'imageinfo_96x32.' . $format;
            if (true === ($r = $this->imager->load(TEST_IMAGE_DIR . $file))) {
                $result[] = $format;
            }
        }
        return $this->assertEquals(array_keys($this->formats), $result);
    }

    /**
     * Tests the crop() method
     */
    function testCropWithXYPositioning()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Crop With XY Positioning',
            'crop_111x111-at-30x30.png', 'crop.png');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'crop.png'))
                  && (true === $this->imager->crop(111, 111, 30, 30))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend . 'crop_111x111-at-30x30.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the crop() method
     */
    function testCropUsingDefault0x0XYPositioning()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Crop Using Default 0x0 XY Positioning',
            'crop_32x32-at-0x0.png', 'crop.png');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'crop.png'))
                  && (true === $this->imager->crop(31, 31))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'crop_32x32-at-0x0.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the mirror() method
     */
    function testMirrorLeftRight()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
        Image_TransformTestHelper::log('Mirror Left Right', 'mirror.png',
            'alpha.png');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'alpha.png'))
                  && (true === $this->imager->mirror())
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'mirror.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the flip() method
     */
    function testFlipTopBottom()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Flip Top Bottom', 'flip.png',
            'alpha.png');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'alpha.png'))
                  && (true === $this->imager->flip())
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'flip.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the flip() method with alpha-channel
     */
    function testFlipTopBottomWithAlphaChannel()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Flip Top Bottom With Alpha Channel',
            'flip-alpha.png', 'alpha-gradient.png');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR
                                    . 'alpha-gradient.png'))
                  && (true === $this->imager->flip())
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'flip-alpha.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the greyscale() method
     */
    function testGreyscale()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Greyscale', 'greyscale.png',
            'plasma.png');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'plasma.png'))
                  && (true === $this->imager->greyscale())
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'greyscale.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the fit() method
     *
     */
    function testFit150x200px()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Fit into 150x200px box',
            'fit150x200px.jpg', 'mixed.jpg');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'mixed.jpg'))
                  && (true === $this->imager->fit(150, 200))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'fit150x200px.jpg', 'jpeg'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the fit() method
     *
     */
    function testFit200x100px()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Fit into 200x100px box',
            'fit200x100px.jpg', 'mixed.jpg');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'mixed.jpg'))
                  && (true === $this->imager->fit(200, 100))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'fit200x100px.jpg', 'jpeg'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the rotate() method
     *
     * Simple test
     */
    function testRotation90()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Rotation 90°', 'rotate90.png',
            'mirror-flip.png');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'mirror-flip.png'))
                  && (true === $this->imager->rotate(90))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'rotate90.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the rotation() method
     *
     * Rotated image reveals background. This test is to make sure
     * proper color background is used.
     */
    function testRotation120WithBlueBackground()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Rotation 120° With Blue Background',
            'rotate120.png', 'mirror-flip.png');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'mirror-flip.png'))
                  && (true === $this->imager->rotate(120, array('canvasColor' => '#0000FF')))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'rotate120.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the scaleByX() method
     */
    function testScaleByXTo200px()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Scale by X to 200px',
            'scaleByXTo200px.jpg', 'mixed.jpg');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'mixed.jpg'))
                  && (true === $this->imager->scaleByX(200))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'scaleByXTo200px.jpg', 'jpeg'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the resize() method
     *
     * Tests the ability to resize an image without keeping width/height
     * proportions.
     */
    function testResizeTo150x150px()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Resize to 150x150 px',
            'resizeTo150x150px.jpg', 'mixed.jpg');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'mixed.jpg'))
                  && (true === $this->imager->resize(150, 150))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'resizeTo150x150px.jpg', 'jpeg'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the resize() method
     *
     * Tests the ability to resize doing pixel replication instead of
     * interpolation
     */
    function testResizePixel()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Resize by pixel',
            'resizePixel.png', 'resizePixel.png');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR
                                . 'resizePixel.png'))
                  && (true === $this->imager->resize(200, 100,
                                        array('scaleMethod' => 'pixel')))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'resizePixel.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the scaleByY() method
     */
    function testScaleByYTo112px()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Scale by Y to 112px',
            'scaleByYTo112px.jpg', 'mixed.jpg');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'mixed.jpg'))
                  && (true === $this->imager->scaleByY(112))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'scaleByYTo112px.jpg', 'jpeg'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the scaleByFactor() method
     */
    function testScaleByFactor0_33()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Scale by Factor 0.33',
            'scaleByFactor0_33.jpg', 'mixed.jpg');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'mixed.jpg'))
                  && (true === $this->imager->scaleByFactor(0.33))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'scaleByFactor0_33.jpg', 'jpeg'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the scale() method using a "x%" input format
     */
    function testScaleByPercentageString()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Scale by Percentage "31.5%"',
            'scalePct31_5s.jpg', 'mixed.jpg');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'mixed.jpg'))
                  && (true === $this->imager->resize('31.5%', '31.5%'))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'scalePct31_5s.jpg', 'jpeg'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Tests the scaleByPercentage() method
     */
    function testScaleByPercentage()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        Image_TransformTestHelper::log('Scale by Percentage 31.5',
            'scaleByPct31_5.jpg', 'mixed.jpg');
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'mixed.jpg'))
                  && (true === $this->imager->scaleByPercentage(31.5))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'scaleByPct31_5.jpg', 'jpeg'));
        return $this->assertEquals(true, $result);
    }

    /**
     * Helper method to some other tests
     *
     * @param string $name method name to call
     * @param string $format image format extension name
     * @return bool|PEAR_Error TRUE on success, PEAR_Error object on error
     */
    function _CallMethod($name, $format)
    {
        $file = 'imageinfo_96x32.' . $format;
        if (true === $this->imager->load(TEST_IMAGE_DIR . $file)) {
            return $this->imager->$name();
        }
        return false;
    }


    /**
     * Tests the getImageType() method
     *
     * To ensure the driver and understand basic image formats
     */
    function testGetImageType()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        $expected = array();
        $result = array();
        foreach ($this->formats as $format => $mime) {
            $expected[$format] = $format;
            $result[$format] = $this->_CallMethod('getImageType', $format);
        }
        return $this->assertEquals($expected, $result);
    }

    /**
     * Tests the getMimeType() method
     */
    function testGetMimeType()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        foreach ($this->formats as $format => $mime) {
            $result[$format] = $this->_CallMethod('getMimeType', $format);
        }
        return $this->assertEquals($this->formats, $result);
    }

    /**
     * Tests the getImageWidth() method
     */
    function testGetImageWidth()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        $expected = array();
        $result = array();
        foreach ($this->formats as $format => $mime) {
            $expected[$format] = 96;
            $result[$format] = $this->_CallMethod('getImageWidth', $format);
        }
        return $this->assertEquals($expected, $result);
    }

    /**
     * Tests the getImageHeight() method
     */
    function testGetImageHeight()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        $expected = array();
        $result = array();
        foreach ($this->formats as $format => $mime) {
            $expected[$format] = 32;
            $result[$format] = $this->_CallMethod('getImageHeight', $format);
        }
        return $this->assertEquals($expected, $result);
    }

    /**
     * Tests the getImageSize() method
     */
    function testGetImageSize()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        $expected = array();
        $result = array();
        foreach ($this->formats as $format => $mime) {
            $expected[$format] = array(
                96,
                32,
                $this->formatIMAGETYPE[$format],
                'height="32" width="96"',
                'mime' => $mime);
            $result[$format] = $this->_CallMethod('getImageSize', $format);
        }
        return $this->assertEquals($expected, $result);
    }

    /**
     * Tests the addText() method
     *
    function testAddText()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        return $this->assertTrue(false, 'Test implemented?');
    }

    /**
     * Tests the gamma() method
     *
    function testGamma()
    {
        if (!$this->valid) {
            return $this->assertFalse(true, 'Class constructor failed.');
        }
        return $this->assertTrue(false, 'Test implemented?');
    }
    */
}

?>