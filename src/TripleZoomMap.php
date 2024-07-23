<?php

namespace Bame\StaticMap;

/**
 * Map with 3 zoom level to show the location of a point of interest at one glance
 */
class TripleZoomMap
{
    protected int $widthSmall;
    protected int $heightSmall;

    protected array $zoom = [10, 13, 16];

    public function __construct($lat, $lon, $totalWidth, $totalHeight, $colors, $tileSource, $applicationName)
    {
        $this->applicationName = $applicationName;
        $this->tileSource = $tileSource;

        $this->widthSmall = round($totalWidth / 3);
        $this->heightSmall = round($totalHeight / 2);
        $this->totalWidth = $totalWidth;
        $this->totalHeight = $totalHeight;
        $this->colors = $colors;

        $this->overview = $this->getMap($lat, $lon, $this->zoom[0], $this->widthSmall, $this->heightSmall);
        $this->overview->disableCopyright();
        $this->closer = $this->getMap($lat, $lon, $this->zoom[1], $this->widthSmall, $this->heightSmall);
        $this->closer->disableCopyright();
        $this->detail = $this->getMap($lat, $lon, $this->zoom[2], $this->widthSmall * 2, $this->heightSmall * 2);
    }

    protected function getMap($lat, $lon, $zoom, $width, $height)
    {
        $map = new StaticMap($this->applicationName);
        $map->setCustomTileSrc($this->tileSource);
        $map->disableMapCache();
// don't disable if using 3rd party tiles! see #1
        $map->disableTileCache();
        $map->setParams($lat, $lon, $zoom, $width, $height,);

        return $map;
    }

    public function sendHeader()
    {
        $this->overview->sendHeader();
    }

    public function addSignPost($text, $markerFile, $fontFile)
    {
        $this->text = $text;
        $this->markerFile = $markerFile;
        $this->fontFile = $fontFile;
    }

    public function getImage()
    {
        $image = imagecreatetruecolor($this->widthSmall * 3, $this->heightSmall * 2);

        $overviewImage = $this->overview->getImage();
        $closerImage = $this->closer->getImage();
        $detailImage = $this->detail->getImage();

        $this->addFrame($overviewImage, $this->colors[0]);
        $divisor = $this->pot2($this->zoom[1] - $this->zoom[0]);
        $this->addCenteredRectangle($overviewImage, $this->widthSmall / $divisor, $this->heightSmall / $divisor, $this->colors[1]);

        $this->addFrame($closerImage, $this->colors[1]);
        $divisor = $this->pot2($this->zoom[2] - $this->zoom[1]);
        $this->addCenteredRectangle($closerImage, $this->widthSmall / $divisor, $this->heightSmall / $divisor, $this->colors[2]);

        $this->addFrame($detailImage, $this->colors[2]);

        if (!empty($this->text)) {
            $this->renderSignPost($detailImage);
        }

        imagecopy($image, $overviewImage, 0, 0, 0, 0, $this->widthSmall, $this->heightSmall);
        imagecopy($image, $closerImage, 0, $this->heightSmall, 0, 0, $this->widthSmall, $this->heightSmall);
        imagecopy($image, $detailImage, $this->widthSmall, 0, 0, 0, $this->widthSmall * 2, $this->heightSmall * 2);

        return $image;
    }

    private function addFrame($image, $color)
    {
        imagesetthickness($image, 2);
        imagerectangle($image, 1, 1, imagesx($image) - 2, imagesy($image) - 2, $this->getColor($image, $color));
    }

    private function getColor($image, $color)
    {
        return imagecolorallocate($image, $color[0], $color[1], $color[2]);
    }

    /**
     * calculates 2^$exponent
     */
    private function pot2($exponent)
    {
        $factor = 1;
        for ($i = 0; $i < $exponent; $i++) {
            $factor *= 2;
        }
        return $factor;
    }

    private function addCenteredRectangle($image, $width, $height, $color)
    {
        imagesetthickness($image, 2);
        $sx = imagesx($image);
        $sy = imagesy($image);
        imagerectangle($image, ($sx - $width) / 2, ($sy - $height) / 2, ($sx - $width) / 2 + $width, ($sy - $height) / 2 + $height, $this->getColor($image, $color));
    }

    protected function renderSignPost($image)
    {
        $text = $this->text;

        $px = imagesx($image) / 2;
        $py = imagesy($image) / 2;

        imagealphablending($image, true);

        $fontsize = 9;
        $marker = imagecreatefrompng($this->markerFile);
        imagealphablending($marker, true);

        $bbox = imagettfbbox($fontsize, 0, $this->fontFile, $text);

        $textwidth = $bbox[2] - $bbox[0];
        $textheight = $bbox[7] - $bbox[1];

        // top left coordinate of the marker
        $mh = imagesy($marker);
        $half = ceil($textwidth / 2);
        $mx = $px - $half;
        $my = $py - $mh;

        // left
        $a = 0;
        $b = 4;
        imagecopy($image, $marker, $px - 3 - $half - 9, $my, $a, 0, 10, $mh);

        // from middle to left
        for ($i = 0; $i <= $half + 2; $i++) {
            $a = 9;
            imagecopy($image, $marker, $px - $i, $my, $a, 0, 1, $mh);
        }

        // center
        $centerWidth = 30;
        $offsetCenter = 31;
        imagecopy($image, $marker, $px - $centerWidth / 2, $py - $mh + $offsetCenter, 10, $offsetCenter, $centerWidth, $mh - $offsetCenter);

        // from middle to right
        for ($i = 0; $i <= $half + 2; $i++) {
            $a = 35;
            imagecopy($image, $marker, $px + $i, $my, $a, 0, 1, $mh);
        }

        // right
        $a = imagesx($marker) - 10;
        imagecopy($image, $marker, $px + 3 + $half, $my, $a, 0, 10, $mh);


        $black = imagecolorallocate($image, 255, 255, 255);
        imagettftext($image, $fontsize, 0, $mx + 1, $my + 20, $black, $this->fontFile, $text );

    }

}
