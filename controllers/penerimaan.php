<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Penerimaan extends CI_Controller
{
    private $module = 'penerimaan';
    private $controller = 'penerimaan';
    private $current = 'penerimaan';

    function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            $this->session->set_flashdata('msg_warning', 'Session telah kadaluarsa, silahkan login ulang.');
            redirect('login');
            exit;
        }

        $this->load->library('module_auth', array(
            'module' => $this->module
        ));

        $this->load->model(array(
            'apps_model',
            'dasar_model',
            'perolehan_model',
            'penerimaan_model',
            'ppat_model',
            'sspd_model',
            'bphtb_model',
            'tp_model',
            'status_hak_model',
        ));

        $this->load->helper(active_module());
    }

    public function index()
    {
        if (!$this->module_auth->read) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
            redirect(active_module_url(''));
        }

        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();

        $options = array(
            '1' => 'BELUM PROSES',
            '2' => 'SUDAH PROSES',
        );
        $js = 'id="proses_id" class="input-medium"';
        $select = form_dropdown('proses_id', $options, 1, $js);
        $select = preg_replace("/[\r\n]+/", "", $select);
        $data['select_proses'] = $select;

        $this->load->view('vpenerimaan', $data);
    }


    function grid()
    {
        $tgl1 = $this->uri->segment(4);
        $tgl2 = $this->uri->segment(5);
        $proses_id = $this->uri->segment(6);

        if($tgl1=='') {
            // $tgl1 = date('Y-01-01');
            $tgl1 = date('Y-m-d');
            $tgl2 = date('Y-m-d');;
        } else {
            $tgl1 = date('Y-m-d', strtotime($tgl1));
            $tgl2 = date('Y-m-d', strtotime($tgl2));
        }
        $proses_id = ($proses_id) ? $proses_id : 1;

        $this->load->library('Datatables');
        if($proses_id == 2) {
            $this->datatables->select('b.id, {rownum} as rownum, b.transno, b.tanggal,
                get_nop_bank(b.id,true) as nop,
                b.thn_pajak_sppt, b.wp_nama, b.bayar, b.no_tagihan, s.tgl_transaksi', false);
            $this->datatables->from('bphtb_bank b');
            $this->datatables->join('bphtb_sspd s', 'b.sspd_id=s.id', 'left');

            if($this->input->get('sSearch') == '')
                $this->datatables->filter("date(b.tanggal) between '{$tgl1}' and date('{$tgl2}')+1 ");

            $this->datatables->where("b.kd_propinsi", KD_PROPINSI);
            $this->datatables->where("b.kd_dati2", KD_DATI2);

            $this->datatables->where("b.bayar > 0");

        } else {
            $this->datatables->select("s.id, {rownum} as rownum, '' as transno, '' as tanggal,
                get_nop_sspd(s.id,true) as nop,
                s.thn_pajak_sppt, s.wp_nama, s.bphtb_harus_dibayarkan, get_sspdno(s.id) as no_tagihan,
                s.tgl_transaksi", false);
            $this->datatables->from('bphtb_sspd s');

            if($this->input->get('sSearch') == '')
                $this->datatables->filter("date(s.tgl_transaksi) between '{$tgl1}' and '{$tgl2}'");

            $this->datatables->where("s.kd_propinsi", KD_PROPINSI);
            $this->datatables->where("s.kd_dati2", KD_DATI2);

            $this->datatables->where("s.status_pembayaran = 0");
            $this->datatables->where("s.bphtb_harus_dibayarkan > 0");
        }

        $this->datatables->date_column('3,9');
        $this->datatables->rupiah_column('7');
        echo $this->datatables->generate();
    }

    private function fvalidation()
    {
        $this->form_validation->set_error_delimiters('<span>', '</span>');

        $this->form_validation->set_rules('tgl_transaksi', 'Tgl. Transaksi', 'required|callback_valid_date');
        $this->form_validation->set_rules('transno', 'No. Transaksi', 'required');

        $this->form_validation->set_rules('dasar_id','Dasar Perhitungan','required|numeric');
        $this->form_validation->set_rules('perolehan_id','Jenis Perolehan','required|numeric');
        $this->form_validation->set_rules('tp_id','Tempat Pembayaran','required|numeric');

        $this->form_validation->set_rules('wp_nama','Nama WP','required|trim|max_length[50]');
        $this->form_validation->set_rules('wp_npwp','NPWP WP','required|trim|max_length[50]');
        $this->form_validation->set_rules('wp_alamat','Alamat WP','required|trim|max_length[100]');
        $this->form_validation->set_rules('wp_blok_kav','Blok / Kav / No','required|trim|max_length[100]');
        $this->form_validation->set_rules('wp_kecamatan','Kecamatan WP','required|trim|max_length[30]');
        $this->form_validation->set_rules('wp_kelurahan','Kelurahan WP','required|trim|max_length[30]');
        $this->form_validation->set_rules('wp_rt','RT WP','required|trim|max_length[3]');
        $this->form_validation->set_rules('wp_rw','RW WP','required|trim|max_length[3]');
        $this->form_validation->set_rules('wp_kota','Kabupaten / Kota WP','required|trim|max_length[30]');
        $this->form_validation->set_rules('wp_kdpos','Kode Pos WP','required|trim|max_length[5]');
        $this->form_validation->set_rules('wp_provinsi','Propinsi WP','required|trim|max_length[30]');
        $this->form_validation->set_rules('wp_identitas','No.Identitas WP','required|trim|max_length[50]');

        // $this->form_validation->set_rules('wp_nama_asal','Nama WP Asal','required|trim|max_length[50]');

        // menyesuaikan ke banjar
        if(defined('BPHTB_BANJAR') && BPHTB_BANJAR==TRUE) {
            $this->form_validation->set_rules('wp_identitas_asal','No.Identitas WP Asal','required|trim|max_length[50]');
            $this->form_validation->set_rules('wp_npwp_asal','NPWP WP','trim|max_length[50]');
            $this->form_validation->set_rules('wp_alamat_asal','Alamat WP','trim|max_length[100]');
            $this->form_validation->set_rules('wp_blok_kav_asal','Blok / Kav / No','trim|max_length[100]');
            $this->form_validation->set_rules('wp_kecamatan_asal','Kecamatan WP','trim|max_length[30]');
            $this->form_validation->set_rules('wp_kelurahan_asal','Kelurahan WP','trim|max_length[30]');
            $this->form_validation->set_rules('wp_rt_asal','RT WP','trim|max_length[3]');
            $this->form_validation->set_rules('wp_rw_asal','RW WP','trim|max_length[3]');
            $this->form_validation->set_rules('wp_kota_asal','Kabupaten / Kota WP','trim|max_length[30]');
            $this->form_validation->set_rules('wp_kdpos_asal','Kode Pos WP','trim|max_length[5]');
            $this->form_validation->set_rules('wp_provinsi_asal','Propinsi WP','trim|max_length[30]');
            $this->form_validation->set_rules('status_hak_id','Hak Milik','required|numeric');
        }

        $this->form_validation->set_rules('op_alamat','Alamat OP','required|trim|max_length[100]');
        $this->form_validation->set_rules('op_blok_kav','Blok / Kav/ No OP','required|trim|max_length[100]');
        $this->form_validation->set_rules('op_rt','RT OP','required|trim|max_length[3]');
        $this->form_validation->set_rules('op_rw','RW OP','required|trim|max_length[3]');

        $this->form_validation->set_rules('no_sertifikat','No. Sertifikat','trim|max_length[30]');
        $this->form_validation->set_rules('pp_nomor_pengurang_sendiri','No. PP Pengurang Sendiri','trim|max_length[50]');
        $this->form_validation->set_rules('keterangan','Keterangan','trim|max_length[255]');

        $this->form_validation->set_rules('nop_thn','NOP-Thn.Pajak SPPT','trim|required');
        $this->form_validation->set_rules('sspdno','No. Tagihan','trim|required');
    }

    private function fpost()
    {
        $data['id'] = $this->input->post('id');
        $data['tahun'] = $this->input->post('tahun');
        $data['kode'] = $this->input->post('kode');
        $data['no_sspd'] = $this->input->post('no_sspd');
        $data['ppat_id'] = $this->input->post('ppat_id');
        $data['wp_nama'] = $this->input->post('wp_nama');
        $data['wp_npwp'] = $this->input->post('wp_npwp');
        $data['wp_alamat'] = $this->input->post('wp_alamat');
        $data['wp_blok_kav'] = $this->input->post('wp_blok_kav');
        $data['wp_kelurahan'] = $this->input->post('wp_kelurahan');
        $data['wp_rt'] = $this->input->post('wp_rt');
        $data['wp_rw'] = $this->input->post('wp_rw');
        $data['wp_kecamatan'] = $this->input->post('wp_kecamatan');
        $data['wp_kota'] = $this->input->post('wp_kota');
        $data['wp_provinsi'] = $this->input->post('wp_provinsi');
        $data['wp_identitas'] = $this->input->post('wp_identitas');
        $data['wp_identitaskd'] = $this->input->post('wp_identitaskd');
        $data['wp_kdpos'] = $this->input->post('wp_kdpos');
        $data['tgl_transaksi'] = $this->input->post('tgl_transaksi');
        $data['kd_propinsi'] = $this->input->post('kd_propinsi');
        $data['kd_dati2'] = $this->input->post('kd_dati2');
        $data['kd_kecamatan'] = $this->input->post('kd_kecamatan');
        $data['kd_kelurahan'] = $this->input->post('kd_kelurahan');
        $data['kd_blok'] = $this->input->post('kd_blok');
        $data['no_urut'] = $this->input->post('no_urut');
        $data['kd_jns_op'] = $this->input->post('kd_jns_op');
        $data['thn_pajak_sppt'] = $this->input->post('thn_pajak_sppt');
        $data['op_alamat'] = $this->input->post('op_alamat');
        $data['op_blok_kav'] = $this->input->post('op_blok_kav');
        $data['op_rt'] = $this->input->post('op_rt');
        $data['op_rw'] = $this->input->post('op_rw');
        $data['no_sertifikat'] = $this->input->post('no_sertifikat');
        $data['perolehan_id'] = $this->input->post('perolehan_id');

        $data['bumi_luas'] = to_decimal($this->input->post('bumi_luas'));
        $data['bumi_njop'] = to_decimal($this->input->post('bumi_njop'));
        $data['bng_luas'] = to_decimal($this->input->post('bng_luas'));
        $data['bng_njop'] = to_decimal($this->input->post('bng_njop'));
        $data['njop'] = to_decimal($this->input->post('njop'));
        $data['npop'] = to_decimal($this->input->post('npop'));
        $data['npoptkp'] = to_decimal($this->input->post('npoptkp'));
        $data['tarif'] = to_decimal($this->input->post('tarif'));
        $data['terhutang'] = to_decimal($this->input->post('terhutang'));
        $data['bagian'] = to_decimal($this->input->post('bagian'));
        $data['pembagi'] = to_decimal($this->input->post('pembagi'));
        $data['tarif_pengurang'] = to_decimal($this->input->post('tarif_pengurang'));
        $data['pengurang'] = to_decimal($this->input->post('pengurang'));
        $data['bphtb_sudah_dibayarkan'] = to_decimal($this->input->post('bphtb_sudah_dibayarkan'));
        $data['denda'] = to_decimal($this->input->post('denda'));
        $data['restitusi'] = to_decimal($this->input->post('restitusi'));
        $data['bphtb_harus_dibayarkan'] = to_decimal($this->input->post('bphtb_harus_dibayarkan'));
        $data['persen_pengurang_sendiri'] = to_decimal($this->input->post('persen_pengurang_sendiri'));
        $data['jml_pph'] = to_decimal($this->input->post('jml_pph'));

        $data['status_pembayaran'] = $this->input->post('status_pembayaran');
        $data['dasar_id'] = $this->input->post('dasar_id');
        $data['header_id'] = $this->input->post('header_id');
        $data['tgl_print'] = $this->input->post('tgl_print');
        $data['tgl_approval'] = $this->input->post('tgl_approval');
        $data['keterangan'] = $this->input->post('keterangan');
        $data['status_daftar'] = $this->input->post('status_daftar');
        $data['pp_nomor_pengurang_sendiri'] = $this->input->post('pp_nomor_pengurang_sendiri');
        $data['no_ajb'] = $this->input->post('no_ajb');
        $data['tgl_ajb'] = $this->input->post('tgl_ajb');

        // menyesuaikan ke banjar
        $data['wp_nama_asal'] = $this->input->post('wp_nama_asal');
        $data['wp_npwp_asal'] = $this->input->post('wp_npwp_asal');
        $data['wp_alamat_asal'] = $this->input->post('wp_alamat_asal');
        $data['wp_blok_kav_asal'] = $this->input->post('wp_blok_kav_asal');
        $data['wp_kelurahan_asal'] = $this->input->post('wp_kelurahan_asal');
        $data['wp_rt_asal'] = $this->input->post('wp_rt_asal');
        $data['wp_rw_asal'] = $this->input->post('wp_rw_asal');
        $data['wp_kecamatan_asal'] = $this->input->post('wp_kecamatan_asal');
        $data['wp_kota_asal'] = $this->input->post('wp_kota_asal');
        $data['wp_provinsi_asal'] = $this->input->post('wp_provinsi_asal');
        $data['wp_identitas_asal'] = $this->input->post('wp_identitas_asal');
        $data['wp_identitaskd_asal'] = $this->input->post('wp_identitaskd_asal');
        $data['wp_kdpos_asal'] = $this->input->post('wp_kdpos_asal');
        $data['status_hak_id'] = $this->input->post('status_hak_id');

        $data['tgl_pph'] = $this->input->post('tgl_pph');
        $data['posted'] = $this->input->post('posted');
        $data['pos_tp_id'] = $this->input->post('pos_tp_id');
        $data['status_validasi'] = $this->input->post('status_validasi');
        $data['status_bpn'] = $this->input->post('status_bpn');
        $data['tgl_jatuh_tempo'] = $this->input->post('tgl_jatuh_tempo');
        $data['hasil_penelitian'] = $this->input->post('hasil_penelitian');

        $data['file1'] = $this->input->post('file1');
        $data['file2'] = $this->input->post('file2');
        $data['file3'] = $this->input->post('file3');
        $data['file4'] = $this->input->post('file4');
        $data['file5'] = $this->input->post('file5');
        $data['file6'] = $this->input->post('file6');
        $data['file7'] = $this->input->post('file7');
        $data['file8'] = $this->input->post('file8');
        $data['file9'] = $this->input->post('file9');
        $data['file10'] = $this->input->post('file10');

        $data['create_uid'] = $this->input->post('create_uid');
        $data['update_uid'] = $this->input->post('update_uid');
        $data['created'] = $this->input->post('created');
        $data['updated'] = $this->input->post('updated');

        $data['no_sk'] = $this->input->post('no_sk');
        $data['ketetapan_no'] = $this->input->post('ketetapan_no');
        $data['ketetapan_tgl'] = $this->input->post('ketetapan_tgl');
        $data['ketetapan_atas_sspd_no'] = $this->input->post('ketetapan_atas_sspd_no');
        $data['ketetapan_jatuh_tempo_tgl'] = $this->input->post('ketetapan_jatuh_tempo_tgl');
        $data['pengurangan_sk'] = $this->input->post('pengurangan_sk');
        $data['pengurangan_sk_tgl'] = $this->input->post('pengurangan_sk_tgl');
        $data['pengurangan_jatuh_tempo_tgl'] = $this->input->post('pengurangan_jatuh_tempo_tgl');

        $data['sspdno'] = $this->input->post('sspdno');
        $data['nop_thn'] = $this->input->post('nop_thn');
        $data['transno'] = $this->input->post('transno');
        $data['notaris'] = $this->input->post('notaris');
        $data['tp_id'] = $this->input->post('tp_id');
        $data['jumlah_bayar'] = to_decimal($this->input->post('jumlah_bayar'));

        $data['harga_transaksi'] = to_decimal($this->input->post('harga_transaksi'));
        $data['npopkp'] = to_decimal($this->input->post('npopkp'));

        return $data;
    }

    public function bayar()
    {
        if (!$this->module_auth->update) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
            redirect(active_module_url($this->controller));
        }

        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();
        $data['faction'] = active_module_url($this->controller . '/do_bayar');

        $data['ppat']      = $this->ppat_model->get_all();
        $data['dasar']     = $this->dasar_model->get_all();
        $data['perolehan'] = $this->perolehan_model->get_all();
        $data['tp'] = $this->tp_model->get_all();
        $data['status_hak'] = $this->status_hak_model->get_all();

        $data['mode']   = 'edit';

        $id = (int) $this->uri->segment(4);
        if ($id && $get = $this->sspd_model->get($id)) {
            $nopthn  = $this->sspd_model->get_nopthn($id);
            $sspdno  = $this->sspd_model->get_sspdno($id);
            $notaris = $this->ppat_model->get($get->ppat_id)->nama;

            $data['dt']['id']      = $get->id;

            $data['dt']['transno'] = '';
            $data['dt']['tgl_transaksi'] = date('d-m-Y');

            $data['dt']['nop_thn'] = $nopthn;
            $data['dt']['sspdno']  = $sspdno;
            $data['dt']['notaris'] = $notaris;

            $data['dt']['jumlah_bayar'] = empty($get->bphtb_harus_dibayarkan) ? 0 : $get->bphtb_harus_dibayarkan;

            $data['dt']['ppat_id'] = empty($get->ppat_id) ? 0 : $get->ppat_id;
            $data['dt']['wp_nama'] = empty($get->wp_nama) ? NULL : $get->wp_nama;
            $data['dt']['wp_npwp'] = empty($get->wp_npwp) ? NULL : $get->wp_npwp;
            $data['dt']['wp_alamat'] = empty($get->wp_alamat) ? NULL : $get->wp_alamat;
            $data['dt']['wp_blok_kav'] = empty($get->wp_blok_kav) ? NULL : $get->wp_blok_kav;
            $data['dt']['wp_kelurahan'] = empty($get->wp_kelurahan) ? NULL : $get->wp_kelurahan;
            $data['dt']['wp_rt'] = empty($get->wp_rt) ? NULL : $get->wp_rt;
            $data['dt']['wp_rw'] = empty($get->wp_rw) ? NULL : $get->wp_rw;
            $data['dt']['wp_kecamatan'] = empty($get->wp_kecamatan) ? NULL : $get->wp_kecamatan;
            $data['dt']['wp_kota'] = empty($get->wp_kota) ? NULL : $get->wp_kota;
            $data['dt']['wp_provinsi'] = empty($get->wp_provinsi) ? NULL : $get->wp_provinsi;
            $data['dt']['wp_identitas'] = empty($get->wp_identitas) ? NULL : $get->wp_identitas;
            $data['dt']['wp_identitaskd'] = empty($get->wp_identitaskd) ? NULL : $get->wp_identitaskd;
            $data['dt']['kd_propinsi'] = empty($get->kd_propinsi) ? NULL : $get->kd_propinsi;
            $data['dt']['kd_dati2'] = empty($get->kd_dati2) ? NULL : $get->kd_dati2;
            $data['dt']['kd_kecamatan'] = empty($get->kd_kecamatan) ? NULL : $get->kd_kecamatan;
            $data['dt']['kd_kelurahan'] = empty($get->kd_kelurahan) ? NULL : $get->kd_kelurahan;
            $data['dt']['kd_blok'] = empty($get->kd_blok) ? NULL : $get->kd_blok;
            $data['dt']['no_urut'] = empty($get->no_urut) ? NULL : $get->no_urut;
            $data['dt']['kd_jns_op'] = empty($get->kd_jns_op) ? NULL : $get->kd_jns_op;
            $data['dt']['thn_pajak_sppt'] = empty($get->thn_pajak_sppt) ? NULL : $get->thn_pajak_sppt;
            $data['dt']['op_alamat'] = empty($get->op_alamat) ? NULL : $get->op_alamat;
            $data['dt']['op_blok_kav'] = empty($get->op_blok_kav) ? NULL : $get->op_blok_kav;
            $data['dt']['op_rt'] = empty($get->op_rt) ? NULL : $get->op_rt;
            $data['dt']['op_rw'] = empty($get->op_rw) ? NULL : $get->op_rw;
            $data['dt']['bumi_luas'] = empty($get->bumi_luas) ? 0 : $get->bumi_luas;
            $data['dt']['bumi_njop'] = empty($get->bumi_njop) ? 0 : $get->bumi_njop;
            $data['dt']['bng_luas'] = empty($get->bng_luas) ? 0 : $get->bng_luas;
            $data['dt']['bng_njop'] = empty($get->bng_njop) ? 0 : $get->bng_njop;
            $data['dt']['no_sertifikat'] = empty($get->no_sertifikat) ? NULL : $get->no_sertifikat;
            $data['dt']['njop'] = empty($get->njop) ? 0 : $get->njop;
            $data['dt']['perolehan_id'] = empty($get->perolehan_id) ? 0 : $get->perolehan_id;
            $data['dt']['npop'] = empty($get->npop) ? 0 : $get->npop;
            $data['dt']['npoptkp'] = empty($get->npoptkp) ? 0 : $get->npoptkp;
            $data['dt']['tarif'] = empty($get->tarif) ? 0 : $get->tarif;
            $data['dt']['terhutang'] = empty($get->terhutang) ? 0 : $get->terhutang;
            $data['dt']['bagian'] = empty($get->bagian) ? 0 : $get->bagian;
            $data['dt']['pembagi'] = empty($get->pembagi) ? 0 : $get->pembagi;
            $data['dt']['tarif_pengurang'] = empty($get->tarif_pengurang) ? 0 : $get->tarif_pengurang;
            $data['dt']['pengurang'] = empty($get->pengurang) ? 0 : $get->pengurang;
            $data['dt']['bphtb_sudah_dibayarkan'] = empty($get->bphtb_sudah_dibayarkan) ? 0 : $get->bphtb_sudah_dibayarkan;
            $data['dt']['denda'] = empty($get->denda) ? 0 : $get->denda;
            $data['dt']['restitusi'] = empty($get->restitusi) ? 0 : $get->restitusi;
            $data['dt']['bphtb_harus_dibayarkan'] = empty($get->bphtb_harus_dibayarkan) ? 0 : $get->bphtb_harus_dibayarkan;
            $data['dt']['status_pembayaran'] = empty($get->status_pembayaran) ? 0 : $get->status_pembayaran;
            $data['dt']['dasar_id'] = empty($get->dasar_id) ? 0 : $get->dasar_id;
            $data['dt']['create_uid'] = empty($get->create_uid) ? NULL : $get->create_uid;
            $data['dt']['update_uid'] = empty($get->update_uid) ? NULL : $get->update_uid;
            $data['dt']['created'] = empty($get->created) ? NULL : date('d-m-Y', strtotime($get->created));
            $data['dt']['updated'] = empty($get->updated) ? NULL : date('d-m-Y', strtotime($get->updated));
            $data['dt']['header_id'] = empty($get->header_id) ? 0 : $get->header_id;
            $data['dt']['tgl_print'] = empty($get->tgl_print) ? NULL : date('d-m-Y', strtotime($get->tgl_print));
            $data['dt']['tgl_approval'] = empty($get->tgl_approval) ? NULL : date('d-m-Y', strtotime($get->tgl_approval));
            $data['dt']['file1'] = empty($get->file1) ? NULL : $get->file1;
            $data['dt']['file2'] = empty($get->file2) ? NULL : $get->file2;
            $data['dt']['file3'] = empty($get->file3) ? NULL : $get->file3;
            $data['dt']['file4'] = empty($get->file4) ? NULL : $get->file4;
            $data['dt']['file5'] = empty($get->file5) ? NULL : $get->file5;
            $data['dt']['wp_kdpos'] = empty($get->wp_kdpos) ? NULL : $get->wp_kdpos;
            $data['dt']['file6'] = empty($get->file6) ? NULL : $get->file6;
            $data['dt']['file7'] = empty($get->file7) ? NULL : $get->file7;
            $data['dt']['file8'] = empty($get->file8) ? NULL : $get->file8;
            $data['dt']['file9'] = empty($get->file9) ? NULL : $get->file9;
            $data['dt']['file10'] = empty($get->file10) ? NULL : $get->file10;
            $data['dt']['keterangan'] = empty($get->keterangan) ? NULL : $get->keterangan;
            $data['dt']['status_daftar'] = empty($get->status_daftar) ? 0 : $get->status_daftar;
            $data['dt']['persen_pengurang_sendiri'] = empty($get->persen_pengurang_sendiri) ? 0 : $get->persen_pengurang_sendiri;
            $data['dt']['pp_nomor_pengurang_sendiri'] = empty($get->pp_nomor_pengurang_sendiri) ? NULL : $get->pp_nomor_pengurang_sendiri;
            $data['dt']['no_ajb'] = empty($get->no_ajb) ? NULL : $get->no_ajb;
            $data['dt']['tgl_ajb'] = empty($get->tgl_ajb) ? NULL : date('d-m-Y', strtotime($get->tgl_ajb));

            $data['dt']['wp_nama_asal'] = empty($get->wp_nama_asal) ? NULL : $get->wp_nama_asal;

            $data['dt']['jml_pph'] = empty($get->jml_pph) ? 0 : $get->jml_pph;
            $data['dt']['tgl_pph'] = empty($get->tgl_pph) ? NULL : date('d-m-Y', strtotime($get->tgl_pph));
            $data['dt']['posted'] = empty($get->posted) ? 0 : $get->posted;
            $data['dt']['pos_tp_id'] = empty($get->pos_tp_id) ? 0 : $get->pos_tp_id;
            $data['dt']['status_validasi'] = empty($get->status_validasi) ? 0 : $get->status_validasi;
            $data['dt']['status_bpn'] = empty($get->status_bpn) ? 0 : $get->status_bpn;
            $data['dt']['tgl_jatuh_tempo'] = empty($get->tgl_jatuh_tempo) ? NULL : date('d-m-Y', strtotime($get->tgl_jatuh_tempo));
            $data['dt']['hasil_penelitian'] = empty($get->hasil_penelitian) ? NULL : $get->hasil_penelitian;
            $data['dt']['no_sk'] = empty($get->no_sk) ? NULL : $get->no_sk;
            $data['dt']['ketetapan_no'] = empty($get->ketetapan_no) ? NULL : $get->ketetapan_no;
            $data['dt']['ketetapan_tgl'] = empty($get->ketetapan_tgl) ? NULL : date('d-m-Y', strtotime($get->ketetapan_tgl));
            $data['dt']['ketetapan_atas_sspd_no'] = empty($get->ketetapan_atas_sspd_no) ? NULL : $get->ketetapan_atas_sspd_no;
            $data['dt']['ketetapan_jatuh_tempo_tgl'] = empty($get->ketetapan_jatuh_tempo_tgl) ? NULL : date('d-m-Y', strtotime($get->ketetapan_jatuh_tempo_tgl));
            $data['dt']['pengurangan_sk'] = empty($get->pengurangan_sk) ? NULL : $get->pengurangan_sk;
            $data['dt']['pengurangan_sk_tgl'] = empty($get->pengurangan_sk_tgl) ? NULL : date('d-m-Y', strtotime($get->pengurangan_sk_tgl));
            $data['dt']['pengurangan_jatuh_tempo_tgl'] = empty($get->pengurangan_jatuh_tempo_tgl) ? NULL : date('d-m-Y', strtotime($get->pengurangan_jatuh_tempo_tgl));
            $data['dt']['verifikasi_uid'] = empty($get->verifikasi_uid) ? NULL : $get->verifikasi_uid;
            $data['dt']['verifikasi_date'] = empty($get->verifikasi_date) ? NULL : date('d-m-Y', strtotime($get->verifikasi_date));
            $data['dt']['pbb_nop'] = empty($get->pbb_nop) ? NULL : $get->pbb_nop;
            $data['dt']['verifikasi_bphtb_uid'] = empty($get->verifikasi_bphtb_uid) ? NULL : $get->verifikasi_bphtb_uid;
            $data['dt']['verifikasi_bphtb_date'] = empty($get->verifikasi_bphtb_date) ? NULL : date('d-m-Y', strtotime($get->verifikasi_bphtb_date));

            $data['dt']['npopkp'] = empty($get->npopkp) ? 0 : $get->npopkp;
            $data['dt']['harga_transaksi'] = empty($get->harga_transaksi) ? 0 : $get->harga_transaksi;

            if(defined('BPHTB_BANJAR') && BPHTB_BANJAR==TRUE) {
                // menyesuaikan ke banjar
                $data['dt']['wp_identitas_asal'] = empty($get->wp_identitas_asal) ? NULL : $get->wp_identitas_asal;
                $data['dt']['wp_npwp_asal'] = empty($get->wp_npwp_asal) ? NULL : $get->wp_npwp_asal;
                $data['dt']['wp_alamat_asal'] = empty($get->wp_alamat_asal) ? NULL : $get->wp_alamat_asal;
                $data['dt']['wp_blok_kav_asal'] = empty($get->wp_blok_kav_asal) ? NULL : $get->wp_blok_kav_asal;
                $data['dt']['wp_kelurahan_asal'] = empty($get->wp_kelurahan_asal) ? NULL : $get->wp_kelurahan_asal;
                $data['dt']['wp_rt_asal'] = empty($get->wp_rt_asal) ? NULL : $get->wp_rt_asal;
                $data['dt']['wp_rw_asal'] = empty($get->wp_rw_asal) ? NULL : $get->wp_rw_asal;
                $data['dt']['wp_kecamatan_asal'] = empty($get->wp_kecamatan_asal) ? NULL : $get->wp_kecamatan_asal;
                $data['dt']['wp_kota_asal'] = empty($get->wp_kota_asal) ? NULL : $get->wp_kota_asal;
                $data['dt']['wp_provinsi_asal'] = empty($get->wp_provinsi_asal) ? NULL : $get->wp_provinsi_asal;
                $data['dt']['wp_identitaskd_asal'] = empty($get->wp_identitaskd_asal) ? NULL : $get->wp_identitaskd_asal;
                $data['dt']['wp_kdpos_asal'] = empty($get->wp_kdpos_asal) ? NULL : $get->wp_kdpos_asal;
                $data['dt']['status_hak_id'] = empty($get->status_hak_id) ? 0 : $get->status_hak_id;

                $this->load->view('vpenerimaan_form_banjar', $data);
            } else
                $this->load->view('vpenerimaan_form', $data);
        } else {
            show_404();
        }
    }

    public function do_bayar()
    {
        if (!$this->module_auth->create) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_create);
            redirect(active_module_url($this->uri->segment(2)));
        }
        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();
        $data['faction'] = active_module_url($this->controller . '/do_bayar');

        $data['ppat']      = $this->ppat_model->get_all();
        $data['dasar']     = $this->dasar_model->get_all();
        $data['perolehan'] = $this->perolehan_model->get_all();
        $data['tp'] = $this->tp_model->get_all();
        $data['status_hak'] = $this->status_hak_model->get_all();

        $data['mode']   = 'edit';

        $data['dt'] = $this->fpost();

        $this->fvalidation();
        $sspd_id = $data['dt']['id'];
        if ($this->form_validation->run() == TRUE) {
            $input_post = $this->fpost();
            $nop = $this->sspd_model->get_nop($sspd_id, false);
            $tp  = $this->tp_model->get($input_post['tp_id']);

            $post_data  = array(
                'sspd_id' => $sspd_id,
                'transno' => $input_post['transno'],
                'no_tagihan' => $input_post['sspdno'],
                'notaris' => empty($input_post['notaris']) ? NULL : $input_post['notaris'],
                'tahun' => date('Y', strtotime($input_post['tgl_transaksi'])),
                'tanggal' => date('Y-m-d', strtotime($input_post['tgl_transaksi'])),
                'jam' => date('h:i:s', time()),
                'is_validated' => 0,
                'bankid' => 0,
                'seq' => 0,
                'txs' => '',

                'nop' => $nop,
                'thn_pajak_sppt' => $input_post['thn_pajak_sppt'],
                'bphtbjeniskd' => $input_post['perolehan_id'],
                'catatan' => empty($input_post['keterangan']) ? NULL : $input_post['keterangan'],

                // #field berikut ada triger di table bphtb_bank <-----------
                // 'kd_propinsi' => $input_post['kd_propinsi'],
                // 'kd_dati2' => $input_post['kd_dati2'],
                // 'kd_kecamatan' => $input_post['kd_kecamatan'],
                // 'kd_kelurahan' => $input_post['kd_kelurahan'],
                // 'kd_blok' => $input_post['kd_blok'],
                // 'no_urut' => $input_post['no_urut'],
                // 'kd_jns_op' => $input_post['kd_jns_op'],

                'wp_nama' => empty($input_post['wp_nama']) ? NULL : $input_post['wp_nama'],
                'wp_alamat' => empty($input_post['wp_alamat']) ? NULL : $input_post['wp_alamat'],
                'wp_blok_kav' => empty($input_post['wp_blok_kav']) ? NULL : $input_post['wp_blok_kav'],
                'wp_rt' => empty($input_post['wp_rt']) ? NULL : $input_post['wp_rt'],
                'wp_rw' => empty($input_post['wp_rw']) ? NULL : $input_post['wp_rw'],
                'wp_kelurahan' => empty($input_post['wp_kelurahan']) ? NULL : $input_post['wp_kelurahan'],
                'wp_kecamatan' => empty($input_post['wp_kecamatan']) ? NULL : $input_post['wp_kecamatan'],
                'wp_kota' => empty($input_post['wp_kota']) ? NULL : $input_post['wp_kota'],
                'wp_provinsi' => empty($input_post['wp_provinsi']) ? NULL : $input_post['wp_provinsi'],
                'wp_kdpos' => empty($input_post['wp_kdpos']) ? NULL : $input_post['wp_kdpos'],
                'wp_npwp' => empty($input_post['wp_npwp']) ? NULL : $input_post['wp_npwp'],
                'wp_identitas' => empty($input_post['wp_identitas']) ? NULL : $input_post['wp_identitas'],
                'wp_identitaskd' => 'KPT',

                'bumi_luas' => $input_post['bumi_luas'],
                'bumi_njop' => $input_post['bumi_njop'],
                'bng_luas' => $input_post['bng_luas'],
                'bng_njop' => $input_post['bng_njop'],
                'npop' => $input_post['npop'],

                'bayar' => $input_post['jumlah_bayar'],
                'denda' => $input_post['denda'],

                'kd_kanwil' => isset($tp->kd_kanwil) ? $tp->kd_kanwil : null,
                'kd_kantor' => isset($tp->kd_kantor) ? $tp->kd_kantor : null,
                'kd_bank_tunggal' => isset($tp->kd_bank_tunggal) ? $tp->kd_bank_tunggal : null,
                'kd_bank_persepsi' => isset($tp->kd_bank_persepsi) ? $tp->kd_bank_persepsi : null,

                'users' => $this->session->userdata('uid'),
            );

            // cek dulu kata bos aa, kalo ada ga usah diinsert
            // dan gak ngupdate jml bayar & denda
            // kensekuensinya, kalo jml bayar di tblbank = 0 kaga bakalan tampil
            // ikut ajaaa...
            if(!$this->penerimaan_model->get_by_sspd($sspd_id))
                $this->penerimaan_model->save($post_data);

            // UPDATE SPPD
            $upd_sspd = array(
                'status_daftar' => 1,
                'status_pembayaran' => 1,
                'pos_tp_id' => $input_post['tp_id'],
                'denda' => $input_post['denda'],
                // fld bphtb_sudah_dibayarkan u/ KB aja kata AA - taudah
                // 'bphtb_sudah_dibayarkan' => (float)$input_post['bphtb_sudah_dibayarkan'] + (float)$input_post['jumlah_bayar'],
            );
            $this->sspd_model->update($sspd_id, $upd_sspd);

            $this->session->set_flashdata('msg_success', 'Data telah disimpan');
            redirect(active_module_url($this->uri->segment(2)));
        }

        if(defined('BPHTB_BANJAR') && BPHTB_BANJAR==TRUE)
            $this->load->view('vpenerimaan_form_banjar', $data);
        else
            $this->load->view('vpenerimaan_form', $data);
    }

    public function reversal() {
        if (!$this->module_auth->delete) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
            redirect(active_module_url($this->controller));
        }

        $bank_id = (int) $this->uri->segment(4);
        if ($bank_id && $bank = $this->penerimaan_model->get($bank_id)) {
            //update bank
            $upd_bank = array(
                'bayar' => 0,
                'denda' => 0,
            );
            $this->penerimaan_model->update($bank_id, $upd_bank);

            //update sspd
            if ($this->sspd_model->get($bank->sspd_id)) {
                $upd_sspd = array(
                    'status_daftar' => 0,
                    'status_pembayaran' => 0,
                    'pos_tp_id' => null,
                    'denda' => 0,
                );
                $this->sspd_model->update($bank->sspd_id, $upd_sspd);
            }

            echo "ok";
            exit;
        }
        echo "not ok";
    }


    // ADDITIONAL FUNC ---------------

    function valid_date($str) {
        if (!empty($str)) {
            if (preg_match("/^(\d{1,2})[- \/.](\d{1,2})[- \/.](\d{4})$/", $str, $values)) {
                // Is a date format (dd mm yyyy)
                if (checkdate($values[2], $values[1], $values[3])) {
                    // Date really exists
                    return TRUE;
                }
            } else {
                $this->form_validation->set_message('valid_date', 'Format %s adalah dd-mm-yyyy contoh:28-02-2014.');
                return FALSE;
            }
        }
        // boleh kosong
        return TRUE;
    }

    function extract_nopthn($nopthn) {
        $nop_num = preg_replace("/[^0-9]/", "", $nopthn);
        $nop_dot = preg_replace("/([0-9]{2})([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{1})([0-9]{4})/", "$1.$2.$3.$4.$5.$6.$7.$8", $nop_num);

        $kode = explode(".", $nop_dot);
        list($dt['kd_propinsi'], $dt['kd_dati2'], $dt['kd_kecamatan'], $dt['kd_kelurahan'], $dt['kd_blok'], $dt['no_urut'], $dt['kd_jns_op'], $dt['thn_pajak_sppt']) = $kode;
        return $dt;
    }

    // END FUNC  ----------------------
}
