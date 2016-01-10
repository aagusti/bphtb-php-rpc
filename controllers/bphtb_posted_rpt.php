<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bphtb_posted_rpt extends CI_Controller {

	private $module = 'pbb_bphtb';
	private $controller = 'bphtb_posted_rpt';
	private $current = 'pbb';
    
	function __construct() {
		parent::__construct();
		if(!$this->session->userdata('login')) {
			$this->session->set_flashdata('msg_warning', 'Session telah kadaluarsa, silahkan login ulang.');
			redirect('login');
			exit;
		}

        $this->load->library('module_auth', array('module' => $this->module));
        
		$this->load->model(array(
            'apps_model',
            'bphtb_model', 
            'sspd_model', 
            'ppat_model', 
            'ppat_user_model',
        ));
        
		$this->load->helper(active_module());
	}

	public function index() {
		if(!$this->module_auth->read) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
			redirect(active_module_url(''));
		}

		$data['current'] = $this->current;
		$data['apps']    = $this->apps_model->get_active_only();
                
		$this->load->view('vbphtb_posted_rpt', $data);
	}
	
	function grid() {
        $tgl1 = $this->uri->segment(4);
        $tgl2 = $this->uri->segment(5);
        
        if($tgl1=='') {
            $tgl1 = date('Y-01-01');
            $tgl2 = date('Y-m-d');;
        } else {
            $tgl1 = date('Y-m-d', strtotime($tgl1));
            $tgl2 = date('Y-m-d', strtotime($tgl2));;
        }
        
        $this->load->library('Datatables');
        $this->datatables->select('ss.id, {rownum} as rownum,
            get_sspdno(ss.id) as sspdno, ss.wp_nama,
            get_nop_sspd(ss.id,true) as nop, ss.thn_pajak_sppt, ss.bphtb_harus_dibayarkan, 
            coalesce(ss.status_pembayaran,0) as status_pembayaran,
            ss.tgl_transaksi, ppat.nama as ppatnm, ppat.kode as ppatkd, 
            str_status_validasi(coalesce(ss.status_validasi,0)) as status,
            ss.no_ajb, ss.tgl_ajb', false);
        $this->datatables->from('bphtb_sspd ss');
        $this->datatables->join('bphtb_ppat ppat', 'ppat.id=ss.ppat_id');
                
        $this->datatables->where("ss.kd_propinsi", KD_PROPINSI);
        $this->datatables->where("ss.kd_dati2", KD_DATI2);
        
        // menampilkan data SSPD yang sudah posted
        $this->datatables->where("ss.posted", 1);
        
        if($this->session->userdata('isppat'))
            $this->datatables->where("ss.ppat_id", $this->session->userdata('ppat_id'));
            
        if($this->input->get('sSearch') == '')
            $this->datatables->filter("date(ss.tgl_transaksi) between '{$tgl1}' and '{$tgl2}'");
        
        $this->datatables->date_column('8,13');
        $this->datatables->rupiah_column('6');
        $this->datatables->checkbox_column('7');
        echo $this->datatables->generate();
	}
    
	function show_rpt() {
		$cls_mtd_html = $this->router->fetch_class()."/cetak/html/";
		$cls_mtd_pdf  = $this->router->fetch_class()."/cetak/pdf/";
		$data['rpt_html'] = active_module_url($cls_mtd_html. $_SERVER['QUERY_STRING']);;
		$data['rpt_pdf']  = active_module_url($cls_mtd_pdf . $_SERVER['QUERY_STRING']);;
        $this->load->view('vjasper_viewer', $data);
	}

	function cetak() {
        $type = 'pdf';//$this->uri->segment(4);
		$rptx = $this->uri->segment(5);
        
        $tgl_transaksi = "TANGGAL TRANSAKSI {$this->uri->segment(6)} S.D. {$this->uri->segment(7)} ";
        $tgl1 = date('Y-m-d', strtotime($this->uri->segment(6)));
		$tgl2 = date('Y-m-d', strtotime($this->uri->segment(7)));
		
		$jasper = $this->load->library('Jasper');
		$params = array(
			"kondisi" => " and posted=1 and date(tgl_transaksi) between '{$tgl1}' and '{$tgl2}' ",
            "tgl_transaksi" => $tgl_transaksi,
			"daerah" => LICENSE_TO,
			"dinas" => LICENSE_TO_SUB,
			"logo" => base_url("assets/img/logorpt__.jpg"),
		);
		echo $jasper->cetak($rptx, $params, $type, false);
	}
}
