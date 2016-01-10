<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Bphtb_model_x extends CI_Model
{
    
    private $tbl = array('alasan' => 'bphtb_alasan_pengurang', 'dasar' => 'bphtb_dasar', 'hukum' => 'bphtb_hukum', 'pengalihan' => 'bphtb_perolehan', 'ppat' => 'bphtb_ppat', 'status_hak' => 'bphtb_status_hak', 'jabatan' => 'ref_jabatan', 'pejabat' => 'bphtb_pejabat', 'propinsi' => 'ref_propinsi', 'dati2' => 'ref_dati2', 'kecamatan' => 'ref_kecamatan', 'kelurahan' => 'ref_kelurahan', 'tempat_bayar' => 'tempat_pembayaran', 'dop' => 'dat_objek_pajak');
    
    private $qry_opr = array('or', 'and', 'between');
    
    private $berkas_in = "SELECT 
                          validasi.id, bkin.id AS berkas_in_id, bkin.tahun AS berkas_tahun, bkin.kode AS berkas_kode, 
                          bkin.no_urut AS berkas_no_urut, bkin.sspd_id, bkin.is_nihil, 
                          sspd.tahun AS sspd_tahun, sspd.kode AS sspd_kode, sspd.no_sspd, sspd.ppat_id, 
                          ppat.kode AS ppat_kode, ppat.nama AS nama_ppat, sspd.wp_nama, sspd.wp_npwp, 
                          sspd.wp_alamat, sspd.wp_blok_kav, sspd.wp_kelurahan, sspd.wp_rt, sspd.wp_rw, 
                          sspd.wp_kecamatan, sspd.wp_kota, sspd.wp_provinsi, sspd.wp_identitas, 
                          sspd.wp_identitaskd, sspd.tgl_transaksi, sspd.kd_propinsi, sspd.kd_dati2, 
                          sspd.kd_kecamatan, sspd.kd_kelurahan, sspd.kd_blok, sspd.no_urut, 
                          sspd.kd_jns_op, sspd.thn_pajak_sppt, sspd.op_alamat, sspd.op_blok_kav, 
                          sspd.op_rt, sspd.op_rw, sspd.bumi_luas, sspd.bumi_njop, sspd.bng_luas, 
                          sspd.bng_njop, sspd.no_sertifikat, sspd.njop, sspd.perolehan_id, 
                          sspd.npop, sspd.npoptkp, sspd.tarif, sspd.terhutang, sspd.bagian, 
                          sspd.pembagi, sspd.tarif_pengurang, sspd.pengurang, sspd.bphtb_sudah_dibayarkan, 
                          sspd.denda, sspd.restitusi, sspd.bphtb_harus_dibayarkan, 
                          sspd.status_pembayaran, sspd.dasar_id, sspd.header_id,
                          cast(sspd.kd_propinsi || '.' || sspd.kd_dati2 || '.' || sspd.kd_kecamatan || '.' || 
                          sspd.kd_kelurahan || '.' || sspd.kd_blok || '-' || sspd.no_urut || '.' || sspd.kd_jns_op as varchar) as nomor_op,
                          cast(bkin.tahun || '-' || bkin.kode || '-' || bkin.no_urut as varchar) as nomor_berkas,
                          cast(sspd.tahun || '-' || sspd.kode || '-' || sspd.no_sspd as varchar) as nomor_sspd,
                          sspd.file1, sspd.file2, sspd.file3, sspd.file4, sspd.file5
                        FROM 
                          bphtb_berkas_in bkin inner join bphtb_sspd sspd on bkin.sspd_id = sspd.id 
                          inner join bphtb_ppat ppat on sspd.ppat_id = ppat.id
                          left join bphtb_validasi validasi on validasi.berkas_in_id = bkin.id ";
    
    function __construct()
    {
        parent::__construct();
    }
    
    //-- admin
    function save($data, $tblnm)
    {
        $this->db->trans_start();
        $this->db->insert($tblnm, $data);
        $this->db->trans_complete();
        
        if ($this->db->trans_status())
            return $this->db->insert_id() ? $this->db->insert_id() : true;
        else
            return false;
    }
    
    function update($where, $data, $tblnm)
    {
        $this->db->trans_start();
        $this->db->where($where);
        $this->db->update($tblnm, $data);
        $this->db->trans_complete();
        
        if ($this->db->trans_status())
            return $this->db->affected_rows() ? $this->db->affected_rows() : true;
        else
            return false;
    }
    
    function delete($where, $tblnm)
    {
        $this->db->trans_start();
        $this->db->where($where);
        $this->db->delete($tblnm);
        $this->db->trans_complete();
        
        if ($this->db->trans_status())
            return $this->db->affected_rows() ? $this->db->affected_rows() : true;
        else
            return false;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    private function is_field_tahun($fld)
    {
        return ((strpos($fld, 'thn') > 0) || (strpos($fld, 'tahun') > 0) || ($fld == 'tahun'));
    }
    
    function fields_info($tbl)
    {
        if (!$tbl) {
            return FALSE;
        }
        $sql   = "select * from INFORMATION_SCHEMA.COLUMNS where table_name = '$tbl' order by ordinal_position";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $retval = new stdClass;
            $res    = $query->result();
            foreach ($res as $row) {
                if ((!$row->character_maximum_length) && ($row->numeric_precision && $row->numeric_precision_radix) && ($row->numeric_precision_radix == 2)) {
                    $max_length = floor($row->numeric_precision / $row->numeric_precision_radix); //floor(sqrt($row->numeric_precision));
                } else {
                    $max_length = abs($row->character_maximum_length ? (int) $row->character_maximum_length : 0);
                }
                if ($this->is_field_tahun($row->column_name)) {
                    $max_length = ($max_length > 4 ? 4 : $max_length);
                }
                $retval->{$row->column_name} = new stdClass;
                $retval->{$row->column_name} = (object) array(
                    'name' => $row->column_name,
                    'type' => $row->udt_name,
                    'max_length' => $max_length
                );
            }
            return $retval;
        } else {
            return FALSE;
        }
    }
    
    private function replace_me($xdata)
    {
        $src  = array(
            "\\",
            "\n",
            "\r",
            "'"
        );
        $with = array(
            "\\\\",
            "\\n",
            "\\r",
            "\'"
        );
        return str_replace($src, $with, $xdata);
    }
    
    function get_user($id = 0)
    {
        $sql   = "select * from users where id=$id ";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }
    
    function get_alasan($id = 0)
    {
        $sql = "select * from " . $this->tbl['alasan'];
        if ($id) {
            $sql .= " where id = " . (string) $id;
        }
        $sql .= " order by id";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
    
    function get_dasar_perhitungan($id = 0)
    {
        $sql = "select * from " . $this->tbl['dasar'];
        if ($id) {
            $sql .= " where id = " . (string) $id;
        }
        $sql .= " order by id";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
    
    function get_hukum($id = 0)
    {
        $sql = "select * from " . $this->tbl['hukum'];
        if ($id) {
            $sql .= " where id = " . (string) $id;
        }
        $sql .= " order by id";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
    
    function get_perolehan($id = 0)
    {
        $sql = "select * from " . $this->tbl['pengalihan'];
        if ($id) {
            $sql .= " where id = " . (string) $id;
        }
        $sql .= " order by id";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
    
    function get_ppat($id = 0)
    {
        $sql = "select * from " . $this->tbl['ppat'];
        if ($id) {
            $sql .= " where id = " . (string) $id;
        }
        $sql .= " order by id";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
    
    function get_ppat_nama($id)
    {
        if ($id == '') {
            return '';
        }
        $sql = "select * from " . $this->tbl['ppat'];
        $sql .= " where id = $id ";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $ret = $query->row();
            return $ret->nama;
        } else {
            return '';
        }
    }
    
    function get_status_hak($id = 0)
    {
        $sql = "select * from " . $this->tbl['status_hak'];
        if ($id) {
            $sql .= " where id = " . (string) $id;
        }
        $sql .= " order by id";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
    
    function get_jabatan($kd = '')
    {
        $sql = "select * from " . $this->tbl['jabatan'];
        if ($kd !== '') {
            $sql .= " where kd_jabatan = '" . $this->replace_me($kd) . "'";
        }
        $sql .= " order by kd_jabatan";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
    
    function get_pejabat($id = 0, $nip = '', $opr = 'or')
    {
        $sql = "select * from " . $this->tbl['pejabat'];
        if ($id) {
            $sql .= " where id = " . (string) $id;
        }
        if ($nip !== '') {
            if ($id) {
                if (in_array($opr, $this->qry_opr)) {
                    $sql .= " " . $opr . " nip like '" . replace_me($nip) . "%' ";
                }
            } else {
                $sql .= " where nip like '" . replace_me($nip) . "%' ";
            }
        }
        $sql .= " order by jabatan_kd, id";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
    
    function get_propinsi($kd = '')
    {
        $sql = "select * from " . $this->tbl['propinsi'];
        if ($kd !== '') {
            $sql .= " where kd_propinsi = '" . $this->replace_me($kd) . "'";
        }
        $sql .= " order by kd_propinsi";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
    
    function get_propinsi_nama($kd)
    {
        if ($kd == '') {
            return '';
        }
        $sql = "select * from " . $this->tbl['propinsi'];
        $sql .= " where kd_propinsi = '" . $this->replace_me($kd) . "'";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $ret = $query->row();
            return $ret->nm_propinsi;
        } else {
            return '';
        }
    }
    
    function get_dati2($kp = '', $kd = '')
    {
        $sql = "select * from " . $this->tbl['dati2'];
        if ($kp !== '') {
            $sql .= " where kd_propinsi = '" . $this->replace_me($kp) . "' ";
        }
        if ($kd !== '') {
            if ($kp !== '') {
                $sql .= " and kd_dati2 = '" . $this->replace_me($kd) . "' ";
            } else {
                $sql .= " where kd_dati2 = '" . $this->replace_me($kd) . "' ";
            }
        }
        $sql .= " order by kd_propinsi, kd_dati2";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
    
    function get_dati2_nama($kp, $kd)
    {
        if ($kd == '' || $kp == '') {
            return '';
        }
        $sql = "select * from " . $this->tbl['dati2'];
        $sql .= " where kd_propinsi = '" . $this->replace_me($kp) . "' ";
        $sql .= " and kd_dati2 = '" . $this->replace_me($kd) . "' ";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $ret = $query->row();
            return $ret->nm_dati2;
        } else {
            return '';
        }
    }
    
    function get_kecamatan($kp = '', $kd = '', $kc = '')
    {
        $sql = "select * from " . $this->tbl['kecamatan'];
        if ($kp !== '') {
            $sql .= " where kd_propinsi = '" . $this->replace_me($kp) . "' ";
        }
        if ($kd !== '') {
            if ($kp !== '') {
                $sql .= " and kd_dati2 = '" . $this->replace_me($kd) . "' ";
            } else {
                $sql .= " where kd_dati2 = '" . $this->replace_me($kd) . "' ";
            }
        }
        if ($kc !== '') {
            if (($kp !== '') || ($kd !== '')) {
                $sql .= " and kd_kecamatan = '" . $this->replace_me($kc) . "' ";
            } else {
                $sql .= " where kd_kecamatan = '" . $this->replace_me($kc) . "' ";
            }
        }
        $sql .= " order by kd_propinsi, kd_dati2, kd_kecamatan";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
    
    function get_kecamatan_nama($kp, $kd, $kc)
    {
        if ($kd == '' || $kp == '' || $kc == '') {
            return '';
        }
        $sql = "select * from " . $this->tbl['kecamatan'];
        $sql .= " where kd_propinsi = '" . $this->replace_me($kp) . "' ";
        $sql .= " and kd_dati2 = '" . $this->replace_me($kd) . "' ";
        $sql .= " and kd_kecamatan = '" . $this->replace_me($kc) . "' ";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $ret = $query->row();
            return $ret->nm_kecamatan;
        } else {
            return '';
        }
    }
    
    function get_kelurahan($kp = '', $kd = '', $kc = '', $kl = '')
    {
        $sql = "select * from " . $this->tbl['kelurahan'];
        if ($kp !== '') {
            $sql .= " where kd_propinsi = '" . $this->replace_me($kp) . "' ";
        }
        if ($kd !== '') {
            if ($kp !== '') {
                $sql .= " and kd_dati2 = '" . $this->replace_me($kd) . "' ";
            } else {
                $sql .= " where kd_dati2 = '" . $this->replace_me($kd) . "' ";
            }
        }
        if ($kc !== '') {
            if (($kp !== '') || ($kd !== '')) {
                $sql .= " and kd_kecamatan = '" . $this->replace_me($kc) . "' ";
            } else {
                $sql .= " where kd_kecamatan = '" . $this->replace_me($kc) . "' ";
            }
        }
        if ($kl !== '') {
            if (($kp !== '') || ($kd !== '') || ($kc !== '')) {
                $sql .= " and kd_kelurahan = '" . $this->replace_me($kl) . "' ";
            } else {
                $sql .= " where kd_kelurahan = '" . $this->replace_me($kl) . "' ";
            }
        }
        $sql .= " order by kd_propinsi, kd_dati2, kd_kecamatan, kd_kelurahan";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
    
    function get_kelurahan_nama($kp, $kd, $kc, $kl)
    {
        if ($kd == '' || $kp == '' || $kc == '' || $kl == '') {
            return '';
        }
        $sql = "select * from " . $this->tbl['kelurahan'];
        $sql .= " where kd_propinsi = '" . $this->replace_me($kp) . "' ";
        $sql .= " and kd_dati2 = '" . $this->replace_me($kd) . "' ";
        $sql .= " and kd_kecamatan = '" . $this->replace_me($kc) . "' ";
        $sql .= " and kd_kelurahan = '" . $this->replace_me($kl) . "' ";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $ret = $query->row();
            return $ret->nm_kelurahan;
        } else {
            return '';
        }
    }
    
    function get_sspd()
    {
        $sql = "SELECT sspd.*,
                po.nama as nm_perolehan, po.npoptkp, po.pengurang, 
                ppat.kode, ppat.nama as nm_ppat, ppat.alamat as alamat_ppat, ppat.kelurahan, ppat.kecamatan, 
                ppat.kota, ppat.wilayah_kerja, ppat.kd_wilayah, ppat.npwp,
                cast(sspd.kd_propinsi || '.' || sspd.kd_dati2 || '.' || sspd.kd_kecamatan || '.' || 
                sspd.kd_kelurahan || '.' || sspd.kd_blok || '-' || sspd.no_urut || '.' || sspd.kd_jns_op as varchar) as nomor_op
                FROM 
                bphtb_sspd sspd inner join bphtb_perolehan po on sspd.perolehan_id = po.id
                inner join bphtb_ppat ppat on sspd.ppat_id = ppat.id
                order by id";
        
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else
            return FALSE;
    }
    
    function get_sspd_kode($id)
    {
        $sql   = "SELECT tahun, kode, no_sspd 
                FROM bphtb_sspd where id = " . (($id) ? $id : 0) . " limit 1 ";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $kode = $query->result();
            $ret  = $kode[0]->no_sspd;
            $ret  = '000000' . $ret;
            $ret  = substr($ret, strlen($ret) - 6, 6);
            $ret  = $kode[0]->tahun . '.' . $kode[0]->kode . '.' . $ret;
            return $ret;
        } else
            return '';
    }
    
    function get_new_sspd_kode($thn, $kode)
    {
        $sql   = "select tahun, kode, no_sspd from bphtb_sspd where tahun={$thn} and kode='{$kode}' order by no_sspd desc limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $res = $query->result();
            return array(
                $thn,
                $kode,
                (double) $res[0]->no_sspd + 1
            );
        } else {
            return array(
                $thn,
                $kode,
                1
            );
        }
    }
    
    function get_new_sspd_approval_kode()
    {
        $thn   = date('Y');
        $sql   = "select tahun, kode, no_urut from bphtb_sspd_approval where tahun=$thn order by no_urut desc limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $res = $query->result();
            return array(
                $thn,
                1,
                (double) $res[0]->no_urut + 1
            );
        } else {
            return array(
                $thn,
                1,
                1
            );
        }
    }
    
    function get_new_sk_kode()
    {
        $thn   = date('Y');
        $sql   = "select tahun, kode, no_urut from bphtb_sk where tahun=$thn order by no_urut desc limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $res = $query->result();
            return array(
                $thn,
                1,
                (double) $res[0]->no_urut + 1
            );
        } else {
            return array(
                $thn,
                1,
                1
            );
        }
    }
    
    function create_sk_perubahan($val_id)
    {
        if ((!$val_id) || (double) $val_id < 1) {
            return FALSE;
        }
        $kode   = $this->get_new_sk_kode();
        $skdata = array(
            'tahun' => $kode[0],
            'kode' => $kode[1],
            'no_urut' => $kode[2],
            'validasi_id' => $val_id,
            'created' => date('Y-m-d h:m:s'),
            'create_uid' => $this->session->userdata('uid')
        );
        $this->db->insert('bphtb_sk', $skdata);
        return $this->db->insert_id();
    }
    
    function is_have_lbkb($id)
    {
        if ((double) $id > 0) {
            $sql   = "SELECT id, kode
                    FROM bphtb_sspd where header_id = $id ";
            $query = $this->db->query($sql);
            return ($query->num_rows() !== 0);
        } else {
            return FALSE;
        }
    }
    
    function add_lbkb($data, $val_id)
    {
        if ($data['bphtb_sudah_dibayarkan'] == $data['terhutang']) {
            return FALSE;
        }
        $sql        = "select * from bphtb_sspd where id=0";
        $query      = $this->db->query($sql);
        $sspd_field = $query->list_fields();
        
        $allowed = array(
            'id',
            'tahun',
            'kode',
            'no_sspd',
            'updated',
            'update_uid',
            'tgl_transaksi',
            'created'
        );
        
        $dat = array();
        foreach ($data as $fld => $val) {
            if (in_array($fld, $sspd_field)) {
                if (!in_array($fld, $allowed)) {
                    $dat[$fld] = $val;
                }
            }
        }
        $dat['bphtb_sudah_dibayarkan'] = 0;
        $dat['status_pembayaran']      = 0;
        $kd                            = $this->get_new_sspd_kode();
        $dat['tahun']                  = $kd[0];
        $dat['kode']                   = ($data['bphtb_sudah_dibayarkan'] < $data['terhutang']) ? '2' : '3';
        $dat['no_sspd']                = $kd[2];
        $dat['header_id']              = $data['sspd_id'];
        $dat['created']                = date('Y-m-d h:m:s');
        $dat['tgl_transaksi']          = date('Y-m-d');
        $dat['create_uid']             = $this->session->userdata('uid');
        
        $sspddata = (array) $dat;
        
        $this->db->insert('bphtb_sspd', $sspddata);
        $ret = $this->db->insert_id();
        
        return $ret;
    }
    
    function get_wp($identitas)
    {
        $sql   = "SELECT wp_nama, wp_npwp, wp_alamat, wp_blok_kav, wp_kelurahan, 
                wp_rt, wp_rw, wp_kecamatan, wp_kota, wp_provinsi, 
                wp_identitas, wp_identitaskd, wp_kdpos
                FROM bphtb_sspd where wp_identitas = '$identitas' order by id desc limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->row();
        } else
            return FALSE;
    }
    
    function get_op_sppt($kode, $thn)
    {
        if ((!$kode) || (!$kode)) {
            return FALSE;
        }
        $kodes = explode('.', $kode);
        $sql   = "SELECT (case when op.jalan_op is null or op.jalan_op='' then '-' else op.jalan_op end) as op_alamat, 
                      (case when op.blok_kav_no_op is null or op.blok_kav_no_op='' then '-' else op.blok_kav_no_op end) as op_blok_kav, 
                      (case when op.rw_op is null or op.rw_op='' then '-' else op.rw_op end) as op_rw, 
                      (case when op.rt_op is null or op.rt_op='' then '-' else op.rt_op end) as op_rt, 
                      sppt.luas_bumi_sppt, op.total_luas_bumi as bumi_luas, 
                      sppt.njop_bumi_sppt, op.njop_bumi as bumi_njop, 
                      sppt.luas_bng_sppt, op.total_luas_bng as bng_luas, 
                      sppt.njop_bng_sppt, op.njop_bng as bng_njop,
                      sppt.njop_bumi_sppt + sppt.njop_bng_sppt as njop_sppt,
                      op.njop_bumi + op.njop_bng as njop,
                      sppt.thn_pajak_sppt, sppt.nm_wp_sppt, 
                      sppt.jln_wp_sppt, sppt.blok_kav_no_wp_sppt, 
                      sppt.rw_wp_sppt, sppt.rt_wp_sppt, 
                      sppt.kelurahan_wp_sppt, sppt.kota_wp_sppt, 
                      sppt.kd_pos_wp_sppt, sppt.npwp_sppt
                    FROM 
                      dat_objek_pajak op inner join sppt on op.kd_propinsi=sppt.kd_propinsi and 
                      op.kd_dati2=sppt.kd_dati2 and op.kd_kecamatan=sppt.kd_kecamatan and 
                      op.kd_kelurahan=sppt.kd_kelurahan and op.kd_blok=sppt.kd_blok and
                      op.no_urut=sppt.no_urut and op.kd_jns_op=sppt.kd_jns_op
                     where sppt.kd_propinsi = '$kodes[0]' and sppt.kd_dati2='$kodes[1]' and sppt.kd_kecamatan='$kodes[2]'
                        and sppt.kd_kelurahan='$kodes[3]' and sppt.kd_blok='$kodes[4]' and sppt.no_urut='$kodes[5]' and sppt.kd_jns_op='$kodes[6]'
                        and sppt.thn_pajak_sppt='$thn' 
                    order by sppt.thn_pajak_sppt desc limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }
    
    function get_berkas_all($curent = 0)
    {
        $sql = $this->berkas_in;
        $sql .= " where validasi.id is null ";
        if ($curent <> 0) {
            $sql .= " or bkin.id=$curent ";
        }
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->result();
        } else
            return FALSE;
    }
    
    function get_new_berkas_in_kode()
    {
        $thn   = date('Y');
        $sql   = "select tahun, kode, no_urut from bphtb_berkas_in where tahun=$thn order by no_urut desc limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $res = $query->result();
            return array(
                $thn,
                'A',
                (double) $res[0]->no_urut + 1
            );
        } else {
            return array(
                $thn,
                'A',
                1
            );
        }
    }
    
    function get_new_berkas_out_kode()
    {
        $thn   = date('Y');
        $sql   = "select tahun, kode, no_urut from bphtb_berkas_out where tahun=$thn order by no_urut desc limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $res = $query->result();
            return array(
                $thn,
                'A',
                (double) $res[0]->no_urut + 1
            );
        } else {
            return array(
                $thn,
                'A',
                1
            );
        }
    }
    
    function get_validasi($aa, $id)
    {
        if ($aa == 'berkas') {
            $tt = 'bkin';
        } else {
            $tt = 'validasi';
        }
        $id  = ((int) $id > 0 ? $id : 0);
        $sql = $this->berkas_in;
        if ($id) {
            $sql .= " where $tt.id=$id ";
            $query = $this->db->query($sql);
            if ($query->num_rows() !== 0) {
                return $query->row();
            } else
                return FALSE;
        } else
            return FALSE;
    }
    
    function get_nomor_berkas_in($id)
    {
        $sql   = "SELECT tahun, kode, no_urut 
                FROM bphtb_berkas_in where id = " . (($id) ? $id : 0) . " limit 1 ";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $kode = $query->result();
            $ret  = $kode[0]->no_urut;
            $ret  = '000000' . $ret;
            $ret  = substr($ret, strlen($ret) - 6, 6);
            $ret  = $kode[0]->tahun . '.' . $kode[0]->kode . '.' . $ret;
            return $ret;
        } else
            return '';
    }
    
    function get_new_approval_kode()
    {
        $thn   = date('Y');
        $sql   = "select tahun, kode, no_urut from bphtb_approval where tahun=$thn order by no_urut desc limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $res = $query->result();
            return array(
                $thn,
                1,
                (double) $res[0]->no_urut + 1
            );
        } else {
            return array(
                $thn,
                1,
                1
            );
        }
    }
    
    function get_data_validasi_for_form($id)
    {
        if (!(double) $id) {
            return FALSE;
        }
        $sql   = "select v.*, v.id as validasi_id, ppat.nama as ppat_nm, ppat.kode as ppat_kd, 
                berkas.tahun as berkas_tahun, berkas.kode as berkas_kode, berkas.no_urut as berkas_no_urut,
                sspd.tahun as sspd_tahun, sspd.kode as sspd_kode, sspd.no_sspd,
                jenis.nama as perolehan_nm, dasar.nama as dasar_nm, sspd.file1, sspd.file2, sspd.file3, sspd.file4, sspd.file5
                from bphtb_validasi v inner join bphtb_ppat ppat on v.ppat_id=ppat.id
                inner join bphtb_berkas_in berkas on v.berkas_in_id=berkas.id
                inner join bphtb_perolehan jenis on v.perolehan_id=jenis.id
                inner join bphtb_dasar dasar on v.dasar_id=dasar.id
                inner join bphtb_sspd sspd on v.sspd_id=sspd.id
                where v.id=$id";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            return $query->row();
        } else
            return FALSE;
    }
    
    function get_dokumen_sspd($id)
    {
        $id    = ((double) $id) ? $id : 0;
        $sql2  = "select id, file1, file2, file3, file4, file5 from bphtb_sspd where id=$id order by id limit 1";
        $query = $this->db->query($sql2);
        if ($query->num_rows() !== 0) {
            return $query->row();
        } else
            return FALSE;
    }
    
    function get_tempat_bayar()
    {
        return FALSE;
    }
    
    function get_dop()
    {
        return FALSE;
    }
    
    function get_sppt()
    {
        return FALSE;
    }
    
    function get_pembayaran_sppt()
    {
        return FALSE;
    }
    
    function extract_nop($nop)
    {
        $op = str_replace('-', '.', $nop);
        return explode('.', $op);
    }
    
    // perhitungan bphtb
    function hitung_bphtb($bphtb_value)
    {
        if (!is_array($bphtb_value)) {
            $result['hasil'] = 0;
            $result['found'] = 0;
            return $result;
        }
        $xx = $bphtb_value[0];
        if (!$xx) {
            $result['hasil'] = 0;
            $result['found'] = 0;
        } else {
            $xx = $bphtb_value[0];
            switch ($xx) {
                case 'x1': {
                    $npop            = (double) $bphtb_value[1];
                    $npoptkp         = (double) $bphtb_value[2];
                    $tarif           = (double) $bphtb_value[3];
                    $result['hasil'] = ($npop - $npoptkp) * ($tarif / 100);
                    $result['found'] = 1;
                    break;
                }
                case 'x2': {
                    $terhutang       = (double) $bphtb_value[1];
                    $tarif           = (double) $bphtb_value[2];
                    $result['hasil'] = $terhutang * ($tarif / 100);
                    $result['found'] = 1;
                    break;
                }
                case 'x3': {
                    $terhutang = (double) $bphtb_value[1];
                    $pengurang = (double) $bphtb_value[2];
                    $sdh_bayar = (double) $bphtb_value[3];
                    $denda     = (double) $bphtb_value[4];
                    $bagian    = (double) $bphtb_value[5]; // => APHTB
                    $pembagi   = (double) $bphtb_value[6]; // => dari APHTB
                    if ($pembagi < 1) {
                        $result['hasil'] = 0;
                    } else {
                        $result['hasil'] = ($terhutang - $pengurang - $sdh_bayar + $denda) * $bagian / $pembagi;
                    }
                    $result['found'] = 1;
                    break;
                }
                default:
                    $result['hasil'] = 0;
                    $result['found'] = 0;
                    break;
            }
        }
        return $result;
    }
    
    private function transaksi_nop_setahun($thn, $nop, $id)
    {
        $thn  = (int) $thn;
        $nops = explode(".", $nop);
        $sql  = "select sspd.id, sspd.tahun, sspd.kode, sspd.no_sspd, sspd.kd_propinsi, sspd.kd_dati2, sspd.kd_kecamatan,
                sspd.kd_kelurahan, sspd.kd_blok, sspd.no_urut, sspd.kd_jns_op, sspd.tahun,
                (select count(id) from bphtb_sspd 
                where kd_propinsi=sspd.kd_propinsi and kd_dati2=sspd.kd_dati2 and kd_kecamatan=sspd.kd_kecamatan
                and kd_kelurahan=sspd.kd_kelurahan and kd_blok=sspd.kd_blok and no_urut=sspd.no_urut and kd_jns_op=sspd.kd_jns_op
                and tahun=sspd.tahun and id<=sspd.id) as jml
                from bphtb_sspd sspd 
                where sspd.kd_propinsi='$nops[0]' and sspd.kd_dati2='$nops[1]' and sspd.kd_kecamatan='$nops[2]'
                and sspd.kd_kelurahan='$nops[3]' and sspd.kd_blok='$nops[4]' and sspd.no_urut='$nops[5]' and sspd.kd_jns_op='$nops[6]'
                and sspd.tahun=$thn";
        if ((double) $id > 0) {
            $sql .= " and sspd.id = $id ";
        }
        $sql .= " order by id desc limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $res = $query->row();
            return $res->jml;
        } else {
            return 0;
        }
    }
    
    function get_npoptkp($id, $nop, $thn, $sspd_id)
    {
        $sql   = "select * from bphtb_perolehan where id=$id";
        $jml   = $this->transaksi_nop_setahun($thn, $nop, $sspd_id);
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $res = $query->row();
            return array(
                ($jml > 1) ? 0 : $res->npoptkp,
                $res->pengurang
            );
        } else {
            return array(
                0,
                0
            );
        }
    }
    
    function date_validation($date_str)
    {
        /*  format yg diterima harus format : dmy => dgn :  - d 2 digit 
        - m 2 digit 
        - y 4 digit
        - delimeter : - atau / atau . atau tidak sama sekali
        - untuk tanpa sparator, panjang character adalah 8 digit angka
        - tanpa spasi 
        lakukan penekanan pada user untuk mamasukan format tanggal dgn benar
        - boleh memasukan tanggal tanpa sparator (harus 8 digit)
        - jika menggunakan sparator, harus ada 2 sparator
        - sparator yg diperbolehkan adalah => / - . 
        langka awal, lakukan pemeriksaan sparator, jika tidak ada sparator yg diperbolehkan, maka berikan sparator default yaitu tanda -
        berhubung pencarian menggunakan strpos, jika tidak ditemukan akan bernilai false */
        // ternyata dan ternyata, return value dari strpos() ada 2 type :D :)) =))
        // jika ditemukan bernilai int(index), dgn index dimulai dari 0
        // jika tidak, boolean(false)
        if (strpos($date_str, '/') == FALSE && strpos($date_str, '-') == FALSE && strpos($date_str, '.') == FALSE) {
            if (strlen($date_str) == 6) {
                $date_str = substr($date_str, 0, 2) . '-' . substr($date_str, 2, 2) . '-' . substr($date_str, 4, 2);
            } elseif (strlen($date_str) == 8) {
                $date_str = substr($date_str, 0, 2) . '-' . substr($date_str, 2, 2) . '-' . substr($date_str, 4, 4);
            }
        }
        $date_regex = '%\A(\d{1}|\d{2})[-/.](\d{1}|\d{2})[-/.](\d{2}|\d{4})\z%';
        $hasil      = '';
        $ret        = '';
        if (preg_match($date_regex, $date_str, $hasil) == TRUE) {
            if (count($hasil) == 4) {
                // index 0 berisi data asli
                // jadi index dimulai dari 1 sampai 3
                // untuk tahun, jika yg dimasukan adalah 2 digit, maka berikan 2 digit didepan sesuai tahun sekarang yaitu 20*
                if (strlen($hasil[3]) == 2) {
                    $hasil[3] = '20' . $hasil[3];
                }
                // conversi ke format umum : yyyy-mm-dd
                $ret = $hasil[3] . '-' . $hasil[2] . '-' . $hasil[1];
                // test tanggal hasil conversi, apakah benar???
                // jika benar, kembalikan hasil conversi
                if (checkdate($hasil[2], $hasil[1], $hasil[3])) {
                    return $ret;
                } else {
                    return '';
                }
            } else {
                return '';
            }
        } else {
            return '';
        }
    }
    
    /*  hanya numeric non decimal saja */
    
    private function only_number($val)
    {
        $val_reg = '/[^0-9]/i';
        $ret     = preg_replace($val_reg, '', $val);
        if ($ret == '') {
            $ret = 0;
        }
        return $ret;
    }
    
    function set_field_number_value($listField, $data)
    {
        $ret = $data;
        if ($listField && $data) {
            foreach ($data as $fld => $val) {
                if (in_array($fld, $listField)) {
                    $ret[$fld] = $this->only_number($val);
                }
            }
        }
        return $ret;
    }
    
    function upload_sspd_file($nomor, $uplname, $current)
    {
        // $mydir = dirname(__FILE__) . ('\\..\\views\\files\\');
        $mydir = dirname(__FILE__) . ('//..//files//');
        if ($uplname != '' && $nomor) {
            if (!$_FILES[$uplname]["error"]) {
                $newName = $nomor . '-' . $_FILES[$uplname]["name"];
                if (file_exists($mydir . $newName)) {
                    unlink($mydir . $newName);
                }
                move_uploaded_file($_FILES[$uplname]['tmp_name'], $mydir . $newName);
                return $newName;
            } else {
                return $current;
            }
        } else {
            return $current;
        }
    }
    
    /*  end of numeric non decimal */
}

/* End of file _model.php */