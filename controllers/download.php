<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class download extends CI_Controller {
       
	function __construct() {
		parent::__construct();
	}

	public function index() {
        echo 'Tidak ada file yang dapat didownload.';
	}
    
    function download_file() {
        $file = urldecode($this->uri->segment(4));
        if ($file) {
            $dirfile = dirname(__FILE__) . '//..//files//' . $file;
            if (file_exists($dirfile)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($file).'"');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($dirfile));
                readfile($dirfile);
                exit;
            } else {
                echo "File tidak ditemukan dalam direktori.";
            }
        } else {
            echo 'Tidak ada file yang dapat didownload.';
        }
    }
    
}