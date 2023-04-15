# StaticMap / staticMapLite

PHP library for generating static map images.

Originally from [dfacts/staticmaplite](https://github.com/dfacts/staticmaplite)

Some information can be found here: [https://wiki.openstreetmap.org/wiki/StaticMapLite](https://wiki.openstreetmap.org/wiki/StaticMapLite)

## Changes from original staticMapLite

staticMapLite was only slightly modified to be reusable, it should be mostly backwards compatible and the staticmap.php
script uses this class to provide the same functionality

## Installation

```bash
composer require bame/staticmap
```

## Additional features

### StaticMap

The new class StaticMap is added to be able to reuse the code modularily. It also allows to add a custom tile source
and disabling the cache.

Disable to tile cache only, if you have permission from the tile server administrator. 

### TripleZoomMap

Generates map around a POI with 3 zoom levels:

```
+------------++-------------------------+
|            ||                         |
|  overview  ||  detail                 |
|            ||                         |
|            ||                         |
+------------+|       (My Place)        |
+------------+|            v            |
|            ||                         |
|  closer    ||                         |
|            ||                         |
|            ||                         |
+------------++-------------------------+
```

Each frame has a different color and the area of the previous zoom level is marked.

Example call:

```php
$colors = [
    [0x00, 0x6B, 0x3F],
    [0xF9, 0xDD, 0x16],
    [0xE2, 0x3D, 0x28],
];

$map = new TripleZoomMap(8.977596, 38.76179, 700, 320, 'Bandira Addis Map', $colors, 'https://a.africa.tiles.openplaceguide.org/styles/bright/{Z}/{X}/{Y}.png', 'opg-pages');
$map->sendHeader();
return imagepng($map->getImage());
```
