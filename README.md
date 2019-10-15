# Levi assets
This packages offers a toolkit to manage user generated assets, such as file uploads or any other
third party content in Laravel.

## Installation

You can install the package via composer:

    composer require mehr-it/levi-assets
    
Laravel's package auto detection will automatically load the service provider of the package.


## Usage
The package offers many helpful tools when working with assets. You can use them standalone or
indirectly when using the assets manager.

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


