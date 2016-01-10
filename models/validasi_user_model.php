<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Validasi_user_model extends CI_Model {
	private $tbl = 'bphtb.bphtb_validasi_user';
	
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
    
    function get_level() {        
        $user_id = $this->session->userdata('userid');
        $sql = "select vu.user_id, vu.level_id, lv.level, lv.min_value, lv.max_value 
            from bphtb_validasi_level lv
            inner join bphtb_validasi_user vu on vu.level_id=lv.id
            where vu.user_id={$user_id}";
            
		$query = $this->db->query($sql);
		if($query->num_rows()!==0)
			return $query->row();
		else {
            $this->db->select("0 as user_id, id as level_id, level, min_value, max_value", false);
            $this->db->where('level',1); // level 1
            $query2 = $this->db->get('bphtb_validasi_level');
            return $query2->row();
        }
    }
    
	function get_user($level_id, $in_group=FALSE) {	
		$sql = "select vu.* from (
					select 1 in_group, u.*, ".$level_id." level_id
					from users u
					inner join bphtb_validasi_user vu on vu.user_id=u.id
					where level_id=".$level_id."
					union
					select coalesce((select 2 from bphtb_validasi_user where user_id=u.id),0) as in_group, u.*,".$level_id." level_id
					from users u
					where u.id not in (select user_id from bphtb_validasi_user where level_id=".$level_id.")
                    /*
                    union
                    select 2 as in_group, u.*,".$level_id." level_id
                    from users u
                    where u.id in (select user_id from bphtb_validasi_user where level_id<>".$level_id.")
                    */
				) as vu
                inner join user_groups ug on vu.id=ug.user_id
                inner join groups g on g.id=ug.group_id
                where vu.userid not in (select userid from bphtb_user) -- yg bukan user ppat
                and g.kode='bphtb' -- yg group BPHTB aja
				".($in_group? " and in_group=1 ": "")."
				order by in_group desc, disabled desc, vu.nama ";
                
		$query = $this->db->query($sql);
		if($query->num_rows()!==0)
		{
			return $query->result();
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