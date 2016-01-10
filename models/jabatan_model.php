<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jabatan_model extends CI_Model {
	private $tbl = 'ref_jabatan';
	
	function __construct() {
		parent::__construct();
	}
		
	function get_all()
	{
		//$this->db->order_by('kd_jabatan'); 
		$query = $this->db->get($this->tbl);
		if($query->num_rows()!==0)
		{
			return $query->result();
		}
		else
			return FALSE;
	}
	
	function get($id)
	{
		$this->db->where('kd_jabatan',$id);
		$query = $this->db->get($this->tbl);
		if($query->num_rows()!==0)
		{
			return $query->row();
		}
		else
			return FALSE;
	}
	
	//-- admin
	function save($data) {
		$this->db->insert($this->tbl,$data);
	}
	
	function update($id, $data) {
		$this->db->where('kd_jabatan', $id);
		$this->db->update($this->tbl,$data);
	}
	
	function delete($id) {
		$this->db->where('kd_jabatan', $id);
		$this->db->delete($this->tbl);
	}
}

/* End of file _model.php */