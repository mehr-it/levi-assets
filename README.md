# Levi assets
[![Latest Version on Packagist](https://img.shields.io/packagist/v/mehr-it/levi-assets.svg?style=flat-square)](https://packagist.org/packages/mehr-it/levi-assets)

This packages offers a toolkit to manage user assets, such as file uploads or any other
third party content in Laravel.

## Installation

You can install the package via composer:

    composer require mehr-it/levi-assets
    
Laravel's package auto detection will automatically load the service provider and the `Assets` facade
of the package.


## Usage
The package offers many helpful tools when working with assets. You can use them standalone or
indirectly when using the assets manager.

Image processing depends on [mehr-it/levi-images](https://github.com/mehr-it/levi-images). Please
read the documentation of that package for image processing details and configuration.

### The assets manager
The assets manager is the central hub for managing user assets. Assets are grouped into
collections. This allows to configure different behaviour multiple asset groups.

#### Configuration
Following example config defines a product image collection:

```php
// config/leviAssets.php

return [
    'collections' => [
        'productImages' => [
            'storage'        => 'local',
            'storage_path'   => 'products/images',
            'public_storage' => 's3',
            'public_path'    => 'static/pi',
            'virus_scan'     => true,
            'build' => [
                'small_jpg' => [
                    'imgMaxSize:100,75',
                    'imgFormat:jpg',
                    'imgOptimize',
                ],
                'small_webp' => [
                    'imgMaxSize:100,75',
                    'imgFormat:webp',
                ],
                'large_jpg' => [
                    'imgMaxSize:800,600',
                    'imgFormat:jpg',
                    'imgOptimize',
                ],
                'large_webp' => [
                    'imgMaxSize:800,600',
                    'imgFormat:webp',
                ],
            ],
            'link_filters' => [
                'webp:small_webp,large_webp'
            ]
        ],
    ],
    'builders' => [
        'imgMaxSize'  => ImageMaxSizeBuilder::class,
        'imgFormat'   => ImageFormatBuilder::class,
        'imgOptimize' => OptimizeImageBuilder::class,
    ]
]
```    
    
For each assets collection two disks are configured. The storage disk where the original assets are 
stored and a the public storage where published asset builts are written to, so that they are
public available.

The `build` option holds configurations for public asset builts. The array key is used as built name
and the value holds an array of all builders to apply to the original asset to generate the public
built. Eg. the string `'imgMaxSize:100,75'` adds the `'imgMaxSize'` builder to the built pipeline with  `100` 
and `70` as arguments.

The `virus_scan` allows to disable virus scanning of added assets, which is enabled by default.

The `link_filters` option defines common link processing filters when generating asset links. See 
[mehr-it/levi-assets-linker](https://github.com/mehr-it/levi-assets-linker) for more information
about the usage and built-in link filters.

The builder classes and their names are defined with the help of the `builders` array. By default
a plain copy built named `'_'` is added to the build config. It can be disabled by passing
`['_' => false]`

##### Virtual asset builds
Sometimes you have to perform the same operations for multiple asset builds. Imagine
an image to be resized and then be output in different formats. Instead of resizing 
the image multiple times, you can define a "virtual" build which computes
the resized image. This virtual build than can be used as input for other
builds. See following example config:

```php
// config/leviAssets.php

return [
    'collections' => [
        'productImages' => [
            /*
             * ...
             */
            'build' => [
                ':small' => [
                     'imgMaxSize:100,75',
                ],
                'small_jpg:small' => [
                    'imgFormat:jpg',
                ],
                'small_webp:small' => [
                    'imgFormat:webp',
                ],
            ],
        ],
    ],
]
```

The `:small` build defines a virtual build by starting with a colon. It performs
the resizing task but is never output anywhere.

The `small_jpg` and `small_webp` builds take the virtual build as input
and apply format conversion. This is done by appending `":small"` to the
build name.

This way, resizing is only done once.

#### Asset builders
Asset builders allow to apply any sort of modification to generate a public file version from
the given asset. Common use cases are image manipulation and optimization or adding meta data.

Asset builders can modify the file contents, the file path and the options passed to the storage
disk when writing the public asset file.
 
Asset builders must implement the `AssetBuilder` interface. The `AbstractAssetBuilder` class is
a good starting point for implementing custom asset builders. Asset builders must be registered
with a name, before using them. The configuration file can be used for this as described above.

##### Built-in asset builders
This package ships with a set of widely used asset builders:

###### Cache-Control (cache)
Allows to define a `Cache-Control` meta value. Eg, `'cache:max-age=3600,public'`

###### Content-Disposition (disposition)
Allows to define a `Content-Disposition` meta value. Eg, `'disposition:attachment'`

###### Content-Encoding (encoding)
Allows to define a `Content-Encoding` meta value. Eg, `'encoding:gzip'`

###### Content-Language (language)
Allows to define a `Content-Language` meta value. Eg, `'language:de-DE,en-CA'`

###### Content-Type (mime)
Sets the `Content-Type` meta value. The content type can be explicitly passed as arguments: 
`mime:text/plain,charset=utf-8`. If no arguments, the file content is evaluated using `finfo`
and if a content type was detected it will be used. If content type detection fails
or results in "application/octet-stream", the `Content-Type` meta value is not set.

###### Expires (expires)
Allows to define an `Expires` meta value. Eg, `'expires:' . (time() + 60 * 60)`. Timestamps, and date
objects as arguments are automatically converted to a HTTP date string.

###### S3-Options (s3)
Converts the meta value to a S3 compatible config array to be passed to
S3 when uploading objects. This builder is highly recommended when using S3 storage. Otherwise, any
meta data is lost. 

###### ImageAutoCrop
Automatically crops image background pixels from the edges. Eg: `imgAutoCrop:ffffff,0.01` removes
all white from the edges with a tolerance of 1%. Default options are `ffffff` and `0.04`.

###### ImageCanvas
Paints a canvas with given color and margin in the background of the image. Eg: `imgCanvas:ffffff,10` 
creates a white background and adds 10 pixels to each edge of the image. Default options are
`ffffff` and `0` (no margin).

###### ImageFormat
Sets the output format for an image. Eg. `imgFormat:jpg,quality=90` sets the output format to Jpeg with
quality 90. Format options are optional and vary by format. Common output formats are
`png` (default), `jpg`, `gif` and `webp`.

###### ImageGrayscale
Converts images to grayscale colorspace. This builder has no arguments: `imgGrayscale`.

###### ImageGrayscaleMaxBlack
Converts images to grayscale colorspace and stretches the image histogram so that the
darkest color becomes black. This builder has no arguments: `imgGrayscaleMaxBlack`

###### ImageMaxSize
Resizes images to a maximum size if they exceed it. Eg, `imgMaxSize:200,300,cubic` enforces
a maximum size of 200x300 pixels and uses a "cubic" filter for interpolation. The filter option
is optional but width and height are required. Some valid filter values can be found in 
`Imagine\Image\ImageInterface` but may vary by driver.

###### ImageMinSize
Resizes images to a minimum size if any edge is less than the minimum size. Eg, `imgMinSize:100,50,cubic`
enforces a minimum width of 100 pixels **and** a minimum height of 50 pixels. It uses a "cubic" filter for 
interpolation. The filter option is optional but width and height are required. Some valid filter values
can be found in `Imagine\Image\ImageInterface` but may vary by driver.

###### ImageMinEdge
Resizes images to fit the minimum size for at least one edge. Eg, `imgMinEdge:100,50,cubic`
enforces a minimum width of 100 pixels **or** a minimum height of 50 pixels. It uses a "cubic" filter for interpolation. The filter option is optional but width and height are required. Some valid filter values can be found in `Imagine\Image\ImageInterface` but may vary by driver.

###### ImageOptimize
Passes the image to the formats corresponding image optimizer. This builder has not options: `imgOptimize`.
The corresponding  optimizer library has to be installed. See [mehr-it/levi-images](https://github.com/mehr-it/levi-images)
for details.

###### ImagePalette
Converts the image to the given palette, if required. Eg. `imgPalette:rgb` converts to RGB
palette. Other options are `grayscale` and `cmyk`, if supported by the
image driver.

#### Resolving and linking assets
When generating a page, the URIs for the corresponding assets have to be generated. This task
is split up into two steps. **Resolving** and **linking**: One advantage of this concept is, that
an lightwight external page generator (not loading a whole PHP framework) can be used to generate 
pages very fast. It only needs the persisted resolved path array to generate request based asset
links. See [mehr-it/levi-assets-linker](https://github.com/mehr-it/levi-assets-linker) for more 
details.

First all paths to public builts of a given asset are **resolved**. The result is an array with
the public path for each built (the built names as key). For the product image example from the
config above, the resolved path array could look like this:

    [
        'small_jpg'  => 'static/pi/small_jpg/GiftCardRed.jpg',
        'small_webp' => 'static/pi/small_webp/GiftCardRed.webp',
        'large_jpg'  => 'static/pi/large_jpg/GiftCardRed.jpg',
        'large_webp' => 'static/pi/large_webp/GiftCardRed.webp',
    ]

The **linker** takes this path array, picks the most suitable path and generates a link for it.
Without any link filters, this is always the first path. **So the order in which the builts are
defined also defines a precedence.**

However, link filters can modify the paths array (eg. remove paths or reorder the array). Since
path filters are applied at site generation time, they can eg. react to request headers. One example
is the `webp` filter which sets a higher priority for WebP builts, when the browser indicates
WebP support.

Link filters can also modify the generated URL, such as eg. replacing the host or ensuring `'https'`
as protocol.

Link filters can be specified per collection in the config file or be passed as argument to the
link method:

    Assets::link('productImages', 'GiftCardRed.jpg', [], 'proto:https|host:example.com');
    
For images, typically multiple thumbs with different sizes are generated. The third parameter
allows to define built filters, to preselect the corresponding builts before passing the path
array to the linker:

     Assets::link('productImages', 'GiftCardRed.jpg', 'small_jpg,small_web_p', 'proto:https');
     
Instead of listing all builts, the `'pfx'` and '`sfx`' filters can be used to filter the built
name by prefix or suffix':

    Assets::link('productImages', 'GiftCardRed.jpg', 'pfx:small_', 'proto:https');
    
Callables and multiple filters are also accepted as path filters. Their syntax is very similar to
the link filters.


#### Blade directive

If using assets in your blade template, the `@assetLink` directive points to the `Assets::link()` 
method:

    @assetLink('productImages', 'GiftCardRed.jpg', [], 'proto:https');


### Virus scan
The virus scan requires ClamAV (with daemon) to be installed. You can install it as follows:
    
    apt-get install clamav clamav-daemon
    
Scanning files is very simple:

    $scanner = app(VirusScanner::class);
    try {
        $scanner->scanFile($file);
    }
    catch(VirusDetectedException $ex) {
        // malicious content was detected
    }    
    catch(VirusScanException $ex) {
        // an error occured on file scanning
    }
    
    
#### Configuring the virus scan
If your ClamAV is using another socket than `unix:///var/run/clamav/clamd.ctl`, you have to set
`ASSETS_VIRUS_SCAN_SOCKET` to the correct value.

If you want to globally disable virus scan, eg. in development environments, you can set
`ASSETS_VIRUS_SCAN_BYPASS` to `true`.


