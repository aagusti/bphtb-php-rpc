<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class irul extends CI_Controller {

	function __construct()
	{
        parent::__construct();
        if (!is_login()) {
            echo "<script>window.location.replace('" . base_url() . "');</script>";
            exit;
        }
	}
	
	public function index()
	{
		echo "irul coi";
	} 
}
