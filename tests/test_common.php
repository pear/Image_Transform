<?php

// Unit tests for Image_Transform package
//
// Note: It is rather difficult to test such a package since it manipulates
//       images. Automation is limited, and manual/visual checks are required.

class Image_TransformTest extends PHPUnit_TestCase
{
    /**
     * To hold the image transformer
     *
     * @access protected
     * @var object
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

    function testSupportLoadingBasicImageFormats()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
        $result = array();
        foreach ($this->formats as $format => $mime) {
            $file = 'imageinfo_96x32.' . $format;
            if (true === ($r = $this->imager->load(TEST_IMAGE_DIR . $file))) {
                $result[] = $format;
            } else {
                print_r($r);
            }
        }
        return $this->assertEquals(array_keys($this->formats), $result);
    }

    function testCropWithXYPositioning()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'crop.png'))
                  && (true === $this->imager->crop(111, 111, 30, 30))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend . 'crop_111x111-at-30x30.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    function testCropUsingDefault0x0XYPositioning()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'crop.png'))
                  && (true === $this->imager->crop(32, 32))
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'crop_32x32-at-0x0.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    function testResize()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
    }

    function testMirror()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'mirror-flip.png'))
                  && (true === $this->imager->mirror())
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'mirror.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    function testFlip()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'mirror-flip.png'))
                  && (true === $this->imager->flip())
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'flip.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    function testGamma()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
    }

    function testGreyscale()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
        $result = (true === $this->imager->load(TEST_IMAGE_DIR . 'plasma.png'))
                  && (true === $this->imager->greyscale())
                  && (true === $this->imager->save(TEST_TMP_DIR
                      . $this->prepend  . 'greyscale.png', 'png'));
        return $this->assertEquals(true, $result);
    }

    function testRotation90()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
    }

    function testRotation120()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
    }

    function testScaleByX()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
    }

    function testScaleByY()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
    }

    function testScaleByFactor()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
    }

    function testScaleByPercentageString()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
    }

    function testScaleByPercentage()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
    }

    function testScaleByFactor2()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
    }

    function testScaleByPercentage2()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
    }

    function _CallMethod($name, $format)
    {
        $file = 'imageinfo_96x32.' . $format;
        if (true === $this->imager->load(TEST_IMAGE_DIR . $file)) {
            return $this->imager->$name();
        }
        return false;
    }


    function testGetImageType()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
        $expected = array();
        $result = array();
        foreach ($this->formats as $format => $mime) {
            $expected[$format] = $format;
            $result[$format] = $this->_CallMethod('getImageType', $format);
        }
        return $this->assertEquals($expected, $result);
    }

    function testGetMimeType()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
        foreach ($this->formats as $format => $mime) {
            $result[$format] = $this->_CallMethod('getMimeType', $format);
        }
        return $this->assertEquals($this->formats, $result);
    }

    function testGetImageWidth()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
        $expected = array();
        $result = array();
        foreach ($this->formats as $format => $mime) {
            $expected[$format] = 96;
            $result[$format] = $this->_CallMethod('getImageWidth', $format);
        }
        return $this->assertEquals($expected, $result);
    }

    function testGetImageHeight()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
        $expected = array();
        $result = array();
        foreach ($this->formats as $format => $mime) {
            $expected[$format] = 32;
            $result[$format] = $this->_CallMethod('getImageHeight', $format);
        }
        return $this->assertEquals($expected, $result);
    }

    function testGetImageSize()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
        $expected = array();
        $result = array();
        foreach ($this->formats as $format => $mime) {
            $expected[$format] = array(
                96,
                32,
                1,
                'height="32" width="96"',
                'mime' => $mime);
            $result[$format] = $this->_CallMethod('getImageSize', $format);
        }
        return $this->assertEquals($expected, $result);
    }

    function testAddText()
    {
        if (!$this->valid) {
            return $this->assertTrue(false, 'Have valid "imager" object to work with? ');
        }
    }

}


?>