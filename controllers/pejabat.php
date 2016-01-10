<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pejabat extends CI_Controller {

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
        $this->load->model(array('jabatan_model', 'pejabat_model'));
	}

	public function index() {
		if(!$this->module_auth->read) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
			redirect(active_module_url(''));
		}

		$data['current'] = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$this->load->view('vpejabat', $data);
	}
	
	function grid() {
		$i=0;
		$responce = new stdClass();
		if($query = $this->pejabat_model->get_all()) {
			foreach($query as $row) {
				$responce->aaData[$i][]=$row->id;
                $responce->aaData[$i][]=$row->nip;
				$responce->aaData[$i][]=$row->nama;
                $responce->aaData[$i][]=$row->nm_jabatan;
                $responce->aaData[$i][]=($row->enabled!=0?'True':'False');
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
        $this->form_validation->set_rules('nip','nip','required|trim|max_length[18]');
        $this->form_validation->set_rules('nama','Nama','required|trim|max_length[50]');
        $this->form_validation->set_rules('kd_jabatan','Jabatan','required|trim|max_length[2]');
	}
	
	private function fpost() {        
        $data['id'] = $this->input->post('id');
        $data['nip'] = $this->input->post('nip');
		$data['nama'] = $this->input->post('nama');
        $data['kd_jabatan'] = $this->input->post('kd_jabatan');
        $data['enabled'] = $this->input->post('enabled');
        $data['created'] = $this->input->post('created');
        $data['create_uid'] = $this->input->post('create_uid');
		$data['updated'] = $this->input->post('updated');
        $data['update_uid'] = $this->input->post('update_uid');
        
		return $data;
	}
	
	public function add() {
		if(!$this->module_auth->create) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_create);
			redirect(active_module_url('pejabat'));
		}
		$data['current']     = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction']     = active_module_url('pejabat/add');
        $data['jabatan']     = $this->jabatan_model->get_all();
		$data['dt']          = $this->fpost();
		
		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {
			$data = array(
                'nip' => $this->input->post('nip'),
				'nama' => $this->input->post('nama'),
                'kd_jabatan' => $this->input->post('kd_jabatan'),
                'enabled' => $this->input->post('enabled')=='on'?1:0,
                'created' => date('Y-m-d h:m:s'),
                'create_uid' => $this->session->userdata('username')
			);
			$this->pejabat_model->save($data);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');		
			redirect(active_module_url('pejabat'));
		}
		$this->load->view('vpejabat_form',$data);
	}
	
	public function edit() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url('pejabat'));
		}
		$data['current']   = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction']   = active_module_url('pejabat/update');
        $data['jabatan']   = $this->jabatan_model->get_all();
			
		$id = $this->uri->segment(4);
		if($id && $get = $this->pejabat_model->get($id)) {
			$data['dt']['id'] = $get->id;
			$data['dt']['nip'] = $get->nip;
            $data['dt']['nama'] = $get->nama;
            $data['dt']['kd_jabatan'] = $get->kd_jabatan;
            $data['dt']['enabled'] = $get->enabled;
            $data['dt']['updated'] = $get->updated;
            $data['dt']['update_uid'] = $get->update_uid;
			
			$this->load->view('vpejabat_form',$data);
		} else {
			show_404();
		}
	}
	
	public function update() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url('pejabat'));
		}
		$data['current'] = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction'] = active_module_url('pejabat/update');
        $data['jabatan'] = $this->jabatan_model->get_all();
		$data['dt']      = $this->fpost();
				
		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {	
			$data = array(
                'nip' => $this->input->post('nip'),
				'nama' => $this->input->post('nama'),
                'kd_jabatan' => $this->input->post('kd_jabatan'),
                'enabled' => $this->input->post('enabled')=='on'?1:0,
                'updated' => date('Y-m-d h:m:s'),
                'update_uid' => $this->session->userdata('username')
			);
            $this->pejabat_model->update($this->input->post('id'), $data);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');
			redirect(active_module_url('pejabat'));
		}
		$this->load->view('vpejabat_form',$data);
	}
	
	public function delete() {
		if(!$this->module_auth->delete) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_delete);
			redirect(active_module_url('pejabat'));
		}
		
		$id = $this->uri->segment(4);
		if($id && $this->pejabat_model->get($id)) {
			$this->pejabat_model->delete($id);
			$this->session->set_flashdata('msg_success', 'Data telah dihapus');
			redirect(active_module_url('pejabat'));
		} else {
			show_404();
		}
	}
}