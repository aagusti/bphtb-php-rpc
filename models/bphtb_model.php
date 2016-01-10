<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Bphtb_model extends CI_Model
{
    function get_wp_by_identitas($xktp = NULL)
    {
        // cuma cari
        $this->db->select('id, wp_nama, wp_npwp, wp_alamat, wp_blok_kav, wp_kelurahan, wp_rt, wp_rw, wp_kecamatan, wp_kota, wp_provinsi, wp_identitas, wp_kdpos');
        $this->db->from('bphtb.bphtb_validasi');
        // $this->db->like(array('lower(trim(wp_identitas))' => strtolower($xktp)));
        $this->db->where(array('lower(trim(wp_identitas))' => strtolower($xktp)));
        $this->db->order_by('wp_identitas');
        $query = $this->db->get();
        $numrows = $query->num_rows();
        
        // cari transaksi ditahun yg sama
        $this->db->select('id, wp_nama, wp_npwp, wp_alamat, wp_blok_kav, wp_kelurahan, wp_rt, wp_rw, wp_kecamatan, wp_kota, wp_provinsi, wp_identitas, wp_kdpos');
        $this->db->from('bphtb.bphtb_validasi');
        $this->db->where(array('lower(trim(wp_identitas))' => strtolower($xktp)));
        $this->db->where('extract(year from tgl_transaksi)='.date('Y'));
        $this->db->order_by('wp_identitas');
        $query2 = $this->db->get();
        $jmltr = $query2->num_rows();
        
        
        // if($numrows!==0) {
            $ret = new stdClass();
            $ret->data = ($numrows!==0) ? $query->row() : false;
            $ret->jmltr = $jmltr; // jumlah transaksi ditahun yg sama berdasarkan NIK
            return $ret;
        // } else
            // return FALSE;
    }

    // ke pbb dat_objek_pajak
    function get_data_op_from_dop($kd_propinsi, $kd_dati2, $kd_kecamatan, $kd_kelurahan, $kd_blok, $no_urut, $kd_jns_op) {
        $sql = "select jalan_op, blok_kav_no_op, rw_op, rt_op
                from dat_objek_pajak
                where 1=1
                and kd_propinsi = ? and kd_dati2 = ? and kd_kecamatan = ?
                and kd_kelurahan = ? and kd_blok = ? and no_urut = ?
                and kd_jns_op = ?
                limit 1 ";
        $query = $this->db->query($sql, array($kd_propinsi, $kd_dati2, $kd_kecamatan, $kd_kelurahan, $kd_blok, $no_urut, $kd_jns_op));
        if($query->num_rows()!==0)
            return $query->row();
        else
            return FALSE;
    }

    // ke pbb sppt
    function get_data_op_from_sppt($kd_propinsi, $kd_dati2, $kd_kecamatan, $kd_kelurahan, $kd_blok, $no_urut, $kd_jns_op, $thn) {
        $sql = "select njop_bumi_sppt, njop_bng_sppt, luas_bumi_sppt, luas_bng_sppt
                from sppt
                where 1=1
                and kd_propinsi = ? and kd_dati2 = ? and kd_kecamatan = ?
                and kd_kelurahan = ? and kd_blok = ? and no_urut = ?
                and kd_jns_op = ? and thn_pajak_sppt = ?
                limit 1 ";
        $query = $this->db->query($sql, array($kd_propinsi, $kd_dati2, $kd_kecamatan, $kd_kelurahan, $kd_blok, $no_urut, $kd_jns_op, $thn));
        if($query->num_rows()!==0)
            return $query->row();
        else
            return FALSE;
    }

    function get_npoptkp($id) { // ??
        $sql = "select * from bphtb.bphtb_perolehan where id = ?";
        $query = $this->db->query($sql, $id);
        if($query->num_rows()!==0)
            return $query->row();
        else
            return FALSE;
    }

    function informasi_objek_pajak($nop) {
        $nop_num = preg_replace("/[^0-9]/", "", $nop);

        if ((!$nop) || (!$nop) || strlen($nop_num) != 18)
            return FALSE;

        $nop_dot = preg_replace("/([0-9]{2})([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{1})/", "$1.$2.$3.$4.$5.$6.$7", $nop_num);
        $kode = explode(".", $nop_dot);
        list($kd_propinsi, $kd_dati2, $kd_kecamatan, $kd_kelurahan, $kd_blok, $no_urut, $kd_jns_op) = $kode;

        $sql = " select
                s.kd_propinsi||'.'||s.kd_dati2||'-'||s.kd_kecamatan||'.'||s.kd_kelurahan ||'-'||
                s.kd_blok ||'.'||s.no_urut||'.'|| s.kd_jns_op nop,
                dop.jalan_op || ', ' || dop.blok_kav_no_op alamat_op,
                dop.rt_op || ' / ' || dop.rw_op rt_rw_op,
                dop.total_luas_bumi,
                dop.total_luas_bng,

                s.nm_wp_sppt nm_wp,
                s.jln_wp_sppt|| ', ' || s.blok_kav_no_wp_sppt alamat_wp,
                s.rt_wp_sppt || ' / ' || s.rw_wp_sppt rt_rw_wp,
                s.kelurahan_wp_sppt kelurahan_wp, s.kota_wp_sppt kota_wp,

                s.thn_pajak_sppt,
                s.luas_bumi_sppt luas_tanah,
                s.njop_bumi_sppt njop_tanah,
                s.luas_bng_sppt luas_bng,
                s.njop_bng_sppt njop_bng,
                s.pbb_yg_harus_dibayar_sppt ketetapan,
                s.status_pembayaran_sppt status_bayar

            from sppt s
            left join dat_objek_pajak dop
                on dop.kd_propinsi = s.kd_propinsi
                and dop.kd_dati2 = s.kd_dati2
                and dop.kd_kecamatan = s.kd_kecamatan
                and dop.kd_kelurahan = s.kd_kelurahan
                and dop.kd_blok = s.kd_blok
                and dop.no_urut = s.no_urut
                and dop.kd_jns_op = s.kd_jns_op

            where s.kd_propinsi = ?
                and s.kd_dati2 = ?
                and s.kd_kecamatan = ?
                and s.kd_kelurahan = ?
                and s.kd_blok = ?
                and s.no_urut = ?
                and s.kd_jns_op = ?
                -- and cast(s.thn_pajak_sppt as int) between (".date('Y')."-3) AND ".date('Y')."
            order by s.thn_pajak_sppt desc";
        $query = $this->db->query($sql, array($kd_propinsi, $kd_dati2, $kd_kecamatan, $kd_kelurahan, $kd_blok, $no_urut, $kd_jns_op));
        if($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return NULL;
        }
    }

    /** DASHBOARD **/
    function realisasi_dashboard() {
        // cukup ambil dari bank aja kata BOS.
        $sql = "select sum(cnt_daily) cnt_daily, sum(amt_daily) amt_daily, sum(cnt_weekly) cnt_weekly, sum(amt_weekly) amt_weekly,
                sum(cnt_monthly) cnt_monthly, sum(amt_monthly) amt_monthly, sum(cnt_yearly) cnt_yearly, sum(amt_yearly) amt_yearly
                from (
                    --harian
                    select count(*) cnt_daily, coalesce(sum(bayar),0) amt_daily, 0 cnt_weekly, 0 amt_weekly,
                      0 cnt_monthly, 0 amt_monthly, 0 cnt_yearly, 0 amt_yearly
                    from bphtb.bphtb_bank
                    where tanggal >=now()::date and tanggal <= now()::date+1
                    --mingguan
                    union
                    select 0 cnt_daily, 0 amt_daily, count(*) cnt_weekly, coalesce(sum(bayar),0) amt_weekly,
                      0 cnt_monthly, 0 amt_monthly, 0 cnt_yearly, 0 amt_yearly
                    from bphtb.bphtb_bank
                    where extract (week from tanggal) = extract (week from now()::date)
                    and extract (year from tanggal) = extract (year from now()::date)
                    --bulanan
                    union
                    select 0 cnt_daily, 0 amt_daily, 0 cnt_weekly, 0 amt_weekly,
                      count(*) cnt_monthly, coalesce(sum(bayar),0) amt_monthly, 0 cnt_yearly, 0 amt_yearly
                    from bphtb.bphtb_bank
                    where extract (month from tanggal) = extract (month from now()::date)
                    and extract (year from tanggal) = extract (year from now()::date)
                    --tahunan
                    union
                    select 0 cnt_daily, 0 amt_daily, 0 cnt_weekly, 0 amt_weekly,
                      0 cnt_monthly, 0 amt_monthly, count(*) cnt_yearly, coalesce(sum(bayar),0) amt_yearly
                    from bphtb.bphtb_bank
                    where extract (year from tanggal) = extract (year from now()::date)
                ) as dt_sspd";


        return $this->db->query($sql)->row();
    }

    /*
    // sebelumnya di pake pas ngambil data wp pada saat load data sspd
    function get_op_sppt($nopthn)
    {
        if ((!$nopthn) || (!$nopthn))
            return FALSE;

        $nop_num = preg_replace("/[^0-9]/", "", $nopthn);
        $nop_dot = preg_replace("/([0-9]{2})([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{1})([0-9]{4})/", "$1.$2.$3.$4.$5.$6.$7.$8", $nop_num);
        $kode = explode(".", $nop_dot);
        list($kd_propinsi, $kd_dati2, $kd_kecamatan, $kd_kelurahan, $kd_blok, $no_urut, $kd_jns_op, $thn_pajak_sppt) = $kode;

        $sql = "select (case when op.jalan_op is null or op.jalan_op='' then '-' else op.jalan_op end) as op_alamat,
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

            from dat_objek_pajak op
            inner join sppt on
                op.kd_propinsi=sppt.kd_propinsi and
                op.kd_dati2=sppt.kd_dati2 and
                op.kd_kecamatan=sppt.kd_kecamatan and
                op.kd_kelurahan=sppt.kd_kelurahan and
                op.kd_blok=sppt.kd_blok and
                op.no_urut=sppt.no_urut and
                op.kd_jns_op=sppt.kd_jns_op
            where
                sppt.kd_propinsi = ? and
                sppt.kd_dati2 = ? and
                sppt.kd_kecamatan = ? and
                sppt.kd_kelurahan = ? and
                sppt.kd_blok = ? and
                sppt.no_urut = ? and
                sppt.kd_jns_op = ? and
                sppt.thn_pajak_sppt = ?
            order by sppt.thn_pajak_sppt desc
            limit 1";

        $query = $this->db->query($sql, array($kd_propinsi, $kd_dati2, $kd_kecamatan, $kd_kelurahan, $kd_blok, $no_urut, $kd_jns_op, $thn_pajak_sppt));
        if ($query->num_rows() !== 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }
    */


    // OLD VERSION --- under limit huh, kebanyak ngalor ngidul! dipake di module pasaran
    private function replace_me($xdata) {
        $src = array("\\", "\n", "\r", "'");
        $with = array("\\\\", "\\n", "\\r", "\'");
        return str_replace($src, $with, $xdata);
    }

    function get_propinsi($kd = '') {
        $sql = "select * from ref_propinsi";
        if ($kd !== '') {
            $sql .= " where kd_propinsi = '" . $this->replace_me($kd) . "'";
        }
        $sql .= " order by kd_propinsi";
        $query = $this->db->query($sql);
        if($query->num_rows()!==0)
        {
            return $query->result();
        }
        else { return FALSE; }
    }

    function get_propinsi_nama($kd) {
        if ($kd=='') { return ''; }
        $sql = "select * from ref_propinsi";
        $sql .= " where kd_propinsi = '" . $this->replace_me($kd) . "'";
        $query = $this->db->query($sql);
        if($query->num_rows()!==0)
        {
            $ret = $query->row();
            return $ret->nm_propinsi;
        }
        else { return ''; }
    }

    function get_dati2($kp = '', $kd = '') {
        $sql = "select * from ref_dati2";
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
        if($query->num_rows()!==0)
        {
            return $query->result();
        }
        else { return FALSE; }
    }

    function get_dati2_nama($kp, $kd) {
        if ($kd=='' || $kp=='') { return ''; }
        $sql = "select * from ref_dati2";
        $sql .= " where kd_propinsi = '" . $this->replace_me($kp) . "' ";
        $sql .= " and kd_dati2 = '" . $this->replace_me($kd) . "' ";
        $query = $this->db->query($sql);
        if($query->num_rows()!==0)
        {
            $ret = $query->row();
            return $ret->nm_dati2;
        }
        else { return ''; }
    }

    function get_kecamatan($kp = '', $kd = '', $kc = '') {
        $sql = "select * from ref_kecamatan";
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
        if($query->num_rows()!==0)
        {
            return $query->result();
        }
        else { return FALSE; }
    }

    function get_kecamatan_nama($kp, $kd, $kc) {
        if ($kd=='' || $kp=='' || $kc=='') { return ''; }
        $sql = "select * from ref_kecamatan";
        $sql .= " where kd_propinsi = '" . $this->replace_me($kp) . "' ";
        $sql .= " and kd_dati2 = '" . $this->replace_me($kd) . "' ";
        $sql .= " and kd_kecamatan = '" . $this->replace_me($kc) . "' ";
        $query = $this->db->query($sql);
        if($query->num_rows()!==0)
        {
            $ret = $query->row();
            return $ret->nm_kecamatan;
        }
        else { return ''; }
    }

    function get_kelurahan($kp = '', $kd = '', $kc = '', $kl = '') {
        $sql = "select * from ref_kelurahan";
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
        if($query->num_rows()!==0)
        {
            return $query->result();
        }
        else { return FALSE; }
    }

    function get_kelurahan_nama($kp, $kd, $kc, $kl) {
        if ($kd=='' || $kp=='' || $kc=='' || $kl=='') { return ''; }
        $sql = "select * from ref_kelurahan";
        $sql .= " where kd_propinsi = '" . $this->replace_me($kp) . "' ";
        $sql .= " and kd_dati2 = '" . $this->replace_me($kd) . "' ";
        $sql .= " and kd_kecamatan = '" . $this->replace_me($kc) . "' ";
        $sql .= " and kd_kelurahan = '" . $this->replace_me($kl) . "' ";
        $query = $this->db->query($sql);
        if($query->num_rows()!==0)
        {
            $ret = $query->row();
            return $ret->nm_kelurahan;
        }
        else { return ''; }
    }

}

/* End of file _model.php */