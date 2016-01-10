<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class bphtb_bogor extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        if (active_module() != 'bphtb_bogor') {
            show_404();
            exit;
        }
        
        $this->load->model('login_model');
        if ($grp = $this->login_model->check_user_app()) {
            $this->session->set_userdata('groupid', $grp->group_id);
            $this->session->set_userdata('groupkd', $grp->group_kode);
            $this->session->set_userdata('groupname', $grp->group_nama);
        }
        
        $this->load->model(array(
            'apps_model',
            'bphtb_model',
            'ppat_model',
            'ppat_user_model',
        ));
        
		//cek is user ppat
		$query = $this->ppat_model->get_all();
		if($is_ppat = $this->ppat_user_model->getkode($this->session->userdata('uid'))) {
			$ppat  = $this->ppat_model->get_id_nama($is_ppat->kode);
            
            $this->session->set_userdata('isppat', true);
            $this->session->set_userdata('ppat_id', $ppat[0]);
		}
		
		$this->load->helper(active_module());
    }
    
    public function index()
    {
        $data['current'] = 'beranda';
        $data['apps']    = $this->apps_model->get_active_only();
             
        
        $this->load->view('vmain', $data);
    }
    
    public function get_realisasi() {
        $r                   = $this->bphtb_model->realisasi_dashboard();
        $data['tahun']       = date('Y');
        $data['amt_daily']   = number_format($r->amt_daily, 0, ',', '.');
        $data['amt_weekly']  = number_format($r->amt_weekly, 0, ',', '.');
        $data['amt_monthly'] = number_format($r->amt_monthly, 0, ',', '.');
        $data['amt_yearly']  = number_format($r->amt_yearly, 0, ',', '.');
        echo json_encode($data);
    }
    
}
