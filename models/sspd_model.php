<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sspd_model extends CI_Model {
    private $tbl = 'bphtb.bphtb_sspd';

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

    function get_sspdno($id)
    {
        $sql = "select get_sspdno(id) as sspdno
                from bphtb_sspd
                where id=?";

        $query = $this->db->query($sql, array($id));
        if($query->num_rows()!==0)
        {
            return $query->row()->sspdno;
        }
        else
            return FALSE;
    }

    function get_nop($id, $formated=true)
    {
        $formated = ($formated) ? 'true' : 'false';
        $sql = "select get_nop_sspd(id, {$formated}) as nopthn
                from bphtb_sspd
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
        $sql = "select get_nop_thn_sspd(id, true) as nopthn
                from bphtb_sspd
                where id=?";

        $query = $this->db->query($sql, array($id));
        if($query->num_rows()!==0)
        {
            return $query->row()->nopthn;
        }
        else
            return FALSE;
    }

    function get_new_sspdno($thn, $kode)
    {
        if($kode!='kb') 
            $where = " where tahun={$thn} and kode='{$kode}' ";
        else
            $where = " where tahun={$thn} ";
        $sql = "select tahun, kode, no_sspd
                from bphtb_sspd
                {$where}
                order by no_sspd desc limit 1";
/*
        $sql = "select tahun, kode, no_sspd
                from bphtb_sspd
                where tahun={$thn} and kode='{$kode}'
                order by no_sspd desc limit 1";
*/
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $result = $query->result();
            return array(
                $thn,
                $kode,
                (double) $result[0]->no_sspd + 1
            );
        } else {
            return array($thn,$kode,1);
        }
    }
    
    //baru
    function get_new_kode($thn, $nourut)
    {
        $sql = "select max(kode::int) kodenya
                from bphtb_sspd
                where tahun={$thn} and no_sspd='{$nourut}' and kode::int < 97
                limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() !== 0) {
            $kode = $query->row()->kodenya;
            $kode++;
            return $kode;
        } else {
            return FALSE;
        }
    }

    function do_upload($data)
    {
        $kode  = $data['tahun'] . '.' . $data['kode'];
        $nomor = $data['no_sspd'];
        $nomor = str_pad($nomor, 6, "0", STR_PAD_LEFT);
        $kode .= '.' . $nomor;
        for ($i = 1; $i <= 10; $i++) {
            $file = $this->upload_sspd_file($kode . '-doc' . (string) $i, 'attach' . (string) $i, $data['file' . (string) $i]);
            if ($file != '') {
                $data['file' . (string) $i] = $file;
            }
        }
        return $data;
    }

    function upload_sspd_file($nomor, $uplname, $current)
    {
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

    function proses_stpd($sspd_id) {            
        $kode = 97;
        $denda = 7500000;
        $create_uid = $this->session->userdata('uid');
        $created = date('Y-m-d h:m:s');
        $jt = date('Y-m-d', strtotime("+30 days"));
        $dasar_hitung = 2;

        // cek udah ada stpd blm untuk nomor ini
        $sspd = $this->get($sspd_id);
        $sql = "select tahun from bphtb_sspd
                where tahun='".$sspd->tahun."' and no_sspd='".$sspd->no_sspd."' and kode='{$kode}'";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return;            
            
        $sql = "
            INSERT INTO bphtb_sspd(
                tahun, kode, no_sspd, ppat_id, wp_nama, wp_npwp, wp_alamat,
                wp_blok_kav, wp_kelurahan, wp_rt, wp_rw, wp_kecamatan, wp_kota,
                wp_provinsi, wp_identitas, wp_identitaskd, tgl_transaksi, kd_propinsi,
                kd_dati2, kd_kecamatan, kd_kelurahan, kd_blok, no_urut, kd_jns_op,
                thn_pajak_sppt, op_alamat, op_blok_kav, op_rt, op_rw, bumi_luas,
                bumi_njop, bng_luas, bng_njop, no_sertifikat, njop, perolehan_id,
                npop, npoptkp, tarif, terhutang, bagian, pembagi, tarif_pengurang,
                pengurang, bphtb_sudah_dibayarkan, denda, restitusi, bphtb_harus_dibayarkan,
                status_pembayaran, dasar_id, create_uid, created,
                header_id, tgl_print, tgl_approval, file1, file2, file3,
                file4, file5, wp_kdpos, file6, file7, file8, file9, file10, keterangan,
                status_daftar, persen_pengurang_sendiri, pp_nomor_pengurang_sendiri,
                no_ajb, tgl_ajb, wp_nama_asal, jml_pph, tgl_pph, posted, pos_tp_id,
                status_validasi, status_bpn, tgl_jatuh_tempo, verifikasi_uid,
                verifikasi_date, pbb_nop, verifikasi_bphtb_uid, verifikasi_bphtb_date,
                hasil_penelitian, no_sk, pengurangan_sk, pengurangan_jatuh_tempo_tgl,
                pengurangan_sk_tgl, ketetapan_no, ketetapan_tgl, ketetapan_atas_sspd_no,
                ketetapan_jatuh_tempo_tgl, pembayaran_ke, mutasi_penuh, harga_transaksi, npopkp )
            SELECT tahun, '{$kode}' kode, no_sspd, ppat_id, wp_nama, wp_npwp, wp_alamat,
                wp_blok_kav, wp_kelurahan, wp_rt, wp_rw, wp_kecamatan, wp_kota,
                wp_provinsi, wp_identitas, wp_identitaskd, tgl_transaksi, kd_propinsi,
                kd_dati2, kd_kecamatan, kd_kelurahan, kd_blok, no_urut, kd_jns_op,
                thn_pajak_sppt, op_alamat, op_blok_kav, op_rt, op_rw, bumi_luas,
                bumi_njop, bng_luas, bng_njop, no_sertifikat, njop, perolehan_id,
                npop, npoptkp, tarif, {$denda} terhutang, bagian, pembagi, tarif_pengurang,
                pengurang, terhutang as bphtb_sudah_dibayarkan, {$denda} as denda, restitusi, {$denda} bphtb_harus_dibayarkan,
                0 status_pembayaran, {$dasar_hitung} dasar_id, '{$create_uid}' create_uid, '{$created}' created,
                {$sspd_id} header_id, null tgl_print, null tgl_approval, file1, file2, file3,
                file4, file5, wp_kdpos, file6, file7, file8, file9, file10, 'STPD' keterangan,
                0 status_daftar, persen_pengurang_sendiri, pp_nomor_pengurang_sendiri,
                no_ajb, tgl_ajb, wp_nama_asal, jml_pph, tgl_pph, null posted, pos_tp_id,
                null status_validasi, null status_bpn, '{$jt}' tgl_jatuh_tempo, null verifikasi_uid,
                null verifikasi_date, pbb_nop, null verifikasi_bphtb_uid, null verifikasi_bphtb_date,
                null hasil_penelitian, null no_sk, null pengurangan_sk, null pengurangan_jatuh_tempo_tgl,
                null pengurangan_sk_tgl, null ketetapan_no, null ketetapan_tgl, null ketetapan_atas_sspd_no,
                null ketetapan_jatuh_tempo_tgl, 0 pembayaran_ke, mutasi_penuh, harga_transaksi, npopkp
            FROM bphtb_sspd
            WHERE id={$sspd_id}";

        $this->db->query($sql);
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
