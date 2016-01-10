<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Perolehan extends CI_Controller {

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
        $this->load->model(array('perolehan_model'));
	}

	public function index() {
		if(!$this->module_auth->read) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
			redirect(active_module_url(''));
		}

		$data['current'] = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$this->load->view('vperolehan', $data);
	}
	
	function grid() {
		$i=0;
		$responce = new stdClass();
		if($query = $this->perolehan_model->get_all()) {
			foreach($query as $row) {
				$responce->aaData[$i][]=$row->id;
				$responce->aaData[$i][]=$row->nama;
				$responce->aaData[$i][]=$row->singkatan;
                $responce->aaData[$i][]=number_format($row->npoptkp,0,',','.');
                $responce->aaData[$i][]=$row->pengurang;
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
        $this->form_validation->set_rules('nama','Nama','required|trim|max_length[100]');
        $this->form_validation->set_rules('npoptkp','NPOPTKP','required|numeric');
        $this->form_validation->set_rules('pengurang','Pengurang','numeric');
	}
	
	private function fpost() {
        $data['cur_id'] = $this->input->post('id');
		$data['id'] = $this->input->post('id');
		$data['nama'] = $this->input->post('nama');
        $data['npoptkp'] = $this->input->post('npoptkp');
        $data['pengurang'] = $this->input->post('pengurang');
        $data['singkatan'] = $this->input->post('singkatan');
		
		return $data;
	}
	
	public function add() {
		if(!$this->module_auth->create) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_create);
			redirect(active_module_url('perolehan'));
		}
		$data['current']     = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction']     = active_module_url('perolehan/add');
		$data['dt']          = $this->fpost();
		
		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {
			$data = array(
                'id' => $this->input->post('id'),
				'nama' => $this->input->post('nama'),
                'npoptkp' => $this->input->post('npoptkp'),
                'pengurang' => $this->input->post('pengurang'),
                'singkatan' => $this->input->post('singkatan'),
			);
			$this->perolehan_model->save($data);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');		
			redirect(active_module_url('perolehan'));
		}
		$this->load->view('vperolehan_form',$data);
	}
	
	public function edit() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url('perolehan'));
		}
		$data['current']   = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction']   = active_module_url('perolehan/update');
			
		$id = $this->uri->segment(4);
		if($id && $get = $this->perolehan_model->get($id)) {
            $data['dt']['cur_id'] = $get->id;
			$data['dt']['id'] = $get->id;
			$data['dt']['nama'] = $get->nama;
            $data['dt']['npoptkp'] = $get->npoptkp;
            $data['dt']['pengurang'] = $get->pengurang;
            $data['dt']['singkatan'] = $get->singkatan;
			
			$this->load->view('vperolehan_form',$data);
		} else {
			show_404();
		}
	}
	
	public function update() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url('perolehan'));
		}
		$data['current'] = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction'] = active_module_url('perolehan/update');
		$data['dt'] = $this->fpost();
				
		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {	
			$data = array(
                'id' => $this->input->post('id'),
				'nama' => $this->input->post('nama'),
                'npoptkp' => $this->input->post('npoptkp'),
                'pengurang' => $this->input->post('pengurang'),
                'singkatan' => $this->input->post('singkatan'),
			);
			$this->perolehan_model->update($this->input->post('cur_id'), $data);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');
			redirect(active_module_url('perolehan'));
		}
		$this->load->view('vperolehan_form',$data);
	}
	
	public function delete() {
		if(!$this->module_auth->delete) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_delete);
			redirect(active_module_url('perolehan'));
		}
		
		$id = $this->uri->segment(4);
		if($id && $this->perolehan_model->get($id)) {
			$this->perolehan_model->delete($id);
			$this->session->set_flashdata('msg_success', 'Data telah dihapus');
			redirect(active_module_url('perolehan'));
		} else {
			show_404();
		}
	}
}