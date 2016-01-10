<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jabatan extends CI_Controller {

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
        $this->load->model(array('jabatan_model'));
	}

	public function index() {
		if(!$this->module_auth->read) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
			redirect(active_module_url(''));
		}

		$data['current'] = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$this->load->view('vjabatan', $data);
	}
	
	function grid() {
		$i=0;
		$responce = new stdClass();
        if($query = $this->jabatan_model->get_all()) {
			foreach($query as $row) {
				$responce->aaData[$i][]=$row->kd_jabatan;
				$responce->aaData[$i][]=$row->nm_jabatan;
                $responce->aaData[$i][]=$row->singkatan_jabatan;
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
        $this->form_validation->set_rules('kd_jabatan','Kode','required|trim|max_length[2]');
        $this->form_validation->set_rules('nm_jabatan','Nama Jabata','required|trim|max_length[30]');
        $this->form_validation->set_rules('singkatan_jabatan','Singkatan','trim|max_length[30]');
	}
	
	private function fpost() {
        $data['cur_id'] = $this->input->post('kd_jabatan');
		$data['kd_jabatan'] = $this->input->post('kd_jabatan');
		$data['nm_jabatan'] = $this->input->post('nm_jabatan');
        $data['singkatan_jabatan'] = $this->input->post('singkatan_jabatan');
		
		return $data;
	}
	
	public function add() {
		if(!$this->module_auth->create) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_create);
			redirect(active_module_url('jabatan'));
		}
		$data['current']     = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction']     = active_module_url('jabatan/add');
		$data['dt']          = $this->fpost();
		
		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {
			$data = array(
                'kd_jabatan' => $this->input->post('kd_jabatan'),
				'nm_jabatan' => $this->input->post('nm_jabatan'),
                'singkatan_jabatan' => $this->input->post('singkatan_jabatan')
			);
            $this->jabatan_model->save($data);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');		
			redirect(active_module_url('jabatan'));
		}
		$this->load->view('vjabatan_form',$data);
	}
	
	public function edit() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url('jabatan'));
		}
		$data['current']   = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction']   = active_module_url('jabatan/update');
			
		$id = $this->uri->segment(4);
        if($id && $get = $this->jabatan_model->get($id)) {
            $data['dt']['cur_id'] = $get->kd_jabatan;
			$data['dt']['kd_jabatan'] = $get->kd_jabatan;
			$data['dt']['nm_jabatan'] = $get->nm_jabatan;
            $data['dt']['singkatan_jabatan'] = $get->singkatan_jabatan;
			
			$this->load->view('vjabatan_form',$data);
		} else {
			show_404();
		}
	}
	
	public function update() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url('jabatan'));
		}
		$data['current'] = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction'] = active_module_url('jabatan/update');
		$data['dt'] = $this->fpost();
				
		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {	
			$data = array(
                'kd_jabatan' => $this->input->post('kd_jabatan'),
				'nm_jabatan' => $this->input->post('nm_jabatan'),
                'singkatan_jabatan' => $this->input->post('singkatan_jabatan')
			);
            $this->jabatan_model->update($this->input->post('cur_id'), $data);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');
			redirect(active_module_url('jabatan'));
		}
		$this->load->view('vjabatan_form',$data);
	}
	
	public function delete() {
		if(!$this->module_auth->delete) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_delete);
			redirect(active_module_url('jabatan'));
		}
		
		$id = $this->uri->segment(4);
        if($id && $this->jabatan_model->get($id)) {
            $this->jabatan_model->delete($id);
			$this->session->set_flashdata('msg_success', 'Data telah dihapus');
			redirect(active_module_url('jabatan'));
		} else {
			show_404();
		}
	}
}