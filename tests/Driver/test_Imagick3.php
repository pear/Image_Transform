<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Unit tests for Image_Transform package - Imagick3 driver
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author     Philippe Jausions <Philippe.Jausions@11abacus.com>
 * @copyright  2007 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Image_Transform
 */
class Image_TransformTestImagick3 extends Image_TransformTest
{
    /**
     * Holds driver's name
     *
     * @var string $driver
     * @access protected
     */
    var $driver = 'Imagick3';

    /**
     * Constructor
     *
     * @see __construct()
     **/
    function Image_TransformTestImagick3($name)
    {
        $this->__construct($name);
    }

    /**
     * Constructor
     *
     * @var string $name
     **/
    function __construct($name)
    {
        parent::__construct($name);
    }
}

?>