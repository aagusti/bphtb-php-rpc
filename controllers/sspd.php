<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Sspd extends CI_Controller
{

/*
NEED UPDATE:
tgl_approval timestamp without time zone,

status_pembayaran smallint NOT NULL DEFAULT 0,
bphtb_sudah_dibayarkan bigint NOT NULL DEFAULT 0,
pos_tp_id integer,

keterangan character varying(100),
status_daftar smallint DEFAULT 0,
status_validasi integer DEFAULT 0,
status_bpn integer DEFAULT 0,
hasil_penelitian character varying(20) DEFAULT 'Draft'::character varying, -- Draft/Sesuai/Kurang Bayar/Lebih Bayar

tgl_jatuh_tempo timestamp without time zone,
header_id bigint NOT NULL DEFAULT 0,
ketetapan_no character varying(20),
ketetapan_tgl timestamp without time zone,
ketetapan_atas_sspd_no character varying(20),
ketetapan_jatuh_tempo_tgl timestamp without time zone,


STATUS DAFTAR:
0 = 'Draft';
1 = 'Bayar';
2 = 'Approval';
3 = 'Penelitian';
4 = 'SKPD-KB';
5 = 'SKPD-LB';
6 = 'BPN';
7 = 'Selesai';
*/

    private $module     = 'sspd';
    private $controller = 'sspd';
    private $current    = 'sspd';

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
        $this->load->helper('ws_helper');

        $this->load->model(array(
            'apps_model',
            'bphtb_model',
            'sspd_model',
            'ppat_model',
            'dasar_model',
            'perolehan_model',
            'ppat_user_model',
            'tp_model',
            'status_hak_model',
        ));

        //cek is user ppat
        $query = $this->ppat_model->get_all();
        if($is_ppat = $this->ppat_user_model->getkode($this->session->userdata('uid'))) {
            $ppat  = $this->ppat_model->get_id_nama($is_ppat->kode);

            $this->session->set_userdata('isppat', true);
            $this->session->set_userdata('ppat_id', $ppat[0]);
        }

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

        if(defined('BPHTB_BANJAR') && BPHTB_BANJAR==TRUE)
            $this->load->view('vsspd_banjar', $data);
        else
            $this->load->view('vsspd', $data);
    }

    function grid()
    {
        $tgl1 = $this->uri->segment(4);
        $tgl2 = $this->uri->segment(5);

        if($tgl1=='') {
            $tgl1 = date('Y-01-01');
            $tgl2 = date('Y-m-d');;
        } else {
            $tgl1 = date('Y-m-d', strtotime($tgl1));
            $tgl2 = date('Y-m-d', strtotime($tgl2));
        }

        $this->load->library('Datatables');
        if(defined('BPHTB_BANJAR') && BPHTB_BANJAR==TRUE) {
            $this->datatables->select('ss.id, {rownum} as rownum,
                get_sspdno(ss.id) as sspdno, ss.tgl_transaksi, ss.wp_nama,
                ppat.nama as ppatnm, get_nop_sspd(ss.id,true) as nop, ss.thn_pajak_sppt, p.nama as perolehan,
                d.nama as dasar, ss.npop, ss.bphtb_harus_dibayarkan, ss.tgl_jatuh_tempo,
                coalesce(ss.status_pembayaran,0) as status_pembayaran,
                coalesce(ss.status_validasi,0) as status_permohonan,
                ss.hasil_penelitian, case when a.id is null then 0 else 1 end as approved', false);
            $this->datatables->from('bphtb.bphtb_sspd ss');
            $this->datatables->join('bphtb.bphtb_ppat ppat', 'ppat.id=ss.ppat_id');
            $this->datatables->join('bphtb.bphtb_perolehan p', 'p.id=ss.perolehan_id');
            $this->datatables->join('bphtb.bphtb_dasar d', 'd.id=ss.dasar_id');

            //nambah info approval
            $this->datatables->join('bphtb.bphtb_validasi v', 'ss.id=v.sspd_id', 'left');
            $this->datatables->join('(
                select a.*
                from bphtb.bphtb_approval a
                inner join bphtb.bphtb_approval_final af on af.approval_id = a.id
                ) a', 'a.validasi_id = v.id', 'left');

            $this->datatables->where("ss.kd_propinsi", KD_PROPINSI);
            $this->datatables->where("ss.kd_dati2", KD_DATI2);

            if($this->session->userdata('isppat'))
                $this->datatables->where("ss.ppat_id", $this->session->userdata('ppat_id'));

            if($this->input->get('sSearch') == '')
                $this->datatables->filter("date(ss.tgl_transaksi) between '{$tgl1}' and '{$tgl2}'");

            $this->datatables->date_column('3,12');
            $this->datatables->rupiah_column('10,11');
            $this->datatables->checkbox_column('13,14,16');
        } else {
            $this->datatables->select('ss.id, {rownum} as rownum,
                get_sspdno(ss.id) as sspdno, ss.tgl_transaksi, ss.wp_nama,
                ppat.nama as ppatnm, get_nop_sspd(ss.id,true) as nop, ss.thn_pajak_sppt, p.nama as perolehan,
                d.nama as dasar, ss.npop, ss.bphtb_harus_dibayarkan, ss.tgl_jatuh_tempo,
                coalesce(ss.status_pembayaran,0) as status_pembayaran,
                coalesce(ss.status_validasi,0) as status_permohonan,
                ss.hasil_penelitian, get_approvalno(coalesce(a.id,0)) as approvalno', false);
            $this->datatables->from('bphtb.bphtb_sspd ss');
            $this->datatables->join('bphtb.bphtb_ppat ppat', 'ppat.id=ss.ppat_id');
            $this->datatables->join('bphtb.bphtb_perolehan p', 'p.id=ss.perolehan_id');
            $this->datatables->join('bphtb.bphtb_dasar d', 'd.id=ss.dasar_id');

            //nambah info approval
            $this->datatables->join('bphtb.bphtb_validasi v', 'ss.id=v.sspd_id', 'left');
            $this->datatables->join('(
                select a.*
                from bphtb.bphtb_approval a
                inner join bphtb.bphtb_approval_final af on af.approval_id = a.id
                ) a', 'a.validasi_id = v.id', 'left');

            $this->datatables->where("ss.kd_propinsi", KD_PROPINSI);
            $this->datatables->where("ss.kd_dati2", KD_DATI2);

            if($this->session->userdata('isppat'))
                $this->datatables->where("ss.ppat_id", $this->session->userdata('ppat_id'));

            if($this->input->get('sSearch') == '')
                $this->datatables->filter("date(ss.tgl_transaksi) between '{$tgl1}' and '{$tgl2}'");

            $this->datatables->date_column('3,12');
            $this->datatables->rupiah_column('10,11');
            $this->datatables->checkbox_column('13,14');
        }
        echo $this->datatables->generate();
    }

    //admin
    private function fvalidation()
    {
        $this->form_validation->set_error_delimiters('<span>', '</span>');

        // $this->form_validation->set_rules('id','ID','required|numeric');
        // $this->form_validation->set_rules('header_id','Atas No.SSPD BPHTB','required|numeric');
        // $this->form_validation->set_rules('tahun','Tahun','required|numeric');
        // $this->form_validation->set_rules('kode','Kode','required|trim|max_length[1]');
        // $this->form_validation->set_rules('no_sspd','No.SSPD BPHTB','required|numeric');
        // $this->form_validation->set_rules('no_sk','No.Akta / No.SK','required|max_length[20]');
        $this->form_validation->set_rules('ppat_id','PPAT','required|numeric');
        $this->form_validation->set_rules('dasar_id','Dasar Perhitungan','required|numeric');
        $this->form_validation->set_rules('perolehan_id','Jenis Perolehan','required|numeric');
        // $this->form_validation->set_rules('pos_tp_id','Tempat Pembayaran','required|numeric');
        $this->form_validation->set_rules('tgl_transaksi','Tgl.Transaksi','required|callback_valid_date');

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
        // $this->form_validation->set_rules('wp_identitaskd','Identitas WP','required|trim|max_length[50]');

        $this->form_validation->set_rules('wp_nama_asal','Nama WP Asal','required|trim|max_length[50]');

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

        // $this->form_validation->set_rules('kd_propinsi','Kode Propinsi','required|trim|max_length[2]');
        // $this->form_validation->set_rules('kd_dati2','Kode Dati 2','required|trim|max_length[2]');
        // $this->form_validation->set_rules('kd_kecamatan','Kode Kecamatan','required|trim|max_length[3]');
        // $this->form_validation->set_rules('kd_kelurahan','Kode Kelurahan','required|trim|max_length[3]');
        // $this->form_validation->set_rules('kd_blok','Kode Blok','required|trim|max_length[3]');
        // $this->form_validation->set_rules('no_urut','No. Urut','required|trim|max_length[4]');
        // $this->form_validation->set_rules('kd_jns_op','Kode Jenis OP','required|trim|max_length[1]');
        // $this->form_validation->set_rules('thn_pajak_sppt','Tahun Pajak SPPT','required|trim|max_length[4]');

        // $this->form_validation->set_rules('njop','NJOP','required|numeric');
        // $this->form_validation->set_rules('npop','NPOP','required|numeric');
        // $this->form_validation->set_rules('bumi_luas','Luas Bumi','required|numeric');
        // $this->form_validation->set_rules('bumi_njop','NJOP Bumi','required|numeric');
        // $this->form_validation->set_rules('bng_luas','Luas Bangunan','required|numeric');
        // $this->form_validation->set_rules('bng_njop','NJOP Bangunan','required|numeric');

        // $this->form_validation->set_rules('npoptkp','NPOPTKP','required|numeric');
        // $this->form_validation->set_rules('tarif','Tarif','required|numeric');
        // $this->form_validation->set_rules('terhutang','BPHTB Terhutang','required|numeric');
        // $this->form_validation->set_rules('bagian','Bagian','required|numeric');
        // $this->form_validation->set_rules('pembagi','Pembagi','required|numeric');
        // $this->form_validation->set_rules('tarif_pengurang','Tarif Pengurang','required|numeric');
        // $this->form_validation->set_rules('pengurang','Pengurang','required|numeric');
        // $this->form_validation->set_rules('bphtb_sudah_dibayarkan','BPHTB Sudah Dibayarkan','required|numeric');
        // $this->form_validation->set_rules('denda','Denda','required|numeric');
        // $this->form_validation->set_rules('restitusi','Restitusi','numeric');
        // $this->form_validation->set_rules('bphtb_harus_dibayarkan','BPHTB yang Harus Dibayarkan','required|numeric');
        // $this->form_validation->set_rules('persen_pengurang_sendiri','Persen Pengurang Sendiri','required|numeric');

        // $this->form_validation->set_rules('no_ajb','No. AJB','required|trim|max_length[50]');
        // $this->form_validation->set_rules('tgl_ajb','Tanggal AJB','required');
        // $this->form_validation->set_rules('jml_pph','Jumlah PPH','required|numeric');
        // $this->form_validation->set_rules('tgl_pph','Tanggal PPH','required');

        $this->form_validation->set_rules('no_sertifikat','No. Sertifikat','required|trim|max_length[30]');
        // $this->form_validation->set_rules('posted','Posted','required|numeric');
        // $this->form_validation->set_rules('status_daftar','Status Daftar','required|numeric');
        // $this->form_validation->set_rules('status_pembayaran','Status Pembayaran','required|numeric');
        // $this->form_validation->set_rules('status_validasi','Status Validasi','required|numeric');
        // $this->form_validation->set_rules('status_bpn','Status BPN','required|numeric');
        // $this->form_validation->set_rules('hasil_penelitian','Hasil Penelitian','required|trim|max_length[20]');

        $this->form_validation->set_rules('pp_nomor_pengurang_sendiri','No. PP Pengurang Sendiri','trim|max_length[50]');

        // $this->form_validation->set_rules('tgl_print','Tanggal Cetak','required');
        // $this->form_validation->set_rules('tgl_approval','Tanggal Approval','required');
        // $this->form_validation->set_rules('tgl_jatuh_tempo','Tanggal Jatuh Tempo','required|callback_valid_date');
        $this->form_validation->set_rules('keterangan','Keterangan','trim|max_length[100]');

        $this->form_validation->set_rules('file1','File 1','trim|max_length[150]');
        $this->form_validation->set_rules('file2','File 2','trim|max_length[150]');
        $this->form_validation->set_rules('file3','File 3','trim|max_length[150]');
        $this->form_validation->set_rules('file4','File 4','trim|max_length[150]');
        $this->form_validation->set_rules('file5','File 5','trim|max_length[150]');
        $this->form_validation->set_rules('file6','File 6','trim|max_length[150]');
        $this->form_validation->set_rules('file7','File 7','trim|max_length[150]');
        $this->form_validation->set_rules('file8','File 8','trim|max_length[150]');
        $this->form_validation->set_rules('file9','File 9','trim|max_length[150]');
        $this->form_validation->set_rules('file10','File 10','trim|max_length[150]');

        // $this->form_validation->set_rules('create_uid','create_uid','trim|max_length[20]');
        // $this->form_validation->set_rules('update_uid','update_uid','trim|max_length[20]');
        // $this->form_validation->set_rules('created','created','required');
        // $this->form_validation->set_rules('updated','updated','required');

        // $this->form_validation->set_rules('ketetapan_no','No. Dasar Ketetapan','trim|max_length[20]');
        // $this->form_validation->set_rules('ketetapan_tgl','Tgl. Dasar Ketetapan','trim|callback_valid_date');
        // $this->form_validation->set_rules('ketetapan_atas_sspd_no','No. SSPD Dasar Ketetapan','trim|max_length[20]');
        // $this->form_validation->set_rules('ketetapan_jatuh_tempo_tgl','Tgl. Jatuh Tempo Dasar Ketetapan','trim|callback_valid_date');
        // $this->form_validation->set_rules('pengurangan_sk','No. SK Pengurangan','trim|max_length[20]');
        // $this->form_validation->set_rules('pengurangan_sk_tgl','Tgl. SK Pengurangan','trim|callback_valid_date');
        // $this->form_validation->set_rules('pengurangan_jatuh_tempo_tgl','Tgl. Jatuh Tempo Pengurangan','trim|callback_valid_date');

        $this->form_validation->set_rules('nop_thn','NOP-Thn.Pajak SPPT','trim|required');
        // $this->form_validation->set_rules('mutasi_penuh','Mutasi','trim|required');
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
        $data['mutasi_penuh'] = $this->input->post('mutasi_penuh');

        $data['harga_transaksi'] = to_decimal($this->input->post('harga_transaksi'));
        $data['npopkp'] = to_decimal($this->input->post('npopkp'));

        return $data;
    }

    public function add()
    {
        if (!$this->module_auth->create) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_create);
            redirect(active_module_url($this->controller));
        }
        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();
        $data['faction'] = active_module_url($this->controller . '/add');

        $data['ppat']      = $this->ppat_model->get_all();
        $data['dasar']     = $this->dasar_model->get_all();
        $data['perolehan'] = $this->perolehan_model->get_all();
        $data['tp'] = $this->tp_model->get_all();
        $data['status_hak'] = $this->status_hak_model->get_all();

        $data['mode'] = 'add';
        $data['dt']   = $this->fpost();

        $this->fvalidation();
        if ($this->form_validation->run() == TRUE) {
            $input_post = $this->fpost();

            // tahun no sspd ngambil dari tanggal: req by AA
            // $sspd_kode = $this->sspd_model->get_new_sspdno(date('Y'), '1');
            $sspd_kode = $this->sspd_model->get_new_sspdno(date('Y', strtotime($input_post['tgl_transaksi'])), '1');

            $tahun     = $sspd_kode[0];
            $kode      = $sspd_kode[1];
            $no_sspd   = $sspd_kode[2];

            $nop = $this->extract_nopthn($input_post['nop_thn']);

            $post_data = array(
                'tahun' => $tahun,
                'kode' => $kode,
                'no_sspd' => $no_sspd,

                'tgl_transaksi' => empty($input_post['tgl_transaksi']) ? NULL : date('Y-m-d', strtotime($input_post['tgl_transaksi'])),
                'tgl_jatuh_tempo' => empty($input_post['tgl_transaksi']) ? NULL : date('Y-m-d', strtotime($input_post['tgl_transaksi'])),

                'header_id' => 0,
                'ppat_id' => $input_post['ppat_id'],
                'dasar_id' => $input_post['dasar_id'],
                'perolehan_id' => $input_post['perolehan_id'],
                'pos_tp_id' => $input_post['pos_tp_id'],

                'kd_propinsi' => $nop['kd_propinsi'],
                'kd_dati2' => $nop['kd_dati2'],
                'kd_kecamatan' => $nop['kd_kecamatan'],
                'kd_kelurahan' => $nop['kd_kelurahan'],
                'kd_blok' => $nop['kd_blok'],
                'no_urut' => $nop['no_urut'],
                'kd_jns_op' => $nop['kd_jns_op'],
                'thn_pajak_sppt' => $nop['thn_pajak_sppt'],

                'wp_identitaskd' => 'KTP',
                'wp_nama' => empty($input_post['wp_nama']) ? NULL : $input_post['wp_nama'],
                'wp_npwp' => empty($input_post['wp_npwp']) ? NULL : $input_post['wp_npwp'],
                'wp_alamat' => empty($input_post['wp_alamat']) ? NULL : $input_post['wp_alamat'],
                'wp_blok_kav' => empty($input_post['wp_blok_kav']) ? NULL : $input_post['wp_blok_kav'],
                'wp_kelurahan' => empty($input_post['wp_kelurahan']) ? NULL : $input_post['wp_kelurahan'],
                'wp_rt' => empty($input_post['wp_rt']) ? NULL : $input_post['wp_rt'],
                'wp_rw' => empty($input_post['wp_rw']) ? NULL : $input_post['wp_rw'],
                'wp_kecamatan' => empty($input_post['wp_kecamatan']) ? NULL : $input_post['wp_kecamatan'],
                'wp_kota' => empty($input_post['wp_kota']) ? NULL : $input_post['wp_kota'],
                'wp_provinsi' => empty($input_post['wp_provinsi']) ? NULL : $input_post['wp_provinsi'],
                'wp_identitas' => empty($input_post['wp_identitas']) ? NULL : $input_post['wp_identitas'],
                'wp_kdpos' => empty($input_post['wp_kdpos']) ? NULL : $input_post['wp_kdpos'],
                'no_sertifikat' => empty($input_post['no_sertifikat']) ? NULL : $input_post['no_sertifikat'],

                'wp_nama_asal' => empty($input_post['wp_nama_asal']) ? NULL : $input_post['wp_nama_asal'],

                'op_alamat' => empty($input_post['op_alamat']) ? NULL : $input_post['op_alamat'],
                'op_blok_kav' => empty($input_post['op_blok_kav']) ? NULL : $input_post['op_blok_kav'],
                'op_rt' => empty($input_post['op_rt']) ? NULL : $input_post['op_rt'],
                'op_rw' => empty($input_post['op_rw']) ? NULL : $input_post['op_rw'],

                'bumi_luas' => $input_post['bumi_luas'],
                'bumi_njop' => $input_post['bumi_njop'],
                'bng_luas' => $input_post['bng_luas'],
                'bng_njop' => $input_post['bng_njop'],
                'njop' => $input_post['njop'],


                'npop' => $input_post['npop'],
                'npoptkp' => $input_post['npoptkp'],
                'tarif' => $input_post['tarif'],
                'terhutang' => $input_post['terhutang'],
                'bagian' => $input_post['bagian'],
                'pembagi' => $input_post['pembagi'],
                'tarif_pengurang' => $input_post['tarif_pengurang'],
                'pengurang' => $input_post['pengurang'],
                'bphtb_sudah_dibayarkan' => $input_post['bphtb_sudah_dibayarkan'],
                'denda' => $input_post['denda'],
                'restitusi' => $input_post['restitusi'],
                'bphtb_harus_dibayarkan' => $input_post['bphtb_harus_dibayarkan'],

                'keterangan' => empty($input_post['keterangan']) ? NULL : $input_post['keterangan'],
                'tgl_print' => empty($input_post['tgl_print']) ? NULL : date('Y-m-d', strtotime($input_post['tgl_print'])),
                'tgl_approval' => empty($input_post['tgl_approval']) ? NULL : date('Y-m-d', strtotime($input_post['tgl_approval'])),
                'persen_pengurang_sendiri' => empty($input_post['persen_pengurang_sendiri']) ? NULL : $input_post['persen_pengurang_sendiri'],
                'pp_nomor_pengurang_sendiri' => empty($input_post['pp_nomor_pengurang_sendiri']) ? NULL : $input_post['pp_nomor_pengurang_sendiri'],
                'no_sk' => empty($input_post['no_sk']) ? NULL : $input_post['no_sk'],
                'pengurangan_sk' => empty($input_post['pengurangan_sk']) ? NULL : $input_post['pengurangan_sk'],
                'pengurangan_sk_tgl' => empty($input_post['pengurangan_sk_tgl']) ? NULL : date('Y-m-d', strtotime($input_post['pengurangan_sk_tgl'])),
                'pengurangan_jatuh_tempo_tgl' => empty($input_post['pengurangan_jatuh_tempo_tgl']) ? NULL : date('Y-m-d', strtotime($input_post['pengurangan_jatuh_tempo_tgl'])),


                'status_daftar' => 0,
                'status_pembayaran' => 0,
                'status_validasi' => 0,
                'status_bpn' => 0,
                'posted' => 0,
                'hasil_penelitian' => 'Draft',
                'mutasi_penuh' => $input_post['mutasi_penuh']=='on'?1:0,

                'file1' => empty($input_post['file1']) ? NULL : $input_post['file1'],
                'file2' => empty($input_post['file2']) ? NULL : $input_post['file2'],
                'file3' => empty($input_post['file3']) ? NULL : $input_post['file3'],
                'file4' => empty($input_post['file4']) ? NULL : $input_post['file4'],
                'file5' => empty($input_post['file5']) ? NULL : $input_post['file5'],
                'file6' => empty($input_post['file6']) ? NULL : $input_post['file6'],
                'file7' => empty($input_post['file7']) ? NULL : $input_post['file7'],
                'file8' => empty($input_post['file8']) ? NULL : $input_post['file8'],
                'file9' => empty($input_post['file9']) ? NULL : $input_post['file9'],
                'file10' => empty($input_post['file10']) ? NULL : $input_post['file10'],

                'created' => date('Y-m-d h:m:s'),
                'create_uid' => $this->session->userdata('uid'),
            
                'npopkp' => $input_post['npopkp'],
                'harga_transaksi' => $input_post['harga_transaksi'],
            );

            if(defined('BPHTB_BANJAR') && BPHTB_BANJAR==TRUE) {
                $post_data = array_merge($post_data, array(
                    // menyesuaikan ke banjar
                    'wp_npwp_asal' => empty($input_post['wp_npwp_asal']) ? NULL : $input_post['wp_npwp_asal'],
                    'wp_alamat_asal' => empty($input_post['wp_alamat_asal']) ? NULL : $input_post['wp_alamat_asal'],
                    'wp_blok_kav_asal' => empty($input_post['wp_blok_kav_asal']) ? NULL : $input_post['wp_blok_kav_asal'],
                    'wp_kelurahan_asal' => empty($input_post['wp_kelurahan_asal']) ? NULL : $input_post['wp_kelurahan_asal'],
                    'wp_rt_asal' => empty($input_post['wp_rt_asal']) ? NULL : $input_post['wp_rt_asal'],
                    'wp_rw_asal' => empty($input_post['wp_rw_asal']) ? NULL : $input_post['wp_rw_asal'],
                    'wp_kecamatan_asal' => empty($input_post['wp_kecamatan_asal']) ? NULL : $input_post['wp_kecamatan_asal'],
                    'wp_kota_asal' => empty($input_post['wp_kota_asal']) ? NULL : $input_post['wp_kota_asal'],
                    'wp_provinsi_asal' => empty($input_post['wp_provinsi_asal']) ? NULL : $input_post['wp_provinsi_asal'],
                    'wp_identitas_asal' => empty($input_post['wp_identitas_asal']) ? NULL : $input_post['wp_identitas_asal'],
                    'wp_kdpos_asal' => empty($input_post['wp_kdpos_asal']) ? NULL : $input_post['wp_kdpos_asal'],
                    'status_hak_id' => $input_post['status_hak_id'],

                    'ketetapan_no' => empty($input_post['ketetapan_no']) ? NULL : $input_post['ketetapan_no'],
                    'ketetapan_atas_sspd_no' => empty($input_post['ketetapan_atas_sspd_no']) ? NULL : $input_post['ketetapan_atas_sspd_no'],
                    'ketetapan_tgl' => empty($input_post['ketetapan_tgl']) ? NULL : date('Y-m-d', strtotime($input_post['ketetapan_tgl'])),
                    'ketetapan_jatuh_tempo_tgl' => empty($input_post['ketetapan_jatuh_tempo_tgl']) ? NULL : date('Y-m-d', strtotime($input_post['ketetapan_jatuh_tempo_tgl'])),
                ));
            }

            $post_data = $this->sspd_model->do_upload($post_data);
            $this->sspd_model->save($post_data);

            $this->session->set_flashdata('msg_success', 'Data telah disimpan');
            redirect(active_module_url($this->controller));
        }
        $data['dt']['tgl_transaksi']   = ($data['dt']['tgl_transaksi']) ? $data['dt']['tgl_transaksi'] : date('d-m-Y');
        $data['dt']['tarif_pengurang'] = ($data['dt']['tarif_pengurang'] > 0) ? $data['dt']['tarif_pengurang'] : 0;
        $data['dt']['tarif']           = ($data['dt']['tarif'] > 0) ? $data['dt']['tarif'] : BPHTB_TARIF;
        $data['dt']['mutasi_penuh']    = 1;

        if(defined('BPHTB_BANJAR') && BPHTB_BANJAR==TRUE)
            $this->load->view('vsspd_form_banjar', $data);
        else
            $this->load->view('vsspd_form', $data);
    }

    public function edit()
    {
        if (!$this->module_auth->update) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
            redirect(active_module_url($this->controller));
        }

        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();
        $data['faction'] = active_module_url($this->controller . '/update');

        $data['ppat']      = $this->ppat_model->get_all();
        $data['dasar']     = $this->dasar_model->get_all();
        $data['perolehan'] = $this->perolehan_model->get_all();
        $data['tp']        = $this->tp_model->get_all();
        $data['status_hak'] = $this->status_hak_model->get_all();

        $data['mode']   = 'edit';

        $id = (int) $this->uri->segment(4);
        if ($id && $get = $this->sspd_model->get($id)) {
            if ((intval($get->status_pembayaran) > 0 || (int)$get->kode > 1) && !is_super_admin()) {
                $data['mode'] = 'view';
                //$this->session->set_flashdata('msg_warning', 'SSPD sudah tidak dapat diedit');
                //redirect(active_module_url($this->controller));
            }
            $nopthn = $this->sspd_model->get_nopthn($id);
            $sspdno = $this->sspd_model->get_sspdno($id);

            $data['dt']['id']      = $get->id;
            $data['dt']['tahun']   = $get->tahun;
            $data['dt']['kode']    = str_pad($get->kode, 2, "0", STR_PAD_LEFT);
            $data['dt']['no_sspd'] = str_pad($get->no_sspd, 6, "0", STR_PAD_LEFT);

            $data['dt']['nop_thn'] = $nopthn;
            $data['dt']['sspdno']  = $sspdno;
            ;
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
            $data['dt']['tgl_transaksi'] = empty($get->tgl_transaksi) ? NULL : date('d-m-Y', strtotime($get->tgl_transaksi));
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

            $data['dt']['mutasi_penuh'] = $get->mutasi_penuh;

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

                $this->load->view('vsspd_form_banjar', $data);
            } else $this->load->view('vsspd_form', $data);
        } else {
            show_404();
        }
    }

    public function update()
    {
        if (!$this->module_auth->update) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
            redirect(active_module_url($this->controller));
        }
        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();
        $data['faction'] = active_module_url($this->controller . '/update');

        $data['ppat']      = $this->ppat_model->get_all();
        $data['dasar']     = $this->dasar_model->get_all();
        $data['perolehan'] = $this->perolehan_model->get_all();
        $data['tp'] = $this->tp_model->get_all();
        $data['status_hak'] = $this->status_hak_model->get_all();

        $data['mode'] = 'edit';
        $data['dt']   = $this->fpost();

        $this->fvalidation();
        $id = $data['dt']['id'];
        if ($this->form_validation->run() == TRUE) {
            $input_post = $this->fpost();

            $nop = $this->extract_nopthn($input_post['nop_thn']);
            $post_data = array(
                'tahun' => $input_post['tahun'],
                'kode' => (int)$input_post['kode'],
                'no_sspd' => (int)$input_post['no_sspd'],

                'tgl_transaksi' => empty($input_post['tgl_transaksi']) ? NULL : date('Y-m-d', strtotime($input_post['tgl_transaksi'])),
                'tgl_jatuh_tempo' => empty($input_post['tgl_transaksi']) ? NULL : date('Y-m-d', strtotime($input_post['tgl_transaksi'])),

                // 'header_id' => 0,
                'ppat_id' => $input_post['ppat_id'],
                'dasar_id' => $input_post['dasar_id'],
                'perolehan_id' => $input_post['perolehan_id'],
                'pos_tp_id' => $input_post['pos_tp_id'],

                'kd_propinsi' => $nop['kd_propinsi'],
                'kd_dati2' => $nop['kd_dati2'],
                'kd_kecamatan' => $nop['kd_kecamatan'],
                'kd_kelurahan' => $nop['kd_kelurahan'],
                'kd_blok' => $nop['kd_blok'],
                'no_urut' => $nop['no_urut'],
                'kd_jns_op' => $nop['kd_jns_op'],
                'thn_pajak_sppt' => $nop['thn_pajak_sppt'],

                'wp_identitaskd' => 'KTP',
                'wp_nama' => empty($input_post['wp_nama']) ? NULL : $input_post['wp_nama'],
                'wp_npwp' => empty($input_post['wp_npwp']) ? NULL : $input_post['wp_npwp'],
                'wp_alamat' => empty($input_post['wp_alamat']) ? NULL : $input_post['wp_alamat'],
                'wp_blok_kav' => empty($input_post['wp_blok_kav']) ? NULL : $input_post['wp_blok_kav'],
                'wp_kelurahan' => empty($input_post['wp_kelurahan']) ? NULL : $input_post['wp_kelurahan'],
                'wp_rt' => empty($input_post['wp_rt']) ? NULL : $input_post['wp_rt'],
                'wp_rw' => empty($input_post['wp_rw']) ? NULL : $input_post['wp_rw'],
                'wp_kecamatan' => empty($input_post['wp_kecamatan']) ? NULL : $input_post['wp_kecamatan'],
                'wp_kota' => empty($input_post['wp_kota']) ? NULL : $input_post['wp_kota'],
                'wp_provinsi' => empty($input_post['wp_provinsi']) ? NULL : $input_post['wp_provinsi'],
                'wp_identitas' => empty($input_post['wp_identitas']) ? NULL : $input_post['wp_identitas'],
                'wp_kdpos' => empty($input_post['wp_kdpos']) ? NULL : $input_post['wp_kdpos'],
                'no_sertifikat' => empty($input_post['no_sertifikat']) ? NULL : $input_post['no_sertifikat'],

                'wp_nama_asal' => empty($input_post['wp_nama_asal']) ? NULL : $input_post['wp_nama_asal'],

                'op_alamat' => empty($input_post['op_alamat']) ? NULL : $input_post['op_alamat'],
                'op_blok_kav' => empty($input_post['op_blok_kav']) ? NULL : $input_post['op_blok_kav'],
                'op_rt' => empty($input_post['op_rt']) ? NULL : $input_post['op_rt'],
                'op_rw' => empty($input_post['op_rw']) ? NULL : $input_post['op_rw'],

                'bumi_luas' => $input_post['bumi_luas'],
                'bumi_njop' => $input_post['bumi_njop'],
                'bng_luas' => $input_post['bng_luas'],
                'bng_njop' => $input_post['bng_njop'],
                'njop' => $input_post['njop'],

                'npop' => $input_post['npop'],
                'npoptkp' => $input_post['npoptkp'],
                'tarif' => $input_post['tarif'],
                'terhutang' => $input_post['terhutang'],
                'bagian' => $input_post['bagian'],
                'pembagi' => $input_post['pembagi'],
                'tarif_pengurang' => $input_post['tarif_pengurang'],
                'pengurang' => $input_post['pengurang'],
                'bphtb_sudah_dibayarkan' => $input_post['bphtb_sudah_dibayarkan'],
                'denda' => $input_post['denda'],
                'restitusi' => $input_post['restitusi'],
                'bphtb_harus_dibayarkan' => $input_post['bphtb_harus_dibayarkan'],

                'keterangan' => empty($input_post['keterangan']) ? NULL : $input_post['keterangan'],
                'tgl_print' => empty($input_post['tgl_print']) ? NULL : date('Y-m-d', strtotime($input_post['tgl_print'])),
                'tgl_approval' => empty($input_post['tgl_approval']) ? NULL : date('Y-m-d', strtotime($input_post['tgl_approval'])),
                'persen_pengurang_sendiri' => empty($input_post['persen_pengurang_sendiri']) ? NULL : $input_post['persen_pengurang_sendiri'],
                'pp_nomor_pengurang_sendiri' => empty($input_post['pp_nomor_pengurang_sendiri']) ? NULL : $input_post['pp_nomor_pengurang_sendiri'],
                'no_sk' => empty($input_post['no_sk']) ? NULL : $input_post['no_sk'],
                'pengurangan_sk' => empty($input_post['pengurangan_sk']) ? NULL : $input_post['pengurangan_sk'],
                'pengurangan_sk_tgl' => empty($input_post['pengurangan_sk_tgl']) ? NULL : date('Y-m-d', strtotime($input_post['pengurangan_sk_tgl'])),
                'pengurangan_jatuh_tempo_tgl' => empty($input_post['pengurangan_jatuh_tempo_tgl']) ? NULL : date('Y-m-d', strtotime($input_post['pengurangan_jatuh_tempo_tgl'])),

                'mutasi_penuh' => $input_post['mutasi_penuh']=='on'?1:0,

                'file1' => $this->input->post('file1'),
                'file2' => $this->input->post('file2'),
                'file3' => $this->input->post('file3'),
                'file4' => $this->input->post('file4'),
                'file5' => $this->input->post('file5'),
                'file6' => $this->input->post('file6'),
                'file7' => $this->input->post('file7'),
                'file8' => $this->input->post('file8'),
                'file9' => $this->input->post('file9'),
                'file10' => $this->input->post('file10'),

                'updated' => date('Y-m-d h:m:s'),
                'update_uid' => $this->session->userdata('uid'),
                
                'npopkp' => $input_post['npopkp'],
                'harga_transaksi' => $input_post['harga_transaksi'],
            );

            if(defined('BPHTB_BANJAR') && BPHTB_BANJAR==TRUE) {
                $post_data = array_merge($post_data, array(
                    // menyesuaikan ke banjar
                    'wp_npwp_asal' => empty($input_post['wp_npwp_asal']) ? NULL : $input_post['wp_npwp_asal'],
                    'wp_alamat_asal' => empty($input_post['wp_alamat_asal']) ? NULL : $input_post['wp_alamat_asal'],
                    'wp_blok_kav_asal' => empty($input_post['wp_blok_kav_asal']) ? NULL : $input_post['wp_blok_kav_asal'],
                    'wp_kelurahan_asal' => empty($input_post['wp_kelurahan_asal']) ? NULL : $input_post['wp_kelurahan_asal'],
                    'wp_rt_asal' => empty($input_post['wp_rt_asal']) ? NULL : $input_post['wp_rt_asal'],
                    'wp_rw_asal' => empty($input_post['wp_rw_asal']) ? NULL : $input_post['wp_rw_asal'],
                    'wp_kecamatan_asal' => empty($input_post['wp_kecamatan_asal']) ? NULL : $input_post['wp_kecamatan_asal'],
                    'wp_kota_asal' => empty($input_post['wp_kota_asal']) ? NULL : $input_post['wp_kota_asal'],
                    'wp_provinsi_asal' => empty($input_post['wp_provinsi_asal']) ? NULL : $input_post['wp_provinsi_asal'],
                    'wp_identitas_asal' => empty($input_post['wp_identitas_asal']) ? NULL : $input_post['wp_identitas_asal'],
                    'wp_kdpos_asal' => empty($input_post['wp_kdpos_asal']) ? NULL : $input_post['wp_kdpos_asal'],
                    'status_hak_id' => $input_post['status_hak_id'],

                    'ketetapan_no' => empty($input_post['ketetapan_no']) ? NULL : $input_post['ketetapan_no'],
                    'ketetapan_atas_sspd_no' => empty($input_post['ketetapan_atas_sspd_no']) ? NULL : $input_post['ketetapan_atas_sspd_no'],
                    'ketetapan_tgl' => empty($input_post['ketetapan_tgl']) ? NULL : date('Y-m-d', strtotime($input_post['ketetapan_tgl'])),
                    'ketetapan_jatuh_tempo_tgl' => empty($input_post['ketetapan_jatuh_tempo_tgl']) ? NULL : date('Y-m-d', strtotime($input_post['ketetapan_jatuh_tempo_tgl'])),
                ));
            }

            $post_data = $this->sspd_model->do_upload($post_data);
            $this->sspd_model->update($id, $post_data);

            $this->session->set_flashdata('msg_success', 'Data telah disimpan');
            redirect(active_module_url($this->controller));
        }

        if(defined('BPHTB_BANJAR') && BPHTB_BANJAR==TRUE)
            $this->load->view('vsspd_form_banjar', $data);
        else
            $this->load->view('vsspd_form', $data);
    }

    public function delete()
    {
        if (!$this->module_auth->delete) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_delete);
            redirect(active_module_url($this->controller));
        }

        $id = $this->uri->segment(4);
        if ($id && $get = $this->sspd_model->get($id)) {
            if (intval($get->status_pembayaran) > 0 || ((int)$get->kode > 1 && !is_super_admin())) {
                $this->session->set_flashdata('msg_warning', 'SSPD sudah tidak dapat dihapus');
                redirect(active_module_url($this->controller));
            }

            $this->sspd_model->delete($id);
            $this->session->set_flashdata('msg_success', 'Data telah dihapus');
            redirect(active_module_url($this->controller));
        } else {
            show_404();
        }
    }


    /* Register SSPD */
    public function register() {
        if (!$this->module_auth->read) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
            redirect(active_module_url(''));
        }

        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();

        $this->load->view('vsspd_register', $data);
    }

    function grid_register() {
        $tgl1 = $this->uri->segment(4);
        $tgl2 = $this->uri->segment(5);

        if($tgl1=='') {
            $tgl1 = date('Y-01-01');
            $tgl2 = date('Y-m-d');;
        } else {
            $tgl1 = date('Y-m-d', strtotime($tgl1));
            $tgl2 = date('Y-m-d', strtotime($tgl2));
        }

        $this->load->library('Datatables');
        $this->datatables->select('ss.id, {rownum} as rownum,
            get_sspdno(ss.id) as sspdno, ss.tgl_transaksi, ss.wp_nama,
            ppat.nama as ppatnm, get_nop_sspd(ss.id,true) as nop, ss.thn_pajak_sppt,
            ss.npop, ss.bphtb_harus_dibayarkan,
            coalesce(ss.status_pembayaran,0) as status_pembayaran,
            coalesce(ss.status_validasi,0) as status_permohonan,
            ss.hasil_penelitian', false);
        $this->datatables->from('bphtb_sspd ss');
        $this->datatables->join('bphtb_ppat ppat', 'ppat.id=ss.ppat_id');
        $this->datatables->join('bphtb_perolehan p', 'p.id=ss.perolehan_id');
        $this->datatables->join('bphtb_dasar d', 'd.id=ss.dasar_id');

        $this->datatables->where("ss.kd_propinsi", KD_PROPINSI);
        $this->datatables->where("ss.kd_dati2", KD_DATI2);

        if($this->session->userdata('isppat'))
            $this->datatables->where("ss.ppat_id", $this->session->userdata('ppat_id'));

        if($this->input->get('sSearch') == '')
            $this->datatables->filter("date(ss.tgl_transaksi) between '{$tgl1}' and '{$tgl2}'");

        $this->datatables->date_column('3');
        $this->datatables->rupiah_column('8,9');
        $this->datatables->checkbox_column('10,11');
        echo $this->datatables->generate();
    }

    /* report */
    function show_rpt()
    {
        $cls_mtd_html     = $this->router->fetch_class() . "/cetak/html/";
        $cls_mtd_pdf      = $this->router->fetch_class() . "/cetak/pdf/";
        $data['rpt_html'] = active_module_url($cls_mtd_html . $_SERVER['QUERY_STRING']);
        ;
        $data['rpt_pdf'] = active_module_url($cls_mtd_pdf . $_SERVER['QUERY_STRING']);
        ;
        $this->load->view('vjasper_viewer', $data);
    }

    function cetak()
    {
        $type = 'pdf'; //$this->uri->segment(4);
        $rptx = $this->uri->segment(5);

        if ($rptx == 'bphtb_ppat_register') {
            $from = date('Y-m-d', strtotime($this->uri->segment(6)));
            $to   = date('Y-m-d', strtotime($this->uri->segment(7)));
            $src  = $this->uri->segment(8);

            $where = " where (date(bphtb_sspd.tgl_transaksi) between '{$from}' and '{$to}') ";

            if($this->session->userdata('isppat'))
                $where .= " and (bphtb_sspd.ppat_id=" . $this->session->userdata('ppat_id') . ") ";

            if ($src!='') {
                $src = strtolower($src);
                $where .= " and ((lower(bphtb_ppat.kode) like '%" . $src . "%') or (lower(bphtb_ppat.nama) like '" . $src . "%') or
                            (lower(bphtb_sspd.wp_nama) like '%" . $src . "%') or
                            (lower(cast(bphtb_sspd.kd_propinsi || '.' || bphtb_sspd.kd_dati2 || '.' || bphtb_sspd.kd_kecamatan || '.' ||
                            bphtb_sspd.kd_kelurahan || '.' || bphtb_sspd.kd_blok || '-' || bphtb_sspd.no_urut || '.' ||
                            bphtb_sspd.kd_jns_op as varchar)) like '%" . $src . "%')) ";
            }

            $order = " order by bphtb_sspd.tgl_transaksi, bphtb_sspd.ppat_id, bphtb_sspd.tahun, bphtb_sspd.kode, bphtb_sspd.no_sspd ";

            $params = array(
                "kondisi" => $where,
                "order" => $order,
                "daerah" => LICENSE_TO,
                "ibu_kota" => LICENSE_TO_SUB,
                "dinas" => LICENSE_TO_SUB,
            );
                
        } else {
            $id  = $this->uri->segment(6);
            $get = $this->sspd_model->get($id);
            $terbilang = terbilang($get->bphtb_harus_dibayarkan, 3) . " Rupiah";

            $params = array(
                "daerah" => LICENSE_TO,
                "kondisi" => " where bphtb_sspd.id = $id ",
                "terbilang" => $terbilang,
                //"logo" => base_url("assets/img/logorpt__.jpg"),
                "logo" => $_SERVER["DOCUMENT_ROOT"]."/assets/img/logorpt__.jpg",
                "dinas" => LICENSE_TO_SUB
            );
        }
//print_r($params);die();
        $jasper = $this->load->library('Jasper');
       	echo $jasper->cetak($rptx, $params, $type, false);
//       	echo $jasper->query_debug($rptx, $params);
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

    function cari_wp() {
        $id = $this->uri->segment(4);
        // nyari wp di bphtb_validasi
        $data = $this->bphtb_model->get_wp_by_identitas($id);
        echo json_encode($data);
    }

    function cari_nop() {
        $nop = $this->uri->segment(4);
        $nop = urldecode($nop);
        $nop = preg_replace( '/[^0-9]/', '', $nop);

        // print_r($nop);
        $data = NULL;
        if (strlen($nop) == 22) {
          if (PBB_USE_RPC){
              
              $result1 = ws_get_dop(substr($nop,0, -4));
              $result2 = ws_get_sppt(substr($nop,0, -4),substr($nop,-4));
              $data = Null;
              if ($result1['result']['code']==0 and $result2['result']['code']==0 ){
                  $data = (object) array_merge((array) $result1['result']['params']['data'][0], 
                                           (array) $result2['result']['params']['data'][0]);
              };
              echo json_encode($data);
          }else{
            $kd_propinsi    = substr($nop, 0, 2);
            $kd_dati2       = substr($nop, 2, 2);
            $kd_kecamatan   = substr($nop, 4, 3);
            $kd_kelurahan   = substr($nop, 7, 3);
            $kd_blok        = substr($nop, 10, 3);
            $no_urut        = substr($nop, 13, 4);
            $kd_jns_op      = substr($nop, 17, 1);
            $thn_pajak_sppt = substr($nop, 18, 4);

            // ngambil dari ppb.dat_objek_pajak
            $get1 = $this->bphtb_model->get_data_op_from_dop($kd_propinsi, $kd_dati2, $kd_kecamatan, $kd_kelurahan, $kd_blok, $no_urut, $kd_jns_op);
            // ngambil dari sppt
            $get2 = $this->bphtb_model->get_data_op_from_sppt($kd_propinsi, $kd_dati2, $kd_kecamatan, $kd_kelurahan, $kd_blok, $no_urut, $kd_jns_op, $thn_pajak_sppt);

            if (!$get1 && !$get2)
                $data = NULL;
            if ($get1 && $get2)
                $data = (object) array_merge((array) $get1, (array) $get2);
            if ($get1 && !$get2)
                $data = (object) $get1;
            if (!$get1 && $get2)
                $data = (object) $get2;

            echo json_encode($data);
          } 
        } else
            echo json_encode($data);
    }

    function get_npoptkp() {
        $id = $this->uri->segment(4) ? $this->uri->segment(4) : 0;
        if($data = $this->bphtb_model->get_npoptkp($id)) {
            $result =  $data;
        } else {
            $result['npoptkp'] = 0;
            $result['tarif_pengurang'] = 0;
        }
        echo json_encode($result);
    }

    function extract_nopthn($nopthn) {
        $nop_num = preg_replace("/[^0-9]/", "", $nopthn);
        $nop_dot = preg_replace("/([0-9]{2})([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{1})([0-9]{4})/", "$1.$2.$3.$4.$5.$6.$7.$8", $nop_num);

        $kode = explode(".", $nop_dot);
        list($dt['kd_propinsi'], $dt['kd_dati2'], $dt['kd_kecamatan'], $dt['kd_kelurahan'], $dt['kd_blok'], $dt['no_urut'], $dt['kd_jns_op'], $dt['thn_pajak_sppt']) = $kode;
        return $dt;
    }

    function cek_piutang($nop) {
        // $nop = urldecode($nop);
        $nop = preg_replace( '/[^0-9]/', '', $nop);

        // print_r($nop);

        $piutang   =0;
        $realisasi =0;

        $kd_propinsi    = substr($nop, 0, 2);
        $kd_dati2       = substr($nop, 2, 2);
        $kd_kecamatan   = substr($nop, 4, 3);
        $kd_kelurahan   = substr($nop, 7, 3);
        $kd_blok        = substr($nop, 10, 3);
        $no_urut        = substr($nop, 13, 4);
        $kd_jns_op      = substr($nop, 17, 1);

        $sql = 'select sum(pbb_yg_harus_dibayar_sppt) piutang
            from sppt
            where kd_propinsi=?
            and kd_dati2=?
            and kd_kecamatan=?
            and kd_kelurahan=?
            and kd_blok=?
            and no_urut=?
            and kd_jns_op=?
            and thn_pajak_sppt between ? and ?';
        $piutang = $this->db->query($sql, array($kd_propinsi,$kd_dati2,$kd_kecamatan,$kd_kelurahan,$kd_blok,$no_urut,$kd_jns_op,strval(date('Y')-5),date('Y')))->row()->piutang;

        $sql = 'select sum(coalesce(jml_sppt_yg_dibayar,0)-coalesce(denda_sppt,0)) realisasi
            from pembayaran_sppt
            where kd_propinsi=?
            and kd_dati2=?
            and kd_kecamatan=?
            and kd_kelurahan=?
            and kd_blok=?
            and no_urut=?
            and kd_jns_op=?
            and thn_pajak_sppt between ? and ?';
        $realisasi = $this->db->query($sql, array($kd_propinsi,$kd_dati2,$kd_kecamatan,$kd_kelurahan,$kd_blok,$no_urut,$kd_jns_op,strval(date('Y')-5),date('Y')))->row()->realisasi;

        $sisa = (int)$piutang-(int)$realisasi;

        echo (int)$sisa;
    }

    // END FUNC  ----------------------
}
