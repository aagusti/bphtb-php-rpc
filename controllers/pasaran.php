<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pasaran extends CI_Controller 
{
    private $module = 'sspd';
    private $controller = 'pasaran';
    private $current = 'sspd';

	function __construct() {
		parent::__construct();
		if(!$this->session->userdata('login')) {
			$this->session->set_flashdata('msg_warning', 'Session telah kadaluarsa, silahkan login ulang.');
			redirect('login');
			exit;
		}
		
		$this->load->library('module_auth',array(
            'module'=>$this->module
        ));

		$this->load->model(array(
            'apps_model',
            'bphtb_model', 
            'pasaran_model'
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
		$this->load->view('vpasaran', $data);
	}
	
	function grid() {
        $this->load->library('Datatables');
        $this->datatables->select("id, {rownum} as rownum, 
            (kd_propinsi||'.'||kd_dati2||'.'||kd_kecamatan||'.'||kd_kelurahan||'.'||kd_blok||'-'||lpad(no_urut::text, 4, '0')) as kode, 
            uraian, harga", false);
        $this->datatables->from('bphtb_pasar');

        $this->datatables->rupiah_column('4');
        echo $this->datatables->generate();
	}

	//admin
	private function fvalidation() {
		$this->form_validation->set_error_delimiters('<span>', '</span>');
        $this->form_validation->set_rules('kd_propinsi', 'Popinsi', 'required');
		$this->form_validation->set_rules('kd_dati2', 'Kota / Kabupaten', 'required');
        $this->form_validation->set_rules('kd_kecamatan', 'Kecamatan', 'required');
        $this->form_validation->set_rules('kd_kelurahan', 'Kelurahan', 'required');
        $this->form_validation->set_rules('kd_blok', 'Blok', 'required');
        $this->form_validation->set_rules('no_urut', 'No Urut', 'required');
        $this->form_validation->set_rules('uraian', 'Uraian', 'required');
        $this->form_validation->set_rules('harga', 'Harga', 'required');
	}
	
	private function fpost() {        
        $data['id'] = $this->input->post('id');
        $data['kd_propinsi'] = $this->input->post('kd_propinsi');
        $data['kd_dati2'] = $this->input->post('kd_dati2');
        $data['kd_kecamatan'] = $this->input->post('kd_kecamatan');
        $data['kd_kelurahan'] = $this->input->post('kd_kelurahan');
        $data['kd_blok'] = $this->input->post('kd_blok');
        $data['no_urut'] = $this->input->post('no_urut');
        $data['uraian'] = $this->input->post('uraian');
        $data['harga'] = $this->input->post('harga');
        
		return $data;
	}
	
	public function add() {
		if(!$this->module_auth->create) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_create);
			redirect(active_module_url($this->uri->segment(2)));
		}
        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();
        $data['faction'] = active_module_url($this->controller . '/add');
        
		$data['dt']          = $this->fpost();
		
		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {
			$data = array(
                'kd_propinsi' => $this->input->post('kd_propinsi'),
				'kd_dati2' => $this->input->post('kd_dati2'),
                'kd_kecamatan' => $this->input->post('kd_kecamatan'),
                'kd_kelurahan' => $this->input->post('kd_kelurahan'),
                'kd_blok' => $this->input->post('kd_blok'),
                'no_urut' => $this->input->post('no_urut'),
                'uraian' => $this->input->post('uraian'),
                'harga' => $this->input->post('harga')
			);
			$this->pasaran_model->save($data);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');
			redirect(active_module_url($this->uri->segment(2)));
		}
        $data['dt']['kd_propinsi'] = ($this->input->post('kd_propinsi'))?$this->input->post('kd_propinsi'):KD_PROPINSI;
        $data['propinsi'] = $this->bphtb_model->get_propinsi_nama($data['dt']['kd_propinsi']);
        
        $data['dt']['kd_dati2'] = ($this->input->post('kd_dati2'))?$this->input->post('kd_dati2'):KD_DATI2;
		$data['dati2']       = $this->bphtb_model->get_dati2_nama($data['dt']['kd_propinsi'], $data['dt']['kd_dati2']);
        $data['kecamatan']   = $this->bphtb_model->get_kecamatan_nama($data['dt']['kd_propinsi'], $data['dt']['kd_dati2'], $data['dt']['kd_kecamatan']);
        $data['kelurahan']   = $this->bphtb_model->get_kelurahan_nama($data['dt']['kd_propinsi'], $data['dt']['kd_dati2'], 
                                        $data['dt']['kd_kecamatan'], $data['dt']['kd_kelurahan']);
        
        $this->load->view('vpasaran_form',$data);
	}
	
	public function edit() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url($this->uri->segment(2)));
		}

        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();
        $data['faction'] = active_module_url($this->controller . '/update');
			
		$id = $this->uri->segment(4);
		if($id && $get = $this->pasaran_model->get($id)) {
			$data['dt']['id'] = $get->id;
			$data['dt']['kd_propinsi'] = $get->kd_propinsi;
            $data['dt']['kd_dati2'] = $get->kd_dati2;
            $data['dt']['kd_kecamatan'] = $get->kd_kecamatan;
            $data['dt']['kd_kelurahan'] = $get->kd_kelurahan;
            $data['dt']['kd_blok'] = $get->kd_blok;
            $data['dt']['no_urut'] = $get->no_urut;
            $data['dt']['uraian'] = $get->uraian;
            $data['dt']['harga'] = $get->harga;
            
            $data['propinsi']    = $this->bphtb_model->get_propinsi_nama($get->kd_propinsi);
            $data['dati2']       = $this->bphtb_model->get_dati2_nama($get->kd_propinsi, $get->kd_dati2);
            $data['kecamatan']      = $this->bphtb_model->get_kecamatan_nama($get->kd_propinsi, $get->kd_dati2, $get->kd_kecamatan);
            $data['kelurahan']      = $this->bphtb_model->get_kelurahan_nama($get->kd_propinsi, $get->kd_dati2, $get->kd_kecamatan, $get->kd_kelurahan);
			
			$this->load->view('vpasaran_form',$data);
		} else {
			show_404();
		}
	}
	
	public function update() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url($this->uri->segment(2)));
		}

        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();
        $data['faction'] = active_module_url($this->controller . '/update');
		$data['dt'] = $this->fpost();
				
		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {	
			$data = array(
                'kd_propinsi' => $this->input->post('kd_propinsi'),
				'kd_dati2' => $this->input->post('kd_dati2'),
                'kd_kecamatan' => $this->input->post('kd_kecamatan'),
                'kd_kelurahan' => $this->input->post('kd_kelurahan'),
                'kd_blok' => $this->input->post('kd_blok'),
                'no_urut' => $this->input->post('no_urut'),
                'uraian' => $this->input->post('uraian'),
                'harga' => $this->input->post('harga')
			);
            if (strtotime($this->input->post('tgl_sk'))) {
                $data['tgl_sk'] = $this->input->post('tgl_sk');
            }
			$this->pasaran_model->update($this->input->post('id'), $data);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');
			redirect(active_module_url($this->uri->segment(2)));
		}
        
        $data['propinsi']    = $this->bphtb_model->get_propinsi_nama($this->input->post('kd_propinsi'));
        $data['dati2']       = $this->bphtb_model->get_dati2_nama($this->input->post('kd_propinsi'), $this->input->post('kd_dati2'));
        $data['kecamatan']      = $this->bphtb_model->get_kecamatan();
        $data['kelurahan']      = $this->bphtb_model->get_kelurahan();
        
		$this->load->view('vpasaran_form',$data);
	}
	
	public function delete() {
		if(!$this->module_auth->delete) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_delete);
			redirect(active_module_url($this->uri->segment(2)));
		}
		
		$id = $this->uri->segment(4);
		if($id && $this->pasaran_model->get($id)) {
			$this->pasaran_model->delete($id);
			$this->session->set_flashdata('msg_success', 'Data telah dihapus');
			redirect(active_module_url($this->uri->segment(2)));
		} else {
			show_404();
		}
	}
    
    // additional func
    
    function get_kecamatan_nama() {
        $id = $this->uri->segment(4);
        $kd = explode(".", $id);
		$ret = $this->bphtb_model->get_kecamatan_nama($kd[0], $kd[1], $kd[2]);
		$result = (object) array('found' => 1, 'result' => $ret);
		echo json_encode($result); 
    }
    
    function get_kelurahan_nama() {
        $id = $this->uri->segment(4);
        $kd = explode(".", $id);
		$ret = $this->bphtb_model->get_kelurahan_nama($kd[0], $kd[1], $kd[2], $kd[3]);
		$result = (object) array('found' => 1, 'result' => $ret);
		echo json_encode($result); 
    }
}