<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ppat_user_model extends CI_Model {
	private $tbl = 'bphtb.bphtb_user';
	
	function __construct() {
		parent::__construct();
	}
		
	function get_user($ppat_id) //($userid)
	{
		$sql = "
				--free to select
				select 0 in_group, ppat.*
				from (select u.id u_id, u.userid, u.nama, g.id g_id, g.nama g_nm 
                    from users u 
					inner join user_groups ug on u.id=ug.user_id 
					inner join groups g on ug.group_id=g.id
					where g.kode='ppat'
					order by u.nama
				) as ppat 
				where ppat.userid not in (select userid from bphtb_user)

				union 
				--in selected ppat
				select 1 in_group, ppat.*
				from (select u.id u_id, u.userid, u.nama, g.id g_id, g.nama g_nm 
                    from users u 
					inner join user_groups ug on u.id=ug.user_id 
					inner join groups g on ug.group_id=g.id
					where g.kode='ppat'
					order by u.nama
				) as ppat 
				inner join bphtb_user bu on ppat.userid=bu.userid
				where bu.ppat_id={$ppat_id}

				union 
				--already in other ppat
				select 2 in_group, ppat.*
				from (select u.id u_id, u.userid, u.nama, g.id g_id, g.nama g_nm from 
                    users u 
					inner join user_groups ug on u.id=ug.user_id 
					inner join groups g on ug.group_id=g.id
					where g.kode='ppat'
					order by u.nama
				) as ppat 
				where ppat.userid in (select userid from bphtb_user) 
				and  ppat.userid not in (select userid from bphtb_user where ppat_id={$ppat_id})
			";
			
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

    function getkode($kode){
		$this->db->join('bphtb.bphtb_ppat','bphtb_ppat.id=bphtb_user.ppat_id');
		$this->db->where('bphtb.bphtb_user.userid',$kode);
		$query = $this->db->get($this->tbl);
		if($query->num_rows()!==0)
		{
			return $query->row();
		}
		else
			return FALSE;
	}
    
  function get_id_nama($kd)
	{
		$this->db->where('kode',$kd);
		$query = $this->db->get($this->tbl);
		if($query->num_rows()!==0)
		{
            $d = $query->row();
			return array($d->id, $d->nama);
		}
		else
			return array(0, '');
	}
    
 }

/* End of file _model.php */