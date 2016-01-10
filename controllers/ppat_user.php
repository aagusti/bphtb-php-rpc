<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ppat_user extends CI_Controller {

	function __construct() {
		parent::__construct();
		if(!is_login()) {
			$this->session->set_flashdata('msg_warning', 'Session telah kadaluarsa, silahkan login ulang.');
			redirect('login');
			exit;
		}
		
		$this->load->model(array('apps_model'));
		$this->load->model(array('groups_model'));
		
        $this->load->model(array('ppat_model', 'users_model', 'ppat_user_model'));
	}

	public function index() {
		$data['current'] = 'referensi';
		$data['apps']    = $this->apps_model->get_active_only();
		$this->load->view('vppat_user', $data);
	}
	
	function grid() {
		$i=0;
        $responce = new stdClass();
        
		//cek isppat
		$query = $this->ppat_model->get_all();
		if($ppat = $this->ppat_user_model->getkode($this->session->userdata('uid'))) {
			$ppat_id  = $this->ppat_model->get_id_nama($ppat->kode);
			$ppat_id  = $ppat_id[0];
			$query = array($this->ppat_model->get($ppat_id));
		}
		
		if($query) {
			foreach($query as $row) {
				$responce->aaData[$i][]=$row->id;
                $responce->aaData[$i][]=$row->kode;
				$responce->aaData[$i][]=$row->nama;
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
		$ppat_id = $this->uri->segment(4);
		// $in = $this->uri->segment(5);
		// $ingroup = $in? true : false;
		
		$i=0;
        $responce = new stdClass();
		if($ppat_id && $query = $this->ppat_user_model->get_user($ppat_id)) { //, $ingroup)) {
			foreach($query as $row) {
				$responce->aaData[$i][]=$row->u_id;
				if ($row->in_group==2)
					$responce->aaData[$i][]='';
				else
					$responce->aaData[$i][]='<input type="checkbox" onchange="update_stat(\''.$row->userid.'\','.$ppat_id.',this.checked);" name="ingroup" '.($row->in_group?'checked':'').'>';
				$responce->aaData[$i][]=$row->userid;
				$responce->aaData[$i][]=$row->nama;
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
		$userid = $this->uri->segment(4);
		$ppat_id = $this->uri->segment(5);
		$val = $this->uri->segment(6);
		if($val==0) {
			if($uid==sipkd_user_id() && $gid==sipkd_group_id()) {
				// ga bisa
			} else {				
				$this->db->where('userid', $userid);
				$this->db->where('ppat_id',  $ppat_id);			
				$this->db->delete('bphtb_user');
			}
		} else {			
			$data = array('userid' => $userid, 'ppat_id' => $ppat_id);
			$this->db->insert('bphtb_user',$data);
		}
	}
}
