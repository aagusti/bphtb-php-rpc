<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bphtb_verifikasi extends CI_Controller {
	private $module = 'pbb_bphtb';
	private $controller = 'bphtb_verifikasi';
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
        
		$this->load->view('vbphtb_verifikasi', $data);
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
        $this->datatables->join('bphtb_perolehan p', 'p.id=ss.perolehan_id');
        $this->datatables->join('bphtb_dasar d', 'd.id=ss.dasar_id');
                
        $this->datatables->where("ss.kd_propinsi", KD_PROPINSI);
        $this->datatables->where("ss.kd_dati2", KD_DATI2);
        
        // menampilkan data sspd yg AJBnya SUDAH di verifikasi saja
        $this->datatables->where("ss.verifikasi_uid is not null");
        $this->datatables->where("(ss.status_pembayaran=1 or ss.bphtb_harus_dibayarkan=0)");
        $this->datatables->where("(ss.verifikasi_bphtb_date is null or ss.verifikasi_bphtb_uid is null)");
        
        if($this->session->userdata('isppat'))
            $this->datatables->where("ss.ppat_id", $this->session->userdata('ppat_id'));
            
        if($this->input->get('sSearch') == '')
            $this->datatables->filter("date(ss.tgl_transaksi) between '{$tgl1}' and '{$tgl2}'");
        
        $this->datatables->date_column('8,13');
        $this->datatables->rupiah_column('6');
        $this->datatables->checkbox_column('7');
        echo $this->datatables->generate();
	}
    
	//admin
	private function fvalidation() {
        $this->form_validation->set_rules('no_ajb','No. AJB','trim|max_length[50]');
        $this->form_validation->set_rules('wp_nama_asal','Nama WP Asal','trim|max_length[50]');
	}
    	
	private function fpost() {
        $data['id'] = $this->input->post('id');
        $data['tahun'] = $this->input->post('tahun');
		$data['kode'] = $this->input->post('kode');
        $data['no_sspd'] = $this->input->post('no_sspd');
        $data['ppat_id'] = $this->input->post('ppat_id');
        $data['wp_nama'] = $this->input->post('wp_nama');
        $data['wp_npwp'] = $this->input->post('wp_npwp');
        $data['wp_alamat'] = $this->input->post('wp_alamat');
        $data['wp_blok_kav'] = $this->input->post('wp_blok_kav');
        $data['wp_kelurahan'] = $this->input->post('wp_kelurahan');
        $data['wp_rt'] = $this->input->post('wp_rt');
        $data['wp_rw'] = $this->input->post('wp_rw');
        $data['wp_kecamatan'] = $this->input->post('wp_kecamatan');
        $data['wp_kota'] = $this->input->post('wp_kota');
        $data['wp_provinsi'] = $this->input->post('wp_provinsi');
        $data['wp_identitas'] = $this->input->post('wp_identitas');
        $data['wp_identitaskd'] = 'KTP'; //$this->input->post('wp_identitaskd');
        $data['tgl_transaksi'] = $this->input->post('tgl_transaksi');    
        $data['kd_propinsi'] = $this->input->post('kd_propinsi');
        $data['kd_dati2'] = $this->input->post('kd_dati2');
        $data['kd_kecamatan'] = $this->input->post('kd_kecamatan');
        $data['kd_kelurahan'] = $this->input->post('kd_kelurahan');
        $data['kd_blok'] = $this->input->post('kd_blok');
        $data['no_urut'] = $this->input->post('no_urut');
        $data['kd_jns_op'] = $this->input->post('kd_jns_op');
        $data['thn_pajak_sppt'] = $this->input->post('thn_pajak_sppt');
        $data['op_alamat'] = $this->input->post('op_alamat');
        $data['op_blok_kav'] = $this->input->post('op_blok_kav');
        $data['op_rt'] = $this->input->post('op_rt');
        $data['op_rw'] = $this->input->post('op_rw');
        $data['bumi_luas'] = $this->input->post('bumi_luas');
        $data['bumi_njop'] = $this->input->post('bumi_njop');
        $data['bng_luas'] = $this->input->post('bng_luas');
        $data['bng_njop'] = $this->input->post('bng_njop');
        $data['no_sertifikat'] = $this->input->post('no_sertifikat');
        $data['njop'] = $this->input->post('njop');
        $data['perolehan_id'] = $this->input->post('perolehan_id');
        $data['npop'] = $this->input->post('npop');
        $data['npoptkp'] = $this->input->post('npoptkp');
        $data['tarif'] = $this->input->post('tarif');
        $data['terhutang'] = $this->input->post('terhutang');
        $data['bagian'] = $this->input->post('bagian');
        $data['pembagi'] = $this->input->post('pembagi');
        $data['tarif_pengurang'] = $this->input->post('tarif_pengurang');
        $data['pengurang'] = $this->input->post('pengurang');
        $data['bphtb_sudah_dibayarkan'] = $this->input->post('bphtb_sudah_dibayarkan');
        $data['denda'] = $this->input->post('denda');
        //$data['restitusi'] = $this->input->post('restitusi');
        $data['bphtb_harus_dibayarkan'] = $this->input->post('bphtb_harus_dibayarkan');
        $data['status_pembayaran'] = $this->input->post('status_pembayaran');
        $data['dasar_id'] = $this->input->post('dasar_id');
        $data['header_id'] = $this->input->post('header_id');
        $data['wp_kdpos'] = $this->input->post('wp_kdpos');
        $data['keterangan'] = $this->input->post('keterangan');
        
        $data['created'] = $this->input->post('created');
        $data['create_uid'] = $this->input->post('create_uid');
		$data['updated'] = $this->input->post('updated');
        $data['update_uid'] = $this->input->post('update_uid');
        
        //tambahan
        $data['no_ajb'] = $this->input->post('no_ajb');
        $data['tgl_ajb'] = $this->input->post('tgl_ajb');
        $data['wp_nama_asal'] = $this->input->post('wp_nama_asal');
        $data['jml_pph'] = $this->input->post('jml_pph');
        $data['tgl_pph'] = $this->input->post('tgl_pph');
        
        $data['verifikasi_date'] = $this->input->post('verifikasi_date');
        $data['verifikasi_bphtb_date'] = $this->input->post('verifikasi_bphtb_date');
        
        $data['status_daftar'] = $this->input->post('status_daftar');
        
		return $data;
	}
	
	public function edit() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url($this->controller));
		}
		$data['current']        = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();
		$data['faction']        = active_module_url($this->controller.'/update');
        
        $data['mode'] = 'edit';
        $data['error_date_format'] = '';
        	
		$id = (int)$this->uri->segment(4);
		if($id && $get = $this->sspd_model->get($id)) {                        
			$data['dt']['id'] = $get->id;
			$data['dt']['tahun'] = $get->tahun;
            $data['dt']['kode'] = str_pad($get->kode, 2, "0", STR_PAD_LEFT);
            $data['dt']['no_sspd'] = str_pad($get->no_sspd, 6, "0", STR_PAD_LEFT);;
            $data['dt']['ppat_id'] = $get->ppat_id;
            $data['dt']['wp_nama'] = $get->wp_nama;
            $data['dt']['wp_npwp'] = $get->wp_npwp;
            $data['dt']['wp_alamat'] = $get->wp_alamat;
            
            $data['dt']['no_ajb'] = $get->no_ajb;
            $data['dt']['tgl_ajb'] = empty($get->tgl_ajb) ? '' : date('d-m-Y', strtotime($get->tgl_ajb));
            $data['dt']['wp_nama_asal'] = empty($get->wp_nama_asal) ? $get->wp_nama : $get->wp_nama_asal;
            $data['dt']['jml_pph'] = $get->jml_pph;
            $data['dt']['tgl_pph'] = empty($get->tgl_pph) ? '' : date('d-m-Y', strtotime($get->tgl_pph));
            
            $data['dt']['verifikasi_date'] = empty($get->verifikasi_date) ? date('d-m-Y') : date('d-m-Y', strtotime($get->verifikasi_date));
            $data['dt']['verifikasi_bphtb_date'] = empty($get->verifikasi_bphtb_date) ? date('d-m-Y') : date('d-m-Y', strtotime($get->verifikasi_bphtb_date));
                        
            $data['dt']['updated'] = $get->updated;
            $data['dt']['update_uid'] = $get->update_uid;
			
            $this->load->view('vbphtb_verifikasi_form',$data);
		} else {
			show_404();
		}
	}
	
	public function update() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url($this->controller));
		}
        
		$data['current']        = $this->current;
        $data['apps']           = $this->apps_model->get_active_only();
		$data['faction']        = active_module_url($this->controller.'/update');
        
		$data['dt'] = $this->fpost();
        
		$this->fvalidation();
		if ($this->form_validation->run()) {
            $dat = array(                
                'no_ajb' => $this->input->post('no_ajb') ? $this->input->post('no_ajb') : null,
                'tgl_ajb' => $this->input->post('tgl_ajb') ? date('Y-m-d', strtotime($this->input->post('tgl_ajb'))) : null,
                'wp_nama_asal' => $this->input->post('wp_nama_asal') ? $this->input->post('wp_nama_asal') : null,
                'jml_pph' => $this->input->post('jml_pph') ? to_decimal($this->input->post('jml_pph')) : null,
                'tgl_pph' => $this->input->post('tgl_pph') ? date('Y-m-d', strtotime($this->input->post('tgl_pph'))) : null,

                'verifikasi_bphtb_date' => $this->input->post('verifikasi_bphtb_date') ? date('Y-m-d', strtotime($this->input->post('verifikasi_bphtb_date'))) : date('Y-m-d'),
                'verifikasi_bphtb_uid' => $this->session->userdata('uid'),
			);
            
            $this->sspd_model->update($this->input->post('id'), $dat);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');
			redirect(active_module_url($this->controller));
		}
        
		$this->load->view('vbphtb_verifikasi_form',$data);
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
		
        $kondisi = " and date(tgl_transaksi) between '{$tgl1}' and '{$tgl2}' 
            and verifikasi_date is not null and verifikasi_uid is not null ";
            //and verifikasi_bphtb_uid is not null and verifikasi_bphtb_date is not null ";
        
		$jasper = $this->load->library('Jasper');
		$params = array(
			"kondisi" => $kondisi,
            "tgl_transaksi" => $tgl_transaksi,
			"daerah" => LICENSE_TO,
			"dinas" => LICENSE_TO_SUB,
			"logo" => base_url("assets/img/logorpt__.jpg"),
		);
		echo $jasper->cetak($rptx, $params, $type, false);
	}
}
