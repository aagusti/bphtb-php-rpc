<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class lap_penerimaan extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            $this->session->set_flashdata('msg_warning', 'Session telah kadaluarsa, silahkan login ulang.');
            redirect('login');
            exit;
        }
        
        $module = 'laporan';
        $this->load->library('module_auth', array(
            'module' => $module
        ));
        
        $this->load->model(array(
            'apps_model'
        ));
        
		$this->load->helper(active_module());
    }
    
    function index() { /* asd */ }
	
    function harian() {
        if (!$this->module_auth->read) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
            redirect(active_module_url(''));
        }
        
        $data['apps']      = $this->apps_model->get_active_only();
		$data['current']   = 'penerimaan';
        $data['judul_lap'] = 'Laporan Penerimaan : Harian';
        $data['rpt']       = "harian";
		
		$tglawal  = date('d-m-Y', strtotime('2013-01-01'));
		$tglakhir = date('d-m-Y');
        $data['tglawal']  = $tglawal;
        $data['tglakhir'] = $tglakhir;
		
        $this->load->view('vlap_penerimaan', $data);
    }
    
    function harian_kel() {
        if (!$this->module_auth->read) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
            redirect(active_module_url(''));
        }
        
        $data['apps']      = $this->apps_model->get_active_only();
		$data['current']   = 'penerimaan';
        $data['judul_lap'] = 'Laporan Penerimaan : Harian Per Kelurahan';
        $data['rpt']       = "hariankel";
		
		$tglawal  = date('d-m-Y', strtotime('2013-01-01'));
		$tglakhir = date('d-m-Y');
        $data['tglawal']  = $tglawal;
        $data['tglakhir'] = $tglakhir;
		
        $this->load->view('vlap_penerimaan', $data);
    }
    
    function harian_kec() {
        if (!$this->module_auth->read) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
            redirect(active_module_url(''));
        }
        
        $data['apps']      = $this->apps_model->get_active_only();
		$data['current']   = 'penerimaan';
        $data['judul_lap'] = 'Laporan Penerimaan : Harian Per Kecamatan';
        $data['rpt']       = "hariankec";
		
		$tglawal  = date('d-m-Y', strtotime('2013-01-01'));
		$tglakhir = date('d-m-Y');
        $data['tglawal']  = $tglawal;
        $data['tglakhir'] = $tglakhir;
		
        $this->load->view('vlap_penerimaan', $data);
    }
    
    function harian_kab() {
        if (!$this->module_auth->read) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
            redirect(active_module_url(''));
        }
        
        $data['apps']      = $this->apps_model->get_active_only();
		$data['current']   = 'penerimaan';
        $data['judul_lap'] = 'Laporan Penerimaan : Harian Per Kabupaten';
        $data['rpt']       = "hariankab";
		
		$tglawal  = date('d-m-Y', strtotime('2013-01-01'));
		$tglakhir = date('d-m-Y');
        $data['tglawal']  = $tglawal;
        $data['tglakhir'] = $tglakhir;
		
        $this->load->view('vlap_penerimaan', $data);
    }
	
    function harian_not_register() {
        if (!$this->module_auth->read) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
            redirect(active_module_url(''));
        }
        
        $data['apps']      = $this->apps_model->get_active_only();
		$data['current']   = 'bpn';
        $data['judul_lap'] = 'Dokumen Dalam Proses';
        $data['rpt']       = "belum_validasi";
		
		$tglawal  = date('d-m-Y', strtotime('2013-01-01'));
		$tglakhir = date('d-m-Y');
        $data['tglawal']  = $tglawal;
        $data['tglakhir'] = $tglakhir;
		
        $this->load->view('vlap_penerimaan', $data);
    }
    
    function harian_yes_register() {
        if (!$this->module_auth->read) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
            redirect(active_module_url(''));
        }
        
        $data['apps']      = $this->apps_model->get_active_only();
		$data['current']   = 'bpn';
        $data['judul_lap'] = 'Dokumen Selesai';
        $data['rpt']       = "sudah_validasi";
		
		$tglawal  = date('d-m-Y', strtotime('2013-01-01'));
		$tglakhir = date('d-m-Y');
        $data['tglawal']  = $tglawal;
        $data['tglakhir'] = $tglakhir;
		
        $this->load->view('vlap_penerimaan', $data);
    }
	
	/* report */
	function show_rpt() {
		$cls_mtd_html = $this->router->fetch_class()."/cetak/html/";
		$cls_mtd_pdf  = $this->router->fetch_class()."/cetak/pdf/";
		$data['rpt_html'] = active_module_url($cls_mtd_html. $_SERVER['QUERY_STRING']);;
		$data['rpt_pdf']  = active_module_url($cls_mtd_pdf . $_SERVER['QUERY_STRING']);;
        $this->load->view('vjasper_viewer', $data);
	}

	function cetak() {		
        $type = 'pdf'; //$this->uri->segment(4);
        $rptx = $this->uri->segment(5);
        $tglm = $this->uri->segment(6);
        $tgls = $this->uri->segment(7);
		
        $tglm = date('Y-m-d', strtotime($tglm));
        $tgls = date('Y-m-d', strtotime($tgls));
	
		$jasper = $this->load->library('Jasper');
		$params = array(
			"daerah" => LICENSE_TO,
			"startdate" => "{$tglm}",
			"enddate" => "{$tgls}",
			"logo" => base_url("assets/img/logorpt__.jpg"),
		);
		echo $jasper->cetak($rptx, $params, $type, false);
	}
}
