<?php

// Unit tests for Image_Transform package - IM driver

class Image_TransformTestIM extends Image_TransformTest
{
    /**
     * Constructor
     *
     * @see __construct()
     **/
    function Image_TransformTestIM($name)
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
        $this->prepend = 'IM' . DIRECTORY_SEPARATOR;
        $this->imager =& Image_Transform::factory('IM');
        if (!PEAR::isError($this->imager)) {
            $this->valid = true;
        }
    }
}

?>