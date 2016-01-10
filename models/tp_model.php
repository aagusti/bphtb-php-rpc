<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class tp_model extends CI_Model {
	private $tbl = 'tempat_pembayaran';
	
	function __construct() {
		parent::__construct();
	}
	
    function pos_field() {
        $fields     = explode(',', POS_FIELD);
        $pos_fld    = ''; 
        $pos_join   = ''; 
        $pos_kode   = ''; 
        $fs='';
        foreach ($fields as $f) {
            $fs = $f;
            if ($f == 'kd_kanwil_bank')
                $fs = 'kd_kanwil';
            else if ($f == 'kd_kppbb_bank')
                $fs = 'kd_kppbb';
            
            $pos_fld  .= "{$fs},";
            $pos_kode .= "{$fs}||";
            $pos_join .= "up.{$fs}=tp.{$fs} and ";
        }
        $pos_join = substr($pos_join, 0, -4);
        $pos_kode = substr($pos_kode, 0, -2);
        $pos_fld  = substr($pos_fld , 0, -1);
        
        $ret = new stdClass();
        $ret->pos_fld  = $pos_fld;
        $ret->pos_kode = $pos_kode;
        $ret->pos_join = $pos_join;
        return $ret;
    }
    
	function get_all() {
        $kode = $this->pos_field()->pos_kode;
        $sql = "select *, {$kode} as kode
				from tempat_pembayaran
                order by nm_tp ";
		
        $this->db->trans_start();
        $query = $this->db->query($sql);
        $this->db->trans_complete();
        
        if($this->db->trans_status() && $query->num_rows()>0)
            return $query->result();
        else
            return false;
	}
		
	function get($id)
	{
        $kode = $this->pos_field()->pos_kode;
        $sql = "select *, {$kode} as kode
        from tempat_pembayaran 
        where id = {$id}";
        
        $this->db->trans_start();
        $query = $this->db->query($sql);
        $this->db->trans_complete();
        
        if($this->db->trans_status() && $query->num_rows()>0)
            return $query->row();
        else
            return false;
	}
	
	function get_id($where)
	{
        $fld  = $this->pos_field()->pos_fld;
        $kode = $this->pos_field()->pos_kode;
        $col = "id, nm_tp, alamat_tp, {$fld}, {$kode} as kode";
        
        $this->db->trans_start();
        $query = $this->db->select($col)->from($this->tbl)->get_where($this->tbl, $where); 
        $this->db->trans_complete();
        
        if($this->db->trans_status() && $query->num_rows()>0)
            return $query->row();
        else
            return false;
	}
	
	//-- admin
	function save($data) {
        $this->db->trans_start();
        $this->db->insert($this->tbl,$data);
        $this->db->trans_complete();
        if($this->db->trans_status())
            return true;
        else
            return false;
	}
	
	function update($id, $data) {
    $this->db->trans_start();
    $this->db->where('id', $id);
    
    $this->db->update($this->tbl,$data);

    $this->db->trans_complete();
        
    if($this->db->trans_status())
        return true;
    else
        return false;
	}
	
	function delete($data) {
        $this->db->trans_start();
        $this->db->where('id', $data);
        $this->db->delete($this->tbl);
        $this->db->trans_complete();
            
        if($this->db->trans_status())
            return true;
        else
            return false;
	}
}

/* End of file _model.php */