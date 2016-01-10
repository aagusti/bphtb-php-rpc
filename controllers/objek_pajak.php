<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Objek_pajak extends CI_Controller 
{
    private $module = 'objek_pajak';
    private $controller = 'objek_pajak';
    private $current = 'objek_pajak';
    
    function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            $this->session->set_flashdata('msg_warning', 'Session telah kadaluarsa, silahkan login ulang.');
            redirect('login');
            exit;
        }
        $this->load->helper('ws_helper');
        $this->load->library('module_auth', array(
            'module' => $this->module
        ));
        
        $this->load->model(array(
            'apps_model',
            'bphtb_model',
            'ws_model'
        ));
        
		$this->load->helper(active_module());
    }

	public function index()
	{
    if (!$this->module_auth->read) {
        $this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
        redirect(active_module_url(''));
    }

    $data['current'] = $this->current;
    $data['apps']    = $this->apps_model->get_active_only();
    
    $nop = isset($_GET['nop']) ? $_GET['nop'] : '';
		$data['nop'] = $nop;
    $data_source = array();
    if (PBB_USE_RPC){
        $result = ws_info_op($nop);
        if ($result['result']['code']==0){
          $data_source = $result['result']['params']['data'];
        };
    }else{
        $data_source = $this->bphtb_model->informasi_objek_pajak($nop);
		}
    $data['data_source'] = $data_source;
		$this->load->view('vobjek_pajak',$data);
	}	
	
	
}
