<?php
require __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;

class SakolawpAttendanceAdmin
{
    public function __construct()
    {
        // add_shortcode('qr_scanner', 'display_qr_scanner');
    }

    function generate_qr_code($event_id)
    {
        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->data($event_id)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->size(300)
            ->margin(10)
            ->build();

        // Save the QR code image to a file
        $path = __DIR__ . '../public/qrcodes/' . $event_id . '.png';
        $qrCode->saveToFile($path);

        // Return the URL to the QR code image
        return plugin_dir_url(__FILE__) . '../public/qrcodes/' . $event_id . '.png';
    }
}
