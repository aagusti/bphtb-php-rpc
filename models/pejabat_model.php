<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pejabat_model extends CI_Model {
	private $tbl = 'bphtb.bphtb_pejabat';
	
	function __construct() {
		parent::__construct();
	}
		
	function get_all()
	{
        $sql = "select p.*, j.nm_jabatan
				from bphtb.bphtb_pejabat p inner join ref_jabatan j on p.kd_jabatan=j.kd_jabatan 
				order by p.kd_jabatan, p.id";
				
		$query = $this->db->query($sql);
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