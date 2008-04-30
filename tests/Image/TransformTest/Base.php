<?php
require_once 'PHPUnit/Framework.php';

/**
 * Base class for image transform driver tests
 *
 * @author Christian Weiske <cweiske@php.net>
 */
abstract class Image_TransformTest_Base extends PHPUnit_Framework_TestCase
{
    /**
     * @var Image_Transform
     */
    protected $it;

    /**
    * Partial name of the driver (e.g. GD)
    * @var string
    */
    protected $strDriverPart = null;

    /**
    * Location of test images
    */
    protected $strImageDir = null;

    /**
    * Array of temporary files that should be deleted
    * when the test is done.
    *
    * @var array
    */
    protected $arTmpFiles = array();

    /**
    * Maximum average difference to the template.
    * NULL to deactivate and require an exact image.
    *
    * This variable is reset on every setUp().
    *
    * @var int
    */
    protected $nMaxAverageDiff = null;



    public function __construct()
    {
        $strClass            = get_class($this);
        $this->strDriverPart = substr($strClass, strrpos($strClass, '_') + 1);

        chdir(dirname(__FILE__) . '/../../');
        require_once 'Image/TransformTest/Helper.php';

        chdir(dirname(__FILE__) . '/../../../');
        //var_dump(getcwd());die();
        require_once 'Image/Transform.php';

        $this->strImageDir = dirname(__FILE__) . '/../../images/';
    }



    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function mainImpl($strClass)
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite($strClass);
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }



    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->nMaxAverageDiff = null;

        $this->it = Image_Transform::factory($this->strDriverPart);

        $bError = PEAR::isError($this->it);
        if ($bError) {
            if ($this->it->getCode() == IMAGE_TRANSFORM_ERROR_UNSUPPORTED) {
                $this->markTestSkipped($this->it->getMessage());
            } else {
                $this->fail($this->it->getMessage(), $this->it->getCode());
            }
        }
    }//protected function setUp()



    /**
     * Removes temporary files
     *
     * @return void
     */
    protected function tearDown()
    {
//return;
        //delete tmp files
        if (count($this->arTmpFiles) > 0) {
            foreach ($this->arTmpFiles as $strFile) {
                if (!unlink($strFile)) {
                    $this->fail('Could not delete temporary file: ' . $strFile);
                }
            }
            $this->arTmpFiles = array();
        }
    }//protected function tearDown()



    protected function getTmpFilename()
    {
        $strFile = tempnam('/tmp/', __CLASS__ . '-img-');
        $this->arTmpFiles[] = $strFile;
        return $strFile;
    }//protected function getTmpFilename()



    /**
     * Resize image from 4x4 to 2x2
     *
     * @return void
     */
    public function testResize()
    {
        $strTmp = $this->getTmpFilename();
        $this->assertTrue($this->it->load($this->strImageDir . 'base_4x4.png'));
        $this->assertTrue($this->it->resize(2, 2));
        $this->assertTrue($this->it->save($strTmp));
        $this->assertExactlySameImage(
            $this->strImageDir . 'base_4x4-resized-2x2.png',
            $strTmp
        );
    }//public function testResize()



    /**
     * @todo Implement testScaleByX().
     */
    public function testScaleByX() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testScaleByXY().
     */
    public function testScaleByXY() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testScaleByY().
     */
    public function testScaleByY() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testScale().
     */
    public function testScale() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testScaleByPercentage().
     */
    public function testScaleByPercentage() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testScaleByFactor().
     */
    public function testScaleByFactor() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testScaleMaxLength().
     */
    public function testScaleMaxLength() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testScaleByLength().
     */
    public function testScaleByLength() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testFit().
     */
    public function testFit() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testFitOnCanvas().
     */
    public function testFitOnCanvas() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testFitX().
     */
    public function testFitX() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testFitY().
     */
    public function testFitY() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetHandle().
     */
    public function testGetHandle() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testNormalize().
     */
    public function testNormalize() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testLoad().
     */
    public function testLoad() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testDisplay().
     */
    public function testDisplay() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testSave().
     */
    public function testSave() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testFree().
     */
    public function testFree() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAddText().
     */
    public function testAddText() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAddDropShadow().
     */
    public function testAddDropShadow() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAddBorder().
     */
    public function testAddBorder() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCrop().
     */
    public function testCrop() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCanvasResize().
     */
    public function testCanvasResize() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGamma().
     */
    public function testGamma() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testRotate().
     */
    public function testRotate() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * Test horizontal mirroring
     * 123 -> 321
     *
     * @return void
     */
    public function testMirror()
    {
        $strTmp = $this->getTmpFilename();
        $this->it->load($this->strImageDir . 'base_4x4.png');
        $this->it->mirror();
        $this->it->save($strTmp);
        $this->assertExactlySameImage(
            $this->strImageDir . 'base_4x4-mirror.png',
            $strTmp
        );
    }//public function testMirror()



    /**
     * Test vertically flipping the image
     * v -> ^
     *
     * @return void
     */
    public function testFlip()
    {
        $strTmp = $this->getTmpFilename();
        $this->it->load($this->strImageDir . 'base_4x4.png');
        $this->it->flip();
        $this->it->save($strTmp);
        $this->assertExactlySameImage(
            $this->strImageDir . 'base_4x4-flip.png',
            $strTmp
        );
    }//public function testFlip()



    /**
     * @todo Implement testGreyscale().
     */
    public function testGreyscale() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGrayscale().
     */
    public function testGrayscale() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }



    protected function assertExactlySameImage($filename1, $filename2)
    {
        if ($this->nMaxAverageDiff === null) {
            $this->assertTrue(
                Image_TransformTest_Helper::exactlySameFile(
                    $filename1, $filename2
                ),
                'Images are not the same (' . $filename1 . ' and ' . $filename2 . ')'
            );
        } else {
            $this->assertTrue(
                Image_TransformTest_Helper::nearlySameFile(
                    $filename1, $filename2, $this->nMaxAverageDiff
                ),
                'Images are not the same (' . $filename1 . ' and ' . $filename2 . ')'
            );
        }
    }//protected function assertExactlySameImage(..)
}
?>