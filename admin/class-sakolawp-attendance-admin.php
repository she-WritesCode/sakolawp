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
        add_action('wp_ajax_sakolawp_generate_qr_code', [$this, 'generate_qr_code_ajax']);
        add_action('wp_ajax_nopriv_sakolawp_generate_qr_code', [$this, 'generate_qr_code_ajax']);
    }

    function generate_qr_code($event_id)
    {
        $file_url = get_post_meta($event_id, 'attendance_qr_code', true);
        if (!empty($file_url)) {
            return $file_url;
        }

        // Get the upload directory
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'] . '/qr_codes';
        $upload_url = $upload_dir['baseurl'] . '/qr_codes';

        // Create the directory if it doesn't exist
        if (!file_exists($upload_path)) {
            wp_mkdir_p($upload_path);
        }

        // Define the file path and URL
        $file_path = $upload_path . '/' . $event_id . '.png';
        $file_url = $upload_url . '/' . $event_id . '.png';
        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->data($event_id)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->size(300)
            ->margin(10)
            ->build();

        // Save the QR code to the file
        $qrCode->saveToFile($file_path);

        update_post_meta($event_id, 'attendance_qr_code', $file_url);

        // Return the URL to the QR code image
        return $file_url;
    }

    function generate_qr_code_ajax()
    {
        $event_id = isset($_POST['event_id']) ? $_POST['event_id'] : "";
        $result = [];

        if (empty($event_id)) {
            $result["message"] = 'Invalid details';
            wp_send_json_error($result, 400);
        }

        $result["image"] = $this->generate_qr_code($event_id);
        $result["message"] = 'QR code generated successfully';
        wp_send_json_success($result, 201);
    }
}
