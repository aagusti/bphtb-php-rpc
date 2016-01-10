<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ppat extends CI_Controller {

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
        $this->load->model(array('ppat_model'));
        $this->load->model(array('pejabat_model'));
	}

	public function index() {
		if(!$this->module_auth->read) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
			redirect(active_module_url(''));
		}

		$data['current'] = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$this->load->view('vppat', $data);
	}
	
	function grid() {
		$i=0;
		$responce = new stdClass();
		if($query = $this->ppat_model->get_all()) {
			foreach($query as $row) {
				$responce->aaData[$i][]=$row->id;
                $responce->aaData[$i][]= '';
                $responce->aaData[$i][]=$row->kode;
				$responce->aaData[$i][]=$row->nama;
                $responce->aaData[$i][]=$row->alamat;
                $responce->aaData[$i][]=$row->wilayah_kerja;
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
        $this->form_validation->set_rules('kode','Kode','required|trim|max_length[6]');
        $this->form_validation->set_rules('nama','Nama','required|trim|max_length[50]');
        $this->form_validation->set_rules('no_sk','SK','required|trim|max_length[30]');
        
        $this->form_validation->set_rules('alamat','alamat','trim|max_length[50]');
        $this->form_validation->set_rules('kelurahan','kelurahan','trim|max_length[50]');
        $this->form_validation->set_rules('kecamatan','kecamatan','trim|max_length[50]');
        $this->form_validation->set_rules('kota','kota','trim|max_length[50]');
        $this->form_validation->set_rules('wilayah_kerja','wilayah_kerja','trim|max_length[50]');
        $this->form_validation->set_rules('kd_wilayah','kd_wilayah','trim|max_length[4]');
        $this->form_validation->set_rules('no_telp','no_telp','trim|max_length[20]');
        $this->form_validation->set_rules('no_fax','no_fax','trim|max_length[20]');
        $this->form_validation->set_rules('npwp','npwp','trim|max_length[20]');
	}
	
	private function fpost() {        
        $data['id'] = $this->input->post('id');
        $data['kode'] = $this->input->post('kode');
		$data['nama'] = $this->input->post('nama');
        $data['alamat'] = $this->input->post('alamat');
        $data['kelurahan'] = $this->input->post('kelurahan');
        $data['kecamatan'] = $this->input->post('kecamatan');
        $data['kota'] = $this->input->post('kota');
        $data['wilayah_kerja'] = $this->input->post('wilayah_kerja');
        $data['kd_wilayah'] = $this->input->post('kd_wilayah');
        $data['no_telp'] = $this->input->post('no_telp');
        $data['no_fax'] = $this->input->post('no_fax');
        $data['no_sk'] = $this->input->post('no_sk');
        $data['tgl_sk'] = $this->input->post('tgl_sk');
        $data['npwp'] = $this->input->post('npwp');
        $data['created'] = $this->input->post('created');
        $data['create_uid'] = $this->input->post('create_uid');
		$data['updated'] = $this->input->post('updated');
        $data['update_uid'] = $this->input->post('update_uid');
        $data['pejabat_id'] = $this->input->post('pejabat_id');
        
		return $data;
	}
	
	public function add() {
		if(!$this->module_auth->create) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_create);
			redirect(active_module_url('ppat'));
		}
		$data['current']     = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction']     = active_module_url('ppat/add');
		$data['pejabat']     = $this->pejabat_model->get_all();
		$data['dt']          = $this->fpost();
		
		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {
			$data = array(
                'kode' => $this->input->post('kode'),
				'nama' => $this->input->post('nama'),
                'alamat' => $this->input->post('alamat'),
                'kelurahan' => $this->input->post('kelurahan'),
                'kecamatan' => $this->input->post('kecamatan'),
                'kota' => $this->input->post('kota'),
                'wilayah_kerja' => $this->input->post('wilayah_kerja'),
                'kd_wilayah' => $this->input->post('kd_wilayah'),
                'no_telp' => $this->input->post('no_telp'),
                'no_fax' => $this->input->post('no_fax'),
                'no_sk' => $this->input->post('no_sk'),
                'tgl_sk' => strtotime($this->input->post('tgl_sk')) ? date('Y-m-d', strtotime($this->input->post('tgl_sk'))) : NULL,
                'npwp' => $this->input->post('npwp'),
                'created' => date('Y-m-d'),
                'create_uid' => $this->session->userdata('username'),
                'pejabat_id' => empty($data['dt']['pejabat_id'])? null : $this->input->post('pejabat_id'),
			);
			$this->ppat_model->save($data);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');		
			redirect(active_module_url('ppat'));
		}
		$this->load->view('vppat_form',$data);
	}
	
	public function edit() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url('ppat'));
		}
		$data['current']   = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction']   = active_module_url('ppat/update');
		$data['pejabat']     = $this->pejabat_model->get_all();
			
		$id = $this->uri->segment(4);
		if($id && $get = $this->ppat_model->get($id)) {
			$data['dt']['id'] = $get->id;
			$data['dt']['kode'] = $get->kode;
            $data['dt']['nama'] = $get->nama;
            $data['dt']['alamat'] = $get->alamat;
            $data['dt']['kelurahan'] = $get->kelurahan;
            $data['dt']['kecamatan'] = $get->kecamatan;
            $data['dt']['kota'] = $get->kota;
            $data['dt']['wilayah_kerja'] = $get->wilayah_kerja;
            $data['dt']['kd_wilayah'] = $get->kd_wilayah;
            $data['dt']['no_telp'] = $get->no_telp;
            $data['dt']['no_fax'] = $get->no_fax;
            $data['dt']['no_sk'] = $get->no_sk;
            $data['dt']['tgl_sk'] = $get->tgl_sk ? date('d-m-Y', strtotime($get->tgl_sk)) : '';
            $data['dt']['npwp'] = $get->npwp;
            $data['dt']['updated'] = $get->updated;
            $data['dt']['update_uid'] = $get->update_uid;
            $data['dt']['pejabat_id'] = $get->pejabat_id;
			
			$this->load->view('vppat_form',$data);
		} else {
			show_404();
		}
	}
	
	public function update() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url('ppat'));
		}
		$data['current'] = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$data['faction'] = active_module_url('ppat/update');
		$data['pejabat']     = $this->pejabat_model->get_all();
		$data['dt'] = $this->fpost();
				
		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {	
			$data = array(
                'kode' => $this->input->post('kode'),
				'nama' => $this->input->post('nama'),
                'alamat' => $this->input->post('alamat'),
                'kelurahan' => $this->input->post('kelurahan'),
                'kecamatan' => $this->input->post('kecamatan'),
                'kota' => $this->input->post('kota'),
                'wilayah_kerja' => $this->input->post('wilayah_kerja'),
                'kd_wilayah' => $this->input->post('kd_wilayah'),
                'no_telp' => $this->input->post('no_telp'),
                'no_fax' => $this->input->post('no_fax'),
                'no_sk' => $this->input->post('no_sk'),
                'tgl_sk' => strtotime($this->input->post('tgl_sk')) ? date('Y-m-d', strtotime($this->input->post('tgl_sk'))) : NULL,
                'npwp' => $this->input->post('npwp'),
                'updated' => date('Y-m-d'),
                'update_uid' => $this->session->userdata('username'),
                'pejabat_id' => empty($data['dt']['pejabat_id'])? null : $this->input->post('pejabat_id'),
			);
			$this->ppat_model->update($this->input->post('id'), $data);
			
			$this->session->set_flashdata('msg_success', 'Data telah disimpan');
			redirect(active_module_url('ppat'));
		}
		$this->load->view('vppat_form',$data);
	}
	
	public function delete() {
		if(!$this->module_auth->delete) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_delete);
			redirect(active_module_url('ppat'));
		}
		
		$id = $this->uri->segment(4);
		if($id && $this->ppat_model->get($id)) {
			$this->ppat_model->delete($id);
			$this->session->set_flashdata('msg_success', 'Data telah dihapus');
			redirect(active_module_url('ppat'));
		} else {
			show_404();
		}
	}
}