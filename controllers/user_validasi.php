<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_validasi extends CI_Controller {

    private $module = 'referensi';
    private $controller = 'user_validasi';
    private $current = 'referensi';
    
	function __construct() {
		parent::__construct();
		if(!is_login()) {
			$this->session->set_flashdata('msg_warning', 'Session telah kadaluarsa, silahkan login ulang.');
			redirect('login');
			exit;
		}

        $this->load->library('module_auth', array(
            'module' => $this->module
        ));
        
		$this->load->model(array('apps_model'));
		$this->load->model(array('groups_model'));
		
        $this->load->model(array('users_model', 'validasi_level_model', 'validasi_user_model'));
	}

	public function index() {
        if (!$this->module_auth->read) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
            redirect(active_module_url(''));
        }

		$data['current'] = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$this->load->view('vuser_validasi', $data);
	}
	
	function grid() {
        $responce = new stdClass();
        
		$i=0;
		$query = $this->validasi_level_model->get_all();
		if($query) {
			foreach($query as $row) {
				$responce->aaData[$i][]=$row->id;
                $responce->aaData[$i][]=$row->uraian;
				$responce->aaData[$i][]=number_format($row->min_value,0,',','.');
				$responce->aaData[$i][]=number_format($row->max_value,0,',','.');
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
	
	function grid2() {
		$level_id = $this->uri->segment(4);
		$responce = new stdClass();
        
		$i=0;
		if($level_id && $query = $this->validasi_user_model->get_user($level_id)) { //, $ingroup)) {
			foreach($query as $row) {
				$responce->aaData[$i][]=$row->id;
				if ($row->in_group==2)
					$responce->aaData[$i][]='';
				else
					$responce->aaData[$i][]='<input type="checkbox" onchange="update_stat(\''.$row->id.'\','.$level_id.',this.checked);" name="ingroup" '.($row->in_group?'checked':'').'>';
				$responce->aaData[$i][]=$row->nama;
				$responce->aaData[$i][]=$row->jabatan;
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

	function update_stat() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url($this->controller));
		}
      
		$user_id  = $this->uri->segment(4);
		$level_id = $this->uri->segment(5);
		$val      = $this->uri->segment(6);
        
		if($val==0) {
            $this->db->where('user_id', $user_id);
            $this->db->where('level_id',  $level_id);			
            $this->db->delete('bphtb_validasi_user');
		} else {			
			$data = array('user_id' => $user_id, 'level_id' => $level_id);
			$this->db->insert('bphtb_validasi_user',$data);
		}
	}
}
