<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Validasi_level_model extends CI_Model {
	private $tbl = 'bphtb.bphtb_validasi_level';
	
	function __construct() {
		parent::__construct();
	}
		
	function get_all()
	{
		$this->db->order_by('id'); 
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
		$this->db->where('id',$id);
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
		return $this->db->insert_id();
	}
	
	function update($id, $data) {
		$this->db->where('id', $id);
		$this->db->update($this->tbl,$data);
	}
	
	function delete($id) {
		$this->db->where('id', $id);
		$this->db->delete($this->tbl);
	}
    
 }

/* End of file _model.php */