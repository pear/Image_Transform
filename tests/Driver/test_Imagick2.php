<?php

// Unit tests for Image_Transform package - Imagick2 driver

class Image_TransformTestImagick2 extends Image_TransformTest
{
    /**
     * Constructor
     *
     * @see __construct()
     **/
    function Image_TransformTestImagick2($name)
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

    function setUp()
    {
        $this->prepend = 'Imagick2' . DIRECTORY_SEPARATOR;
        $this->imager =& Image_Transform::factory('Imagick2');
        if (PEAR::isError($this->imager)) {
            print_r($this->imager);
        }
    }
}

?>