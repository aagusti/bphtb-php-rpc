<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sspd_approval_model extends CI_Model {
	private $tbl = 'bphtb.bphtb_sspd_approval';
        
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
    
    function get_new_approvalno($thn, $kode)
    {
        $sql = "select tahun, kode, no_urut 
                from bphtb_sspd_approval 
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