<?php

// Unit tests for Image_Transform package - GD driver

class Image_TransformTestGD extends Image_TransformTest
{
    /**
     * Constructor
     *
     * @see __construct()
     **/
    function Image_TransformTestGD($name)
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
        $this->prepend = 'GD' . DIRECTORY_SEPARATOR;
        $this->imager =& Image_Transform::factory('GD');
        if (PEAR::isError($this->imager)) {
            print_r($this->imager);
        }
    }
}

?>