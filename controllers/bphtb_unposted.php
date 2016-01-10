<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class bphtb_unposted extends CI_Controller {

	private $module = 'pbb_bphtb';
	private $controller = 'bphtb_unposted';
	private $current = 'pbb';

	function __construct() {
		parent::__construct();
		if(!$this->session->userdata('login')) {
			$this->session->set_flashdata('msg_warning', 'Session telah kadaluarsa, silahkan login ulang.');
			redirect('login');
			exit;
		}
        
        $this->load->library('module_auth', array('module' => $this->module));

		$this->load->model(array('apps_model'));
        $this->load->model(array('bphtb_pbb_model'));
        
		$this->load->helper(active_module());
    }

	public function index() {
		if(!$this->module_auth->read) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
			redirect(active_module_url(''));
		}

		$data['current'] = $this->current;
		$data['apps']    = $this->apps_model->get_active_only();

		$this->load->view('vbphtb_unposted', $data);
	}

	function grid() {            
        $this->load->library('Datatables');
        
        $this->datatables->order_by("ss.tahun", "desc");
        $this->datatables->order_by("ss.kode", "desc");
        $this->datatables->order_by("ss.no_sspd", "desc");
        
        $this->datatables->select('ss.id, {rownum} as rownum, get_nop_sspd(ss.id,true) as nop', false);
        $this->datatables->from('bphtb_sspd ss');

        $this->datatables->add_column('', "<input type='checkbox' class='cek_sspd_id' id='sspd_id' name='sspd_id' value='$1' onClick='ceklik();'>", 'id');
        
        $this->datatables->where("ss.kd_propinsi", KD_PROPINSI);
        $this->datatables->where("ss.kd_dati2", KD_DATI2);
        
        // menampilkan data sspd yang AJBnya sudah di VERIFIKASI saja
        // $this->datatables->where("ss.kode = '1'");
        $this->datatables->where("ss.posted = 0");
        $this->datatables->where("ss.verifikasi_date is not null");
        $this->datatables->where("ss.verifikasi_uid is not null");
        $this->datatables->where("ss.verifikasi_bphtb_uid is not null");
        $this->datatables->where("ss.verifikasi_bphtb_date is not null");
        
        echo $this->datatables->generate();
	}

    public function load_data()
    {
		if(!$this->module_auth->read) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
			redirect(active_module_url(''));
		}

        $sspd_id = $this->uri->segment(4);

        if ($sspd_id && $sspd = $this->bphtb_pbb_model->get_sspd($sspd_id)) {
            //nambahin prefix
            foreach ($sspd as $key => $val) {
                $sspd['bphtb_'.$key] = $val;
                unset($sspd[$key]);
            }

            $ret = (object) array_merge($sspd, array(
                'sspd_id' => $sspd_id,
                'found' => 1,
            ));

            $sismiop_op_val = 0;
            $sismiop_wp_val = 0;
            if($sismiop_op = $this->bphtb_pbb_model->get_sismiop_op($sspd['bphtb_nop'])) {
                $sismiop_op_val = 1;
                $ret = (object) array_merge((array) $ret, (array) $sismiop_op);

                if($sismiop_wp = $this->bphtb_pbb_model->get_sismiop_wp($sismiop_op->subjek_pajak_id)) {
                    $sismiop_wp_val = 1;
                    $ret = (object) array_merge((array) $ret, (array) $sismiop_wp);
                }
            }

            $ret = (object) array_merge((array) $ret, array(
                'sismiop_op' => $sismiop_op_val,
                'sismiop_wp' => $sismiop_wp_val,
            ));

            echo json_encode($ret);
        } else {
            $result['found'] = 0;
            echo json_encode($result);
        }
    }

    function update() {
        if (!$_POST) redirect(active_module_url('bphtb_unposted'));

        // cek aja dulu
        $bphtb_nop = trim($_POST["bphtb_nop"]);
        $pbb_nop   = trim($_POST["pbb_nop"]);
        if($bphtb_nop!=$pbb_nop) {
            $pbb_nop = preg_replace( '/[^0-9]/', '', $pbb_nop);
            if($this->bphtb_pbb_model->cari_op($pbb_nop)) {
                $this->session->set_flashdata('msg_error', "PBB NOP baru ({$_POST["pbb_nop"]}) sudah ada, Proses dibatalkan");
                redirect(active_module_url('bphtb_unposted'));
            }
        }
                
        // update bphtb sspd
        $data = array (
            'pbb_nop' => $_POST["pbb_nop"],

            'wp_nama' => $_POST["wp_nama"],
            'wp_npwp' => $_POST["wp_npwp"],
            'wp_alamat' => $_POST["wp_alamat"],
            'wp_blok_kav' => $_POST["wp_blok_kav"],
            'wp_kelurahan' => $_POST["wp_kelurahan"],
            'wp_rt' => substr($_POST["wp_rt"], -3),
            'wp_rw' => substr($_POST["wp_rw"], -2),
            'wp_kecamatan' => $_POST["wp_kecamatan"],
            'wp_kota' => $_POST["wp_kota"],
            'wp_provinsi' => $_POST["wp_provinsi"],
            'op_alamat' => $_POST["op_alamat"],
            'op_blok_kav' => $_POST["op_blok_kav"],
            'op_rt' => $_POST["op_rt"],
            'op_rw' => $_POST["op_rw"],
            'bumi_luas' => $_POST["bumi_luas"],
            'bumi_njop' => $_POST["bumi_njop"],
            'bng_luas' => $_POST["bng_luas"],
            'bng_njop' => $_POST["bng_njop"],
            'wp_identitas' => trim($_POST["wp_identitas"]),

            'posted' => 1,
        );
        $this->bphtb_pbb_model->update_sspd($_POST["bphtb_sspd_id"], $data);


        /*
        1. cari subjek pajak sesuai subjek_pajak_id ($_POST["wp_identitas"])
            2. jika ada diupdate, jika tidak insert baru
        3. jika inset/update berhasil,
            4. jika nop_pbb tidak sama dengan nop_bphtb maka
                    insert data dat_objek_pajak
                    insert data dat_op_bumi
               else
                    update dat_objek_pajak
                    update dat_op_bumi
        */

        // update dat_subjek_pajak
        $subjek_pajak_id = trim($_POST["wp_identitas"]);
        $data = array (
            'subjek_pajak_id' => $subjek_pajak_id,
            'nm_wp' => $_POST["wp_nama"],
            'npwp' => $_POST["wp_npwp"],
            'jalan_wp' => $_POST["wp_alamat"],
            'blok_kav_no_wp' => $_POST["wp_blok_kav"],
            'kelurahan_wp' => $_POST["wp_kelurahan"],
            'rt_wp' => substr($_POST["wp_rt"], -3),
            'rw_wp' => substr($_POST["wp_rw"], -2),
            'kecamatan_wp' => $_POST["wp_kecamatan"],
            'kota_wp' => $_POST["wp_kota"],
            'propinsi_wp' => $_POST["wp_provinsi"],
        );

        $update_sp = false;
        if($this->bphtb_pbb_model->cari_sp($subjek_pajak_id, $data)) {
            // update dat_subjek_pajak
            if($this->bphtb_pbb_model->update_sp($subjek_pajak_id, $data))
                $update_sp = true;
        } else {
            // insert dat_subjek_pajak
            if($this->bphtb_pbb_model->insert_sp($data))
                $update_sp = true;
        }

        if($update_sp) {
            $data = array (
                'subjek_pajak_id' => $_POST["wp_identitas"],
                'jalan_op' => $_POST["op_alamat"],
                'blok_kav_no_op' => $_POST["op_blok_kav"],
                'rt_op' => substr($_POST["op_rt"], -3),
                'rw_op' => substr($_POST["op_rw"], -2),
                'total_luas_bumi' => $_POST["bumi_luas"],
                'njop_bumi' => $_POST["bumi_njop"],
                'total_luas_bng' => $_POST["bng_luas"],
                'njop_bng' => $_POST["bng_njop"],
            );

            $bphtb_nop = trim($_POST["bphtb_nop"]);
            $pbb_nop   = trim($_POST["pbb_nop"]);
            if($bphtb_nop==$pbb_nop) {
                // update dat_objek_pajak
                $this->bphtb_pbb_model->update_op($_POST["nop"], $data);
            } else {
                // insert dat_objek_pajak
                if($op_lama = $this->bphtb_pbb_model->cari_op($_POST["nop"])) {
                    $pbb_nop = preg_replace( '/[^0-9]/', '', $pbb_nop);
                    $kd_propinsi    = substr($pbb_nop, 0, 2);
                    $kd_dati2       = substr($pbb_nop, 2, 2);
                    $kd_kecamatan   = substr($pbb_nop, 4, 3);
                    $kd_kelurahan   = substr($pbb_nop, 7, 3);
                    $kd_blok        = substr($pbb_nop, 10, 3);
                    $no_urut        = substr($pbb_nop, 13, 4);
                    $kd_jns_op      = substr($pbb_nop, -1);
                    
                    $data2 = array_merge($data, array (
                        'kd_propinsi' => $kd_propinsi,
                        'kd_dati2' => $kd_dati2,
                        'kd_kecamatan' => $kd_kecamatan,
                        'kd_kelurahan' => $kd_kelurahan,
                        'kd_blok' => $kd_blok,
                        'no_urut' => $no_urut,
                        'kd_jns_op' => $kd_jns_op,
                        
                        'no_formulir_spop' => $op_lama->no_formulir_spop,
                        'no_persil' => $op_lama->no_persil,
                        'kd_status_cabang' => $op_lama->kd_status_cabang,
                        'kd_status_wp' => $op_lama->kd_status_wp,
                        'status_peta_op' => $op_lama->status_peta_op,
                        'jns_transaksi_op' => $op_lama->jns_transaksi_op,
                        'tgl_pendataan_op' => $op_lama->tgl_pendataan_op,
                        'nip_pendata' => $op_lama->nip_pendata,
                        'tgl_pemeriksaan_op' => $op_lama->tgl_pemeriksaan_op,
                        'nip_pemeriksa_op' => $op_lama->nip_pemeriksa_op,
                        'tgl_perekaman_op' => $op_lama->tgl_perekaman_op,
                        'nip_perekam_op' => $op_lama->nip_perekam_op,
                    ));

                    // insert dat_objek_pajak
                    $this->bphtb_pbb_model->insert_op($data2);
                }
            }

            // update dat_objek_pajak lagi (subjek_pajak_id fk)
            /*
            $data = array (
                'subjek_pajak_id' => trim($_POST["wp_identitas"]),
            );
            $this->bphtb_pbb_model->update_op_sp_id(trim($_POST["sp_id"]), $data);
            */


            // update dat_op_bumi
            // ...
        }

		$this->session->set_flashdata('msg_success', 'Data telah diupdate');
        redirect(active_module_url('bphtb_unposted'));
    }
}
