<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Berkas_in extends CI_Controller
{
    private $module = 'pelayanan';
    private $controller = 'berkas_in';
    private $current = 'pelayanan';

	function __construct() {
		parent::__construct();
		if(!$this->session->userdata('login')) {
			$this->session->set_flashdata('msg_warning', 'Session telah kadaluarsa, silahkan login ulang.');
			redirect('login');
			exit;
		}

		$this->load->library('module_auth',array(
            'module'=>$this->module
        ));

		$this->load->model(array(
            'apps_model',
            'berkas_in_model',
            'ppat_model',
            'sspd_model'
        ));

		$this->load->helper(active_module());
	}

	public function index() {
		if(!$this->module_auth->read) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
			redirect(active_module_url(''));
		}

        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();

		$this->load->view('vberkas_in', $data);
	}

	function grid() {
        $tgl1 = $this->uri->segment(4);
        $tgl2 = $this->uri->segment(5);

        if($tgl1=='') {
            $tgl1 = date('Y-01-01');
            $tgl2 = date('Y-m-d');
        } else {
            $tgl1 = date('Y-m-d', strtotime($tgl1));
            $tgl2 = date('Y-m-d', strtotime($tgl2));
        }

        $this->load->library('Datatables');
        $this->datatables->select('bi.id, {rownum} as rownum, get_berkasmasukno(bi.id) as berkasno, 
            bi.tgl_terima, ppat.nama, bi.pengirim, 
            (select count(*) from bphtb_berkas_in_det where berkas_in_id=bi.id) as jml_berkas,
            bi.notes', false);
        $this->datatables->from('bphtb_berkas_in bi');
        $this->datatables->join('bphtb_ppat ppat', 'ppat.id=bi.ppat_id');

        if($this->input->get('sSearch') == '')
            $this->datatables->filter("date(bi.tgl_terima) between '{$tgl1}' and '{$tgl2}'");

        $this->datatables->date_column('3');
        $this->datatables->numeric_column('6');
        echo $this->datatables->generate();
	}

	function grid_detail() {
        $berkas_in_id = $this->uri->segment(4);

        $this->load->library('Datatables');
        $this->datatables->select('ss.id, {rownum} as rownum,
            get_sspdno(ss.id) as sspd_no, get_nop_thn_sspd(ss.id, true) as nopthn,
            ss.wp_nama, coalesce(b.bayar,0) as bayar', false);
        $this->datatables->from('bphtb_berkas_in_det bid');
        $this->datatables->join('bphtb_sspd as ss', 'ss.id=bid.sspd_id');
        $this->datatables->join('bphtb_bank b', 'b.sspd_id=ss.id', 'left');

        $this->datatables->where("bid.berkas_in_id", $berkas_in_id);

        $this->datatables->add_column('Hapus', '<a class="delete" href="">Hapus</a>');

        $this->datatables->rupiah_column('5');
        echo $this->datatables->generate();
	}

	function grid_sspd() {
        $ppat_id = $this->uri->segment(4);

        $this->load->library('Datatables');
        $this->datatables->select('ss.id, {rownum} as rownum,
            get_sspdno(ss.id) as sspd_no, get_nop_thn_sspd(ss.id, true) as nopthn,
            ss.wp_nama, coalesce(b.bayar,0) as bayar', false);
        $this->datatables->from('bphtb_sspd as ss');
        $this->datatables->join('bphtb_bank b', 'b.sspd_id=ss.id', 'left');

        $this->datatables->where("(ss.status_pembayaran > 0 or ss.bphtb_harus_dibayarkan = 0)");
        $this->datatables->where("ss.ppat_id", $ppat_id);
        $this->datatables->where("ss.id not in (select sspd_id from bphtb_berkas_in_det)");

        $this->datatables->rupiah_column('5');
        echo $this->datatables->generate();
	}

	//admin
	private function fvalidation() {
		$this->form_validation->set_error_delimiters('<span>', '</span>');
		$this->form_validation->set_rules('ppat_id','PPAT','required|numeric');
		$this->form_validation->set_rules('pengirim','Pengirim','required|trim');
		$this->form_validation->set_rules('tgl_terima','Tgl. Terima','required|callback_valid_date');
		// $this->form_validation->set_rules('tgl_selesai','Tgl. Selesai','required|callback_valid_date');
	}

	private function fpost() {
        $data['id'] = $this->input->post('id');
		$data['tahun'] = $this->input->post('tahun');
		$data['kode'] = $this->input->post('kode');
		$data['no_urut'] = $this->input->post('no_urut');
		$data['tgl_terima'] = $this->input->post('tgl_terima');
		$data['tgl_selesai'] = $this->input->post('tgl_selesai');
		$data['notes'] = $this->input->post('notes');
		$data['pengirim'] = $this->input->post('pengirim');
		$data['create_uid'] = $this->input->post('create_uid');
		$data['update_uid'] = $this->input->post('update_uid');
		$data['created'] = $this->input->post('created');
		$data['updated'] = $this->input->post('updated');
		$data['ppat_id'] = $this->input->post('ppat_id');

		$data['berkasno'] = $this->input->post('berkasno');

		return $data;
	}

	public function add() {
		if(!$this->module_auth->create) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_create);
			redirect(active_module_url($this->uri->segment(2)));
		}
        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();
        $data['faction'] = active_module_url($this->controller . '/add');

        $data['ppat'] = $this->ppat_model->get_all();

        $data['mode'] = 'add';
        $data['dt']   = $this->fpost();

        $this->fvalidation();
		if ($this->form_validation->run() == TRUE) {
            $sspd_kode = $this->berkas_in_model->get_new_no_urut(date('Y'), '1');
            $tahun     = $sspd_kode[0];
            $kode      = $sspd_kode[1];
            $no_urut   = $sspd_kode[2];

			$input_post = $this->fpost();
			$post_data = array(
				'tahun' => $tahun,
				'kode' => $kode,
				'no_urut' => (int) $no_urut,
				'tgl_terima' => empty($input_post['tgl_terima']) ? NULL : date('Y-m-d', strtotime($input_post['tgl_terima'])),
				'tgl_selesai' => empty($input_post['tgl_selesai']) ? NULL : date('Y-m-d', strtotime($input_post['tgl_selesai'])),
				'notes' => empty($input_post['notes']) ? NULL : $input_post['notes'],
				'pengirim' => empty($input_post['pengirim']) ? NULL : $input_post['pengirim'],
				'ppat_id' => empty($input_post['ppat_id']) ? NULL : $input_post['ppat_id'],
				'create_uid' => $this->session->userdata('uid'),
				'created' => date('Y-m-d'),
			);
			$berkas_in_id = $this->berkas_in_model->save($post_data);

			// data  detail
			$data_dtl = $this->input->post('dtDetail');
			$tambahan_data2 = array();

			if(isset($data_dtl)) {
				$i = 1;
				$data_dtl = json_decode($data_dtl, true);

				//hapus dulu disini
				$this->db->delete('bphtb_berkas_in_det', array('berkas_in_id' => $berkas_in_id));
				if(count($data_dtl['dtDetail']) > 0){
					$rd_row = array();
					foreach($data_dtl['dtDetail'] as $rows) {
						$rd_row = array (
							'berkas_in_id'   => (int) $berkas_in_id,
							'sspd_id'        => (int) $rows[0],
							'sspd_no'  => (int) substr($rows[2],-6),

							'kd_propinsi'    => substr($rows[3],0 ,2), //32.03.030.001.001.0205.0-2012
							'kd_dati2'       => substr($rows[3],3 ,2),
							'kd_kecamatan'   => substr($rows[3],6 ,3),
							'kd_kelurahan'   => substr($rows[3],10,3),
							'kd_blok'        => substr($rows[3],14,3),
							'no_urut'        => substr($rows[3],18,4),
							'kd_jns_op'      => substr($rows[3],23,1),
							'thn_pajak_sppt' => substr($rows[3],25,4),
							'nominal'        => (float) str_replace('.','', $rows[5]),
						);
						$i++;
						$tambahan_data2 = array_merge($tambahan_data2, array($rd_row));
                        
					}

					//langsung ajah dah - sementara
					$this->db->insert_batch('bphtb_berkas_in_det', $tambahan_data2);
				}
			}

            $this->session->set_flashdata('msg_success', 'Data telah disimpan');
            redirect(active_module_url($this->controller));
		}
		$this->load->view('vberkas_in_form',$data);
	}

	public function edit() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url($this->uri->segment(2)));
		}

        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();
        $data['faction'] = active_module_url($this->controller . '/update');

        $data['ppat']      = $this->ppat_model->get_all();

        $id = (int) $this->uri->segment(4);
		if($id && $get = $this->berkas_in_model->get($id)) {
            $berkasno = $this->berkas_in_model->get_berkasno($id);
			$data['dt']['berkasno'] = $berkasno;

			$data['dt']['id'] = empty($get->id) ? NULL : $get->id;
			$data['dt']['tahun'] = empty($get->tahun) ? date('Y') : $get->tahun;
			$data['dt']['kode'] = str_pad($get->kode, 2, "0", STR_PAD_LEFT);
			$data['dt']['no_urut'] = str_pad($get->no_urut, 6, "0", STR_PAD_LEFT);
			$data['dt']['tgl_terima'] = empty($get->tgl_terima) ? NULL : date('d-m-Y', strtotime($get->tgl_terima));
			$data['dt']['tgl_selesai'] = empty($get->tgl_selesai) ? NULL : date('d-m-Y', strtotime($get->tgl_selesai));
			$data['dt']['notes'] = empty($get->notes) ? NULL : $get->notes;
			$data['dt']['pengirim'] = empty($get->pengirim) ? NULL : $get->pengirim;
			$data['dt']['create_uid'] = empty($get->create_uid) ? NULL : $get->create_uid;
			$data['dt']['update_uid'] = empty($get->update_uid) ? NULL : $get->update_uid;
			$data['dt']['created'] = empty($get->created) ? NULL : date('d-m-Y', strtotime($get->created));
			$data['dt']['updated'] = empty($get->updated) ? NULL : date('d-m-Y', strtotime($get->updated));
			$data['dt']['ppat_id'] = empty($get->ppat_id) ? NULL : $get->ppat_id;

			$this->load->view('vberkas_in_form',$data);
		} else {
			show_404();
		}
	}

	public function update() {
		if(!$this->module_auth->update) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_update);
			redirect(active_module_url($this->uri->segment(2)));
		}

        $data['current'] = $this->current;
        $data['apps']    = $this->apps_model->get_active_only();
        $data['faction'] = active_module_url($this->controller . '/update');

        $data['ppat'] = $this->ppat_model->get_all();
		$data['dt']   = $this->fpost();

		$this->fvalidation();
		if ($this->form_validation->run() == TRUE) {
			$input_post = $this->fpost();
			$post_data = array(
				'tgl_terima' => empty($input_post['tgl_terima']) ? NULL : date('Y-m-d', strtotime($input_post['tgl_terima'])),
				'tgl_selesai' => empty($input_post['tgl_selesai']) ? NULL : date('Y-m-d', strtotime($input_post['tgl_selesai'])),
				'notes' => empty($input_post['notes']) ? NULL : $input_post['notes'],
				'pengirim' => empty($input_post['pengirim']) ? NULL : $input_post['pengirim'],
				'ppat_id' => empty($input_post['ppat_id']) ? NULL : $input_post['ppat_id'],
				'update_uid' => $this->session->userdata('uid'),
				'updated' => date('Y-m-d'),
			);
			$this->berkas_in_model->update($input_post['id'], $post_data);

			$berkas_in_id = $input_post['id'];

			// data  detail
			$data_dtl = $this->input->post('dtDetail');
			$tambahan_data2 = array();

			if(isset($data_dtl)) {
				$i = 1;
				$data_dtl = json_decode($data_dtl, true);

				//hapus dulu disini
				$this->db->delete('bphtb_berkas_in_det', array('berkas_in_id' => $input_post['id']));
				if(count($data_dtl['dtDetail']) > 0){
					$rd_row = array();
					foreach($data_dtl['dtDetail'] as $rows) {
						$rd_row = array (
							'berkas_in_id'   => (int) $berkas_in_id,
							'sspd_id'        => (int) $rows[0],
							'sspd_no'  => (int) substr($rows[2],-6),

							'kd_propinsi'    => substr($rows[3],0 ,2), //32.03.030.001.001.0205.0-2012
							'kd_dati2'       => substr($rows[3],3 ,2),
							'kd_kecamatan'   => substr($rows[3],6 ,3),
							'kd_kelurahan'   => substr($rows[3],10,3),
							'kd_blok'        => substr($rows[3],14,3),
							'no_urut'        => substr($rows[3],18,4),
							'kd_jns_op'      => substr($rows[3],23,1),
							'thn_pajak_sppt' => substr($rows[3],25,4),
							'nominal'        => (float) str_replace('.','', $rows[5]),
						);
						$i++;
						$tambahan_data2 = array_merge($tambahan_data2, array($rd_row));
					}

					//langsung ajah dah - sementara
					$this->db->insert_batch('bphtb_berkas_in_det', $tambahan_data2);
				}
			}

			$this->session->set_flashdata('msg_success', 'Data telah disimpan');
			redirect(active_module_url($this->uri->segment(2)));
		}
		$this->load->view('vberkas_in_form',$data);
	}

	public function delete() {
		if(!$this->module_auth->delete) {
			$this->session->set_flashdata('msg_warning', $this->module_auth->msg_delete);
			redirect(active_module_url($this->uri->segment(2)));
		}

		$id = $this->uri->segment(4);
		if($id && $this->berkas_in_model->get($id)) {
			$this->db->delete('bphtb_berkas_in_det', array('berkas_in_id' => $id));

			$this->berkas_in_model->delete($id);
			$this->session->set_flashdata('msg_success', 'Data telah dihapus');
			redirect(active_module_url($this->uri->segment(2)));
		} else {
			show_404();
		}
	}

	/* laporan */
    function lap() {
        if (!$this->module_auth->read) {
            $this->session->set_flashdata('msg_warning', $this->module_auth->msg_read);
            redirect(active_module_url(''));
        }

        $data['apps']      = $this->apps_model->get_active_only();
		$data['current']   = 'pelayanan';
        $data['judul_lap'] = 'Laporan Register Berkas Masuk';
        $data['rpt']       = "berkas_in";

		$tglawal  = date('d-m-Y');
		$tglakhir = date('d-m-Y');
        $data['tglawal']  = $tglawal;
        $data['tglakhir'] = $tglakhir;

        $this->load->view('vlap_berkas', $data);
    }

	/* report */
	function show_rpt() {
		$cls_mtd_html = $this->router->fetch_class()."/cetak/html/";
		$cls_mtd_pdf  = $this->router->fetch_class()."/cetak/pdf/";
		$data['rpt_html'] = active_module_url($cls_mtd_html. $_SERVER['QUERY_STRING']);;
		$data['rpt_pdf']  = active_module_url($cls_mtd_pdf . $_SERVER['QUERY_STRING']);;
        $this->load->view('vjasper_viewer', $data);
	}

	function cetak() {
        $type = $this->uri->segment(4);
        $rptx = $this->uri->segment(5);
        
        if($rptx!='berkas_in_tt') {
            $tglm = $this->uri->segment(6);
            $tgls = $this->uri->segment(7);

            $tglm = substr($tglm, 6, 4) . '-' . substr($tglm, 3, 2) . '-' . substr($tglm, 0, 2);
            $tgls = substr($tgls, 6, 4) . '-' . substr($tgls, 3, 2) . '-' . substr($tgls, 0, 2);

            $jasper = $this->load->library('Jasper');
            $params = array(
                "startdate" => "{$tglm}",
                "enddate" => "{$tgls}",
                "logo" => base_url("assets/img/logorpt__.jpg"),
            );
        } else {
            $id = $this->uri->segment(6);

            $jasper = $this->load->library('Jasper');
            $params = array(
                "daerah" => LICENSE_TO,
                "ibu_kota" => LICENSE_TO_SUB,
                "dinas" => LICENSE_TO_SUB,
                "logo" => base_url("assets/img/logorpt__.jpg"),
                // "logo" => $_SERVER["DOCUMENT_ROOT"]."/assets/img/logorpt__.jpg",
                
                "berkas_id" => $id,
                "user_nm" => sipkd_user_name(),
            );
        }
        
        
		echo $jasper->cetak($rptx, $params, $type, false);
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

    // END FUNC  ----------------------
}
