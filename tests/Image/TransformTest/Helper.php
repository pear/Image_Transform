<?php
/**
* Helper functions
*
* @author Christian Weiske <cweiske@php.net>
*/
class Image_TransformTest_Helper
{
    public static function exactlySameFile($filename1, $filename2)
    {
        return self::nearlySameFile($filename1, $filename2, 0);
    }//public static function exactlySameFile($filename1, $filename2)



    public static function nearlySameFile($filename1, $filename2, $nMaxAverageDiff)
    {
        if (!file_exists($filename1)) {
            throw new Exception('File 1 does not exist: ' . $filename1);
        }
        if (!file_exists($filename2)) {
            throw new Exception('File 2 does not exist: ' . $filename2);
        }

        return self::nearlySame(
            file_get_contents($filename1),
            file_get_contents($filename2),
            $nMaxAverageDiff
        );
    }//public static function nearlySameFile($filename1, $filename2, $nMaxAverageDiff)



    /**
    * Checks if two images contain the same image, pixel by pixel
    *
    * @param mixed $file1 GD image resource OR content as string
    * @param mixed $file2 GD image resource OR content as string
    *
    * @return boolean true if they are the same, false if not
    */
    public static function exactlySame($file1, $file2)
    {
        return self::nearlySame($file1, $file2, 0);
    }//public static function exactlySame($file1, $file2)


    /**
    * Checks if two images contain the same image, pixel by pixel
    *
    * @param mixed $file1           GD image resource OR content as string
    * @param mixed $file2           GD image resource OR content as string
    * @param int   $nMaxAverageDiff Maximum rgb-average difference to the given color
    *
    * @return boolean true if they are the same, false if not
    */
    public static function nearlySame($file1, $file2, $nMaxAverageDiff)
    {
        //echo $file1 . ' - ' . $file2 . "\n";
        if (is_string($file1)) {

            $i1 = imagecreatefromstring($file1);
            if ($i1 === false) {
                throw new Exception('Image 1 could no be opened' . $file1);
            }
        } else {
            $i1 = $file1;
        }

        if (is_string($file2)) {
            $i2 = imagecreatefromstring($file2);
            if ($i2 === false) {
                throw new Exception('Image 2 could no be opened' . $file2);
            }
        } else {
            $i2 = $file2;
        }

        $sx1 = imagesx($i1);
        $sy1 = imagesy($i1);
        if ($sx1 != imagesx($i2) || $sy1 != imagesy($i2)) {
            //image size does not match
            return false;
        }

        $bOk      = true;
        $nMaxDiff = 0;

        for ($x = 0; $x < $sx1; $x++) {
        for ($y = 0; $y < $sy1; $y++) {

            $rgb1  = imagecolorat($i1, $x, $y);
            $pix1a = array(
                'r' => ($rgb1 >> 16) & 0xFF,
                'g' => ($rgb1 >> 8) & 0xFF,
                'b' =>  $rgb1 & 0xFF
            );
            $pix1  = imagecolorsforindex($i1, $rgb1);

            $rgb2  = imagecolorat($i2, $x, $y);
            $pix2a = array(
                'r' => ($rgb2 >> 16) & 0xFF,
                'g' => ($rgb2 >> 8) & 0xFF,
                'b' =>  $rgb2 & 0xFF
            );
            $pix2  = imagecolorsforindex($i2, $rgb2);

            //echo implode(',',$pix1) . ' - ' . implode(',',$pix2) . "\n";
            if ($pix1 != $pix2) {
                $bOk = false;
                if ($nMaxAverageDiff == 0) {
                    break 2;
                } else {
                    $nThisDiff = array_sum(
                        array(
                            abs($pix1a['r'] - $pix2a['r']),
                            abs($pix1a['g'] - $pix2a['g']),
                            abs($pix1a['b'] - $pix2a['b'])
                        )
                    ) / 3;
                    if ($nThisDiff > $nMaxDiff) {
                        $nMaxDiff = $nThisDiff;
                    }
                }
            }
        }
        }

        imagedestroy($i1);
        imagedestroy($i2);

        if ($bOk || $nMaxAverageDiff == 0) {
            return $bOk;
        } else {
            return $nMaxDiff <= $nMaxAverageDiff;
        }
    }//function nearlySame($file1, $file2)

}//class Image_TransformTest_Helper
?>