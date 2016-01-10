<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Penerimaan_model extends CI_Model {
	private $tbl = 'bphtb.bphtb_bank';
	
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
	
	function get_by_sspd($sspd_id)
	{
		$this->db->where('bayar > 0');
		$this->db->where('sspd_id',$sspd_id);
		$query = $this->db->get($this->tbl);
		if($query->num_rows()!==0)
		{
			return $query->row();
		}
		else
			return FALSE;
	}
	
    function get_nop($id, $formated=true)
	{
        $formated = ($formated) ? 'true' : 'false';
        $sql = "select get_nop_bank(id, {$formated}) as nopthn
                from bphtb_bank
                where id=?";
                
        $query = $this->db->query($sql, array($id));
		if($query->num_rows()!==0)
		{
			return $query->row()->nopthn;
		}
		else
			return FALSE;
	}
    
    function get_nopthn($id)
	{
        $sql = "select get_nop_thn_bank(id, true) as nopthn
                from bphtb_bank 
                where id=?";
                
        $query = $this->db->query($sql, array($id));
		if($query->num_rows()!==0)
		{
			return $query->row()->nopthn;
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