<?php

namespace Bame\StaticMap;

class StaticMap extends StaticMapLite
{
    public function disableMapCache()
    {
        $this->useMapCache = false;
    }

    public function disableTileCache()
    {
        $this->useTileCache = false;
    }

    public function disableCopyright()
    {
        $this->osmLogo = false;
    }

    public function setCustomTileSrc($url)
    {
        $this->tileSrcUrl['custom'] = $url;
        $this->maptype = 'custom';
    }

    public function getImage()
    {
        $this->makeMap();
        return $this->image;
    }

    public function setParams($lat, $lon, $zoom, $width, $height)
    {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->zoom = $zoom;
        $this->width = $width;
        $this->height = $height;
    }
}
