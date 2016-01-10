<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hukum extends CI_Controller {

	function __construct() {
		parent::__construct();
		if(!$this->session->userdata('login')) {
			$this->session->set_flashdata('msg_warning', 'Session telah kadaluarsa, silahkan login ulang.');
			redirect('login');
			exit;
		}
		
		$module = 'referensi';
		$this->load->library('module_auth',array('module'=>$module));

		$this->load->model(array('apps_model'));
        $this->load->model(array('hukum_model'));
	}

	public function index() {
		if(!$this->module_auth->read) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
			redirect(active_module_url(''));
		}

		$data['current'] = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$this->load->view('vhukum', $data);
	}
	
	function grid() {
		$i=0;
		$responce = new stdClass();
        if($query = $this->hukum_model->get_all()) {
			foreach($query as $row) {
				$responce->aaData[$i][]=$row->id;
				$responce->aaData[$i][]=$row->nama;
                $responce->aaData[$i][]=$row->uraian;
				$i++;
			}
		} else {
			$responce->sEcho=1;
			$responce->iTotalRecords="0";
			$responce->iTotalDisplayRecords="0";
			$responce->aaData=array();
		}
		echo json_encode($responce);
	}

	//admin
	private function fvalidation() {
		$this->form_validation->set_error_delimiters('<span>', '</span>');
        $this->form_validation->set_rules('id', 'Kode', 'required');
        $this->form_validation->set_rules('nama','Nama','required|trim|max_length[50]');
        $this->form_validation->set_rules('uraian','Uraian','required|trim|max_length[50]');
	}
	
	private function fpost() {
        $data['cur_id'] = $this->input->post('id');
		$data['id'] = $this->input->post('id');
		$data['nama'] = $this->input->post('nama');
        $data['uraian'] = $this->input->post('uraian');
		
		return $data;
	}
	
	public function add() {
		if(!$this->module_auth->create) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_create);
			redirect(active_module_url('hukum'));
		}
		$data['current']     = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction']     = active_module_url('hukum/add');
		$data['dt']          = $this->fpost();
		
		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {
			$data = array(
                'id' => $this->input->post('id'),
				'nama' => $this->input->post('nama'),
                'uraian' => $this->input->post('uraian')
			);
            $this->hukum_model->save($data);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');		
			redirect(active_module_url('hukum'));
		}
		$this->load->view('vhukum_form',$data);
	}
	
	public function edit() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url('hukum'));
		}
		$data['current']   = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction']   = active_module_url('hukum/update');
			
		$id = $this->uri->segment(4);
        if($id && $get = $this->hukum_model->get($id)) {
            $data['dt']['cur_id'] = $get->id;
			$data['dt']['id'] = $get->id;
			$data['dt']['nama'] = $get->nama;
            $data['dt']['uraian'] = $get->uraian;
			
			$this->load->view('vhukum_form',$data);
		} else {
			show_404();
		}
	}
	
	public function update() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url('hukum'));
		}
		$data['current'] = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction'] = active_module_url('hukum/update');
		$data['dt'] = $this->fpost();
				
		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {	
			$data = array(
                'id' => $this->input->post('id'),
				'nama' => $this->input->post('nama'),
                'uraian' => $this->input->post('uraian')
			);
            $this->hukum_model->update($this->input->post('cur_id'), $data);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');
			redirect(active_module_url('hukum'));
		}
		$this->load->view('vhukum_form',$data);
	}
	
	public function delete() {
		if(!$this->module_auth->delete) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_delete);
			redirect(active_module_url('hukum'));
		}
		
		$id = $this->uri->segment(4);
        if($id && $this->hukum_model->get($id)) {
            $this->hukum_model->delete($id);
			$this->session->set_flashdata('msg_success', 'Data telah dihapus');
			redirect(active_module_url('hukum'));
		} else {
			show_404();
		}
	}
}