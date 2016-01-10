<style>
	@media (min-width: 979px) {
		.wekeke{
			 margin-top: -2px !important;
			 width:100%;
			 position:fixed;
		}
		.navbar-inner {
			 border: 0px !important;
			 border-radius: 0px !important;
		}
		footer .container-fluid {
			width:100%;
			background: #000 !important;
		}
		footer .container-fluid p {
			float: right;
			margin-right: 40px;
			margin-top: 2px;
			margin-bottom: 2px;
		}
	}
	.nav-tabs {
		margin-bottom: 6px;
	}
</style>
<div class="navbar navbar-inverse wekeke" style="z-index:1029; ">
  <div class="navbar-inner">
    <button style="margin-top:8px;" class="btn btn-navbar collapsed" data-target=".nav-collapse" 
            data-toggle="collapse" type="button">
        <span class="icon-bar" style="margin-bottom:4px;height:3px;"></span>
        <span class="icon-bar" style="margin-bottom:4px;height:3px;"></span>
        <span class="icon-bar" style="margin-bottom:4px;height:3px;"></span>
    </button>
    
    <a class="brand hidden-desktop" href="<?=active_module_url();?>"><?=module_name();?></a>
    
    <div class="container-fluid">
			<? if(is_login()) :?>
      <div class="nav-collapse collapse">
          <ul class="nav">
              <li <?echo $current=='beranda' ? 'class="active"' : '';?>>
                  <a class="brand visible-desktop" href="<?=active_module_url();?>"><?=module_name();?></a>
              </li>

          <!-- PPAT -->
              <li class="dropdown <?echo $current=='sspd' ? 'active' : '';?>">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">SSPD<strong class="caret"></strong></a>
                  <ul class="dropdown-menu">
                      <li><a href="<?=active_module_url('sspd');?>">Registrasi SSPD</a></li>
                      <li><a href="<?=active_module_url('sspd_kb');?>">Registrasi SSPD KB</a></li>
                      <li><a href="<?=active_module_url('sspd_register');?>">Register SSPD</a></li>
                      <li><a href="<?=active_module_url();?>pasaran">Harga Pasar</a></li>
                      <? //if ((defined('INTEGRASI_PBB_BPHTB')) && (INTEGRASI_PBB_BPHTB==1)) :?>
                      <? if (!($this->session->userdata('isppat'))) : ?>
                      <li class="divider"></li>
                      <li><a href="<?=active_module_url();?>ajb">Entri AJB</a></li>
                      <!--li><a href="<?=active_module_url();?>ajb_verifikasi">Verifikasi AJB</a></li-->
                      <? endif;?>
                  </ul>
              </li>
    
    
              <!-- NON PPAT -->
              <? if (!($this->session->userdata('isppat'))) : ?>
              <li class="dropdown <?echo $current=='penerimaan' ? 'active' : '';?>">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Penerimaan<strong class="caret"></strong></a>
                  <ul class="dropdown-menu">
                      <li><a href="<?=active_module_url();?>penerimaan">Daftar Penerimaan</a></li>
                      <li class="nav-header">Laporan</li>
                      <li><a href="<?=active_module_url('lap_penerimaan/harian');?>">Register Pembayaran Harian</a></li>
                      <li><a href="<?=active_module_url('lap_penerimaan/harian_kel');?>">Rekapitulasi Per Kelurahan</a></li>
                      <li><a href="<?=active_module_url('lap_penerimaan/harian_kec');?>">Rekapitulasi Per Kecamatan</a></li>
                      <li><a href="<?=active_module_url('lap_penerimaan/harian_kab');?>">Rekapitulasi Per Kabupaten</a></li>
                  </ul>
              </li>
                
              <li class="dropdown <?echo $current=='pelayanan' ? 'active' : '';?>">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Pelayanan<strong class="caret"></strong></a>
                  <ul class="dropdown-menu">
                      <li><a href="<?=active_module_url('berkas_in');?>">Berkas Masuk</a></li>
                      <li><a href="<?=active_module_url('berkas_out');?>">Berkas Keluar</a></li>
                      <li class="divider"></li>
                      <li><a href="<?=active_module_url('sspd_approval');?>">SSPD Approval</a></li>
                      <li class="nav-header">Laporan</li>
                      <li><a href="<?=active_module_url('lap_berkas/masuk');?>">Register Penerimaan Berkas</a></li>
                      <li><a href="<?=active_module_url('lap_berkas/keluar');?>">Register Berkas Keluar</a></li>
                  </ul>
              </li>
        
            <li class="dropdown <?echo $current=='penelitian' ? 'active' : '';?>">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Penelitian / Penetapan<strong class="caret"></strong></a>
                  <ul class="dropdown-menu">
                      <li><a href="<?=active_module_url('penelitian');?>">Penelitian SSPD</a></li>
                      <!--li><a href="<?=active_module_url('penelitian_nihil');?>">Penelitian SSPD Bayar</a></li-->
                      <li><a href="<?=active_module_url();?>penetapan">Penetapan SKPD-KB/LB</a></li>
                      <li class="divider"></li>
                      <li><a href="<?=active_module_url();?>approval">Approval</a></li>
                      <li class="nav-header">Laporan</li>
                      <li><a href="<?=active_module_url('lap_penelitian/teliti_ok');?>">Register Penelitian SSPD</a></li>
                      <li><a href="<?=active_module_url('lap_penelitian/teliti_no');?>">Register SSPD yang belum diteliti</a></li>
                      <li><a href="<?=active_module_url('lap_penelitian/penetapan');?>">Register Penetapan</a></li>
                   </ul>
              </li>
          
          <li class="dropdown <?echo $current=='bpn' ? 'active' : '';?>">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">BPN<strong class="caret"></strong></a>
                  <ul class="dropdown-menu">
                      <li><a href="<?=active_module_url('bpn_penerimaan');?>">Register Penerimaan SSPD</a></li>
                      <li><a href="<?=active_module_url('bpn_penyelesaian');?>">Register Penyelesaian Berkas</a></li>
                      <li class="nav-header">Laporan</li>
                      <li><a href="<?=active_module_url('lap_penerimaan/harian_not_register');?>">Dokumen Dalam Proses</a></li>
                      <li><a href="<?=active_module_url('lap_penerimaan/harian_yes_register');?>">Dokumen Selesai</a></li>
                   </ul>
              </li>
              
              <?if ((defined('INTEGRASI_PBB_BPHTB')) && (INTEGRASI_PBB_BPHTB==1)) :?>
              <li class="dropdown <?echo $current=='pbb' ? 'active' : '';?>">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">PBB<strong class="caret"></strong></a>
                  <ul class="dropdown-menu">
                      <li><a href="<?=active_module_url();?>bphtb_verifikasi">Verifikasi BPHTB</a></li>
                      <li><a href="<?=active_module_url();?>bphtb_unposted">BPHTB Unposted</a></li>
                      <li><a href="<?=active_module_url();?>bphtb_posted_rpt">BPHTB Posted</a></li>
                      <!--li><a href="<?=active_module_url();?>bphtb_posted">Posted</a></li-->
                  </ul>
              </li>
              <? endif; // endif integrasi ppbbbphtb ?>
              
          <? endif; // endif ppat?>
              
              <li class="dropdown <?echo $current=='objek_pajak' ? 'active' : '';?>">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Objek Pajak <strong class="caret"></strong></a>
                  <ul class="dropdown-menu">
                      <li><a href="<?=active_module_url('objek_pajak');?>">Histori</a></li>
                  </ul>
              </li>
              
          <!-- NON PPAT -->
          <? if (!($this->session->userdata('isppat'))) : ?>
              <li class="dropdown <?echo $current=='referensi' ? 'active' : '';?>">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Referensi <strong class="caret"></strong></a>
                  <ul class="dropdown-menu">
                      <li><a href="<?=active_module_url('ppat');?>">Daftar PPAT</a></li>
                      <li><a href="<?=active_module_url('ppat_user');?>">User PPAT</a></li>
                      <li><a href="<?=active_module_url();?>user_validasi">Level User (Validasi)</a></li>
                      <li class="divider"></li>
                      <li><a href="<?=active_module_url('alasan');?>">Alasan Pengurangan</a></li>
                      <li><a href="<?=active_module_url('dasar');?>">Dasar Perhitungan</a></li>
                      <li><a href="<?=active_module_url('hukum');?>">Bentuk Hukum</a></li>
                      <li><a href="<?=active_module_url('perolehan');?>">Jenis Perolehan</a></li>
                      <li><a href="<?=active_module_url('status_hak');?>">Status Kepemilikan</a></li>
                      <li><a href="<?=active_module_url('jabatan');?>">Jabatan Pegawai</a></li>
                      <li><a href="<?=active_module_url('pejabat');?>">Pejabat</a></li>
                  </ul>
              </li>
          <? endif;?>
              
          </ul>
      </div>
			<? endif; ?>
        </div>
            
    </div>
</div>

