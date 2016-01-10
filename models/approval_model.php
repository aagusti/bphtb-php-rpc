<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Approval_model extends CI_Model {
	private $tbl = 'bphtb.bphtb_approval';
        
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

    function batal($ap_final_id) {
        $sql = "select id from bphtb_approval
                where validasi_id = (select validasi_id
                    from bphtb_approval 
                    where id=(select approval_id from bphtb_approval_final where id = {$ap_final_id}))";

        $query = $this->db->query($sql);
        foreach ($query->result() as $row) {
            //delete di bphtb_approval
            $this->delete($row->id);
        }
                
        //delete di bphtb_approval_final
		$this->db->where('id', $ap_final_id);
		$this->db->delete('bphtb_approval_final');
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