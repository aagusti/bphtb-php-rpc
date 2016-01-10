<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Berkas_in_model extends CI_Model {
	private $tbl = 'bphtb.bphtb_berkas_in';
    
	function __construct() {
		parent::__construct();
	}
	
	function get_all()
	{
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
        $sql = "select bi.* 
            from bphtb_berkas_in bi
            inner join bphtb_berkas_in_det bid on bid.berkas_in_id=bi.id
            where bid.sspd_id = ?";
		$query = $this->db->query($sql, array($sspd_id));
		if($query->num_rows()!==0)
		{
			return $query->row();
		}
		else
			return FALSE;
	}
    
    function get_berkasno($id)
	{
        $sql = "select get_berkasmasukno(id) as berkasno
                from bphtb_berkas_in 
                where id=?";
                
        $query = $this->db->query($sql, array($id));
		if($query->num_rows()!==0)
		{
			return $query->row()->berkasno;
		}
		else
			return FALSE;
	}
    
	function get_new_no_urut($thn, $kode) {        
        $sql = "select tahun, kode, no_urut 
                from bphtb_berkas_in 
                where tahun={$thn} and kode='{$kode}' 
                order by no_urut desc limit 1";
                
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $result = $query->result();
            return array(
                $thn,
                $kode,
                (double) $result[0]->no_urut + 1
            );
        } else {
            return array($thn,$kode,1);
        }
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