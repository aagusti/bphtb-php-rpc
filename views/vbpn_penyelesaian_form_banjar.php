<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>

<style>
.togle_no_select {
    -moz-appearance: none;
    text-indent: 0.01px;
    text-overflow: '';
}
</style>

<script type="text/javascript">
var xmode = "<?=$mode;?>";

$.fn.toggleOption = function( show ) {
    if(show) {
        if ($(this).hasClass('togle_no_select')) 
            $(this).removeClass('togle_no_select');
    } else {
        if (!$(this).hasClass('togle_no_select')) 
            $(this).addClass('togle_no_select');
    }
};

function hitung_njop() {
    var njop_bumi, njop_bng, njop;
    var luas_bumi, luas_bng;
    luas_bumi = parseInt($("#bumi_luas").autoNumeric('get'));
    luas_bng = parseInt($("#bng_luas").autoNumeric('get'));

    njop_bumi = luas_bumi>0 ? parseFloat($("#bumi_njop").autoNumeric('get')) : 0;
    njop_bng = luas_bng>0 ? parseFloat($("#bng_njop").autoNumeric('get')) : 0;

	njop = parseFloat(luas_bumi * njop_bumi + luas_bng * njop_bng);
    $("#njop").autoNumeric('set', njop);
    $("#njop2").autoNumeric('set', njop);

    var vx1 = parseFloat($("#npop").autoNumeric('get'));
    if (vx1 <= njop)
        $('#lblNPOP').show();
    else
        $('#lblNPOP').hide();

	// console.log('Njop = '+luas_bumi * njop_bumi + luas_bng * njop_bng);
	calculate();
}

function calculate() {
    var njop = parseFloat($("#njop").autoNumeric('get'));
    var vx1 = parseFloat($("#npop").autoNumeric('get'));
    var vx2 = parseFloat($("#npoptkp").autoNumeric('get'));
    var vx3 = parseFloat($("#tarif").autoNumeric('get'));

	var vx4 = 0;
	var perolehan = $('#perolehan_id').val();
	if (vx1 > njop || perolehan==8)
		vx4 = parseInt((vx1  - vx2) * vx3 / 100); // terhutang
	else
		vx4 = parseInt((njop - vx2) * vx3 / 100); // terhutang

    vx4 = (vx4 > 0) ? vx4 : 0;
    $("#terhutang").autoNumeric('set', vx4);

    var vx5 = parseInt($("#tarif_pengurang").autoNumeric('get'));
    var vx6 = parseInt(vx4 * vx5 / 100); // pengurang
    vx6 = (vx6 > 0) ? vx6 : 0;
    $("#pengurang").autoNumeric('set', vx6);

    var vx7 = parseFloat($("#bphtb_sudah_dibayarkan").autoNumeric('get'));
    var vx8 = parseFloat($("#denda").autoNumeric('get'));

    var vx9 = parseInt($("#bagian").autoNumeric('get'));
	if (vx9 < 1) {
		vx9 = 1;
		$("#bagian").autoNumeric('set', vx9);
	}

    var vx10 = parseInt($("#pembagi").autoNumeric('get'));
	if (vx10 < 1)  {
		vx10 = 1;
		$("#pembagi").autoNumeric('set', vx10);
	}

    var vx11 = 0;
    if (vx10 > 0) {
        vx11 = (vx4 - vx6 ) * (vx9 / vx10) - vx7 + vx8; // bphtb yg harus dibayar
    }
    // vx11 = (vx11 > 0) ? vx11 : 0;
    $("#bphtb_harus_dibayarkan").autoNumeric('set', vx11);

    if (vx1 <= njop)
        $('#lblNPOP').show();
    else
        $('#lblNPOP').hide();

	if (vx11 > 0)
     $('#lblKB').show();
    else
        $('#lblKB').hide();
}

$(document).ready(function () {
    $('#nop_thn').formatter({
        'pattern': '{{99}}.{{99}}.{{999}}.{{999}}.{{999}}-{{9999}}.{{9}}.{{9999}}',
    });
    $('#bpn_tgl_terima, #bpn_tgl_selesai').formatter({
        'pattern': '{{99}}-{{99}}-{{9999}}',
    });
	$('#bpn_tgl_selesai').datepicker({
		dateFormat:'dd-mm-yy',
		changeMonth:true, 
		changeYear:true,
	});
	$('#njop, #njop2, #npop, #bumi_luas, #bumi_njop, #bng_luas, #bng_njop').autoNumeric('init', {
		aSep: '.', aDec: ',', vMax: '999999999999.99',  mDec: '0'
	});
	$('#npoptkp, #terhutang, #bagian, #pembagi, #tarif_pengurang, #pengurang').autoNumeric('init', {
		aSep: '.', aDec: ',', vMax: '999999999999.99',  mDec: '0'
	});
    $('#tarif').autoNumeric('init', {
        aSep: '.', aDec: ',', vMax: '999999999999.99',  mDec: '2'
    });
	$('#bphtb_sudah_dibayarkan, #denda, #restitusi, #bphtb_harus_dibayarkan, #persen_pengurang_sendiri').autoNumeric('init', {
		aSep: '.', aDec: ',', vMax: '999999999999.99',  mDec: '0'
	});

	$('#btn_cari_wp').click(function() {
        var id = $('#wp_identitas').val();
        if(id)
            cari_wp(id);
        else
            alert('Harap mengisi Nomor Identitas WP!')
    });

    $('#wp_identitas').keypress(function(event){
        if (event.which == '13') {
            $('#btn_cari_wp').trigger('click');
        }
    });

	$('#btn_cari_nop').click(function() {
        var nop = $('#nop_thn').val();
        if(nop)
            cari_nop(nop);
        else
            alert('Harap mengisi NOP-Tahun Pajak SPPT!')
    });

    $('#nop_thn').keypress(function(event){
        if (event.which == '13') {
            $('#btn_cari_nop').trigger('click');
        }
    });

    $("#perolehan_id").change(function () {
        var poid = $(this).val();
        $.ajax({
            url: "<?=active_module_url($this->uri->segment(2));?>get_npoptkp/" + poid,
            success: function (json) {
                data = JSON.parse(json);
                $("#npoptkp").autoNumeric('set', data['npoptkp']);
                // $("#tarif_pengurang").autoNumeric('set', data['pengurang']);

                calculate();
            },
            error: function (xhr, desc, er) {
                alert(er);
            }
        });
    });

    $('#npop, #npoptkp, #tarif, #terhutang, #tarif_pengurang, #pengurang, #bphtb_sudah_dibayarkan, #denda, #bagian, #pembagi, #bphtb_harus_dibayarkan').keyup(function () {
        calculate();
    });

    $('#bumi_luas, #bumi_njop, #bng_luas, #bng_njop, #njop').keyup(function () {
        hitung_njop();
    });

    $('#njop').change(function () {
        calculate();
    });

    $("#myform").submit(function () {
        $('#myform :input').removeAttr('disabled'); 
    
        var xNJOP = $("#njop").autoNumeric('get');
        var xNPOP = $("#npop").autoNumeric('get');
        if (parseInt(xNPOP) <= parseInt(xNJOP))
            if(confirm('NPOP kurang dari/sama dengan NJOP! Abaikan ?') == false) 
                return false;
        return true;
    });

    $('#btn_cancel').click(function () {
        window.location = '<?=active_module_url($this->uri->segment(2));?>';
    });

    /* init */
    var idx = parseInt(<?=$dt['id']?>);
    if(isNaN(idx)) $("#perolehan_id").trigger('change');
    
    calculate();
	hitung_njop();

    // $('#myform :input').attr('readonly','readonly');
    $('#myform :input').attr('disabled','disabled'); 
    
    $('#bpn_tgl_selesai, #btn_proses, #btn_cancel').removeAttr('disabled');    
    $('#dasar_id, #perolehan_id').toggleOption(false);
});

$(document).keypress(function(event){
	if (event.which == '13') {
		event.preventDefault();
	}
});
</script>

<div class="content">
	<div class="container-fluid">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#"><strong>Register Penyelesaian Berkas</strong></a>
			</li>
		</ul>

		<?php
		if(validation_errors()){
			echo '<blockquote><strong>Harap melengkapi data berikut :</strong>';
			$valer = validation_errors('<small>','</small>');
			$valer = str_replace(array('Field ', ' harus diisi', '<small>', '</small>'), array('', '', '', ','), $valer);
			$valer = (strlen($valer)>0)?substr($valer, 0, strlen($valer)-2) . ' harus diisi':$valer;
			$valer = '<small style="color: red; background-color: #ffe;">Field ' . $valer . '</small>';
			echo $valer;
			echo '</blockquote>';
		} ?>
        <?=msg_block();?>

		<?
			if (isset($error_date_format) && $error_date_format) {
				echo $error_date_format;
			}
		?>

		<?php echo form_open($faction, array('id'=>'myform','class'=>'form-horizontal',
			'enctype'=>'multipart/form-data', 'style'=>'padding-bottom: 0px;'));?>
			<input type="hidden" id="id" name="id" value="<?=$dt['id']?>"/>
			<input type="hidden" id="sspd_id" name="sspd_id" value="<?=$dt['sspd_id']?>"/>
			<input type="hidden" id="header_id" name="header_id" value="<?=$dt['header_id']?>"/>
			<input type="hidden" id="status_daftar" name="status_daftar" value="<?=$dt['status_daftar']?>"/>
            
			<div class="control-group">
				<label class="control-label">Tgl. Terima</label>
				<div class="controls">
					<input style="width:68px;" type="text" class="input" name="bpn_tgl_terima" id="bpn_tgl_terima" value="<?=date('d-m-Y');?>" placeholder="dd-mm-yyyy">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tgl. Selesai &nbsp;&nbsp;
					<input style="width:67px;" type="text" class="input" name="bpn_tgl_selesai" id="bpn_tgl_selesai" value="<?=date('d-m-Y'); ?>" placeholder="dd-mm-yyyy">
				</div>
			</div>

			<div class="control-group">
				<label class="control-label">No.SSPD</label>
				<div class="controls">
					<input class="input-small" type="text" id="sspdno" name="sspdno" value="<?=$dt['sspdno']?>" readonly>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tgl.SSPD &nbsp;&nbsp;
					<input style="width:68px;" type="text" class="input" name="tgl_transaksi" id="tgl_transaksi" value="<?=($dt['tgl_transaksi'])? $dt['tgl_transaksi'] : date('d-m-Y'); ?>" placeholder="dd-mm-yyyy" readonly>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">PPAT</label>
				<div class="controls">
					<?php if( isset($ppat) && $ppat): ?>
						<?php foreach($ppat as $data): ?>
							<?php if($dt['ppat_id']==$data->id):?>
                                <input class="input-xlarge" type="text" id="ppat_nama" name="ppat_nama" value="<?=$data->nama?>" readonly>
                            <?php endif; ?>
						<?php endforeach;?>
                    <?php endif; ?>
				</div>
				<div class="controls hide">
					<? if(isset($isppat) && $isppat) : ?>
					<input type="hidden" id="ppat_id" name="ppat_id" value="<?=$ppatid?>"/>
					<input class="input-x input-x75" type="text" name="ppat_nm" value="<?=$ppatnm?>" readonly>
					<? else : ?>
					<select class="input input-xlarge" name="ppat_id" id="ppat_id">
					<?php if( isset($ppat) && $ppat): ?>
						<?php foreach($ppat as $data): ?>
							<option value="<?php echo $data->id;?>" <?php if($dt['ppat_id']==$data->id) echo 'selected';?>><?php echo $data->kode . " - " . $data->nama;?></option>
						<?php endforeach;?>
                    <?php else:?>
                        <option value="">Tidak ada data!</option>
                    <?php endif; ?>
					</select>
					<? endif ?>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Keterangan</label>
				<div class="controls">
					<input type="text" class="input-xxlarge" name="keterangan" id="keterangan" value="<?=$dt['keterangan']?>" >
				</div>
			</div>
            
			<div class="tabbable">
				<ul id="myTab" class="nav nav-tabs">
					<li class="active"><a href="#wp" data-toggle="tab"><strong>Wajib Pajak</strong></a></li>
					<li class=""><a href="#op" data-toggle="tab"><strong>Objek Pajak</strong></a></li>
					<li class=""><a href="#perhitungan" data-toggle="tab"><strong>Perhitungan</strong></a></li>
					<li class=""><a href="#dok" data-toggle="tab"><strong>Dokumen</strong></a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane fade in active" id="wp">
						<div class="row">
							<div class="span4" style="width:340px;">
								<div class="control-group">
									<label style="width:340px;" class="control-label"><strong>Pihak yang menerima Hak:</strong></label>
								</div>
								<div class="control-group">
									<label class="control-label">Identitas</label>
									<div class="controls">
										<input class="input" style="width:175px;" type="text" id="wp_identitas" name="wp_identitas" value="<?=$dt['wp_identitas']?>" autocomplete="off" >
										<button id="btn_cari_wp" type="button" style="padding:2px 6px !important;" class="btn btn-primary"><i class="icon-search icon-white"></i></button>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Nama</label>
									<div class="controls">
										<input class="input input60" type="text" id="wp_nama" name="wp_nama" value="<?=$dt['wp_nama']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">NPWP</label>
									<div class="controls">
										<input class="input" type="text" id="wp_npwp" name="wp_npwp" value="<?=$dt['wp_npwp']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Alamat</label>
									<div class="controls">
										<input class="input input60" type="text" id="wp_alamat" name="wp_alamat" value="<?=$dt['wp_alamat']?>">
										<label class="label-inline"></label>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Blok / Kav / No</label>
									<div class="controls">
										<input class="input" type="text" id="wp_blok_kav" name="wp_blok_kav" value="<?=$dt['wp_blok_kav']?>">
								  </div>
								</div>
								<div class="control-group">
									<label class="control-label">RT / RW</label>
									<div class="controls">
										<input class="input" style="width:20px;" type="text" id="wp_rt" name="wp_rt" value="<?=$dt['wp_rt']?>"> /
										<input class="input" style="width:20px;" type="text" id="wp_rw" name="wp_rw" value="<?=$dt['wp_rw']?>">
								  </div>
								</div>
                                
                                
								<div class="control-group">
									<label class="control-label">Kelurahan</label>
									<div class="controls">
										<input class="input" type="text" id="wp_kelurahan" name="wp_kelurahan" value="<?=$dt['wp_kelurahan']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Kecamatan</label>
									<div class="controls">
										<input class="input" type="text" id="wp_kecamatan" name="wp_kecamatan" value="<?=$dt['wp_kecamatan']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Kabupaten / Kota</label>
									<div class="controls">
										<input class="input" type="text" id="wp_kota" name="wp_kota" value="<?=$dt['wp_kota']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Propinsi</label>
									<div class="controls">
										<input class="input" type="text" id="wp_provinsi" name="wp_provinsi" value="<?=$dt['wp_provinsi']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Kode Pos</label>
									<div class="controls">
										<input class="input-small" type="text" id="wp_kdpos" name="wp_kdpos" value="<?=$dt['wp_kdpos']?>">
									</div>
								</div>
							</div>

							<div class="span4" style="width:340px;">
								<div class="control-group">
									<label style="width:340px;" class="control-label"><strong>Pihak yang mengalihkan Hak:</strong></label>
								</div>
								<div class="control-group">
									<label class="control-label">Identitas</label>
									<div class="controls">
										<input class="input" style="width:175px;" type="text" id="wp_identitas_asal" name="wp_identitas_asal" value="<?=$dt['wp_identitas_asal']?>" autocomplete="off" >
										<button id="btn_cari_wp_asal" type="button" style="padding:2px 6px !important;" class="btn btn-primary"><i class="icon-search icon-white"></i></button>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Nama WP Asal</label>
									<div class="controls">
										<input class="input" type="text" id="wp_nama_asal" name="wp_nama_asal" value="<?=$dt['wp_nama_asal']?>" maxlength="50" >
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">NPWP</label>
									<div class="controls">
										<input class="input" type="text" id="wp_npwp_asal" name="wp_npwp_asal" value="<?=$dt['wp_npwp_asal']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Alamat</label>
									<div class="controls">
										<input class="input input60" type="text" id="wp_alamat_asal" name="wp_alamat_asal" value="<?=$dt['wp_alamat_asal']?>">
										<label class="label-inline"></label>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Blok / Kav / No</label>
									<div class="controls">
										<input class="input" type="text" id="wp_blok_kav_asal" name="wp_blok_kav_asal" value="<?=$dt['wp_blok_kav_asal']?>">
								  </div>
								</div>
								<div class="control-group">
									<label class="control-label">RT / RW</label>
									<div class="controls">
										<input class="input" style="width:20px;" type="text" id="wp_rt_asal" name="wp_rt_asal" value="<?=$dt['wp_rt_asal']?>"> /
										<input class="input" style="width:20px;" type="text" id="wp_rw_asal" name="wp_rw_asal" value="<?=$dt['wp_rw_asal']?>">
								  </div>
								</div>
                                
								<div class="control-group">
									<label class="control-label">Kelurahan</label>
									<div class="controls">
										<input class="input" type="text" id="wp_kelurahan_asal" name="wp_kelurahan_asal" value="<?=$dt['wp_kelurahan_asal']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Kecamatan</label>
									<div class="controls">
										<input class="input" type="text" id="wp_kecamatan_asal" name="wp_kecamatan_asal" value="<?=$dt['wp_kecamatan_asal']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Kabupaten / Kota</label>
									<div class="controls">
										<input class="input" type="text" id="wp_kota_asal" name="wp_kota_asal" value="<?=$dt['wp_kota_asal']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Propinsi</label>
									<div class="controls">
										<input class="input" type="text" id="wp_provinsi_asal" name="wp_provinsi_asal" value="<?=$dt['wp_provinsi_asal']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Kode Pos</label>
									<div class="controls">
										<input class="input-small" type="text" id="wp_kdpos_asal" name="wp_kdpos_asal" value="<?=$dt['wp_kdpos_asal']?>">
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="tab-pane fade in" id="op">
						<div class="row">
							<div class="span12">
							</div>
						</div>
						<div class="row">
							<div class="span4" style="width:340px;">
								<div class="control-group">
									<label class="control-label">NOP-Thn.Pjk.SPPT</label>
									<div class="controls">
                                        <input class="input" type="text" style="width:175px;" id="nop_thn" name="nop_thn" value="<?=$dt['nop_thn']?>">
										<button id="btn_cari_nop" type="button" style="padding:2px 6px !important;" class="btn btn-primary"><i class="icon-search icon-white"></i></button>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Alamat</label>
									<div class="controls">
										<input class="input input75" type="text" id="op_alamat" name="op_alamat" value="<?=$dt['op_alamat']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Blok / Kav / No</label>
									<div class="controls">
										<input class="input" type="text" id="op_blok_kav" name="op_blok_kav" value="<?=$dt['op_blok_kav']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">RT / RW</label>
									<div class="controls">
										<input class="input" style="width:20px;" type="text" id="op_rt" name="op_rt" value="<?=$dt['op_rt']?>"> /
										<input class="input" style="width:20px;" type="text" id="op_rw" name="op_rw" value="<?=$dt['op_rw']?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Status Kepemilikan</label>
									<div class="controls">
                                        <select class="input input" name="status_hak_id" id="status_hak_id">
                                        <?php if( isset($status_hak) && $status_hak): ?>
                                            <?php foreach($status_hak as $data): ?>
                                                <option value="<?php echo $data->id;?>" <?php if($dt['status_hak_id']==$data->id) echo 'selected';?>><?php echo $data->uraian;?></option>
                                            <?php endforeach;?>
                                        <?php else:?>
                                            <option value="">Tidak ada data!</option>
                                        <?php endif; ?>
                                        </select>
									</div>
								</div>
							</div>
							<div class="span6">
								<div class="control-group">
									<label class="control-label">&nbsp;</label>
									<div class="controls">&nbsp;</div>
								</div>
								<div class="control-group">
									<label class="control-label">Luas / NJOP Bumi</label>
									<div class="controls">
										<input class="input-small" type="text" id="bumi_luas" name="bumi_luas" value="<?= ($dt['bumi_luas'])?$dt['bumi_luas']:0;?>">&nbsp;<label class="label">M<sup>2</sup></label> /
										<input class="input-small" type="text" id="bumi_njop" name="bumi_njop" value="<?= ($dt['bumi_njop'])?$dt['bumi_njop']:0;?>" readonly >
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Luas / NJOP Bngnn</label>
									<div class="controls">
										<input class="input-small" type="text" id="bng_luas" name="bng_luas" value="<?= ($dt['bng_luas'])?$dt['bng_luas']:0;?>">&nbsp;<label class="label">M<sup>2</sup></label> /
										<input class="input-small" type="text" id="bng_njop" name="bng_njop" value="<?= ($dt['bng_njop'])?$dt['bng_njop']:0;?>" readonly >
									</div>
								</div>

								<div class="control-group">
									<label class="control-label">NJOP</label>
									<div class="controls">
										<input class="input-small" type="text" id="njop" name="njop" value="<?= ($dt['njop'])?$dt['njop']:0;?>" readonly>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">No.Sertifikat / Kohir</label>
									<div class="controls">
										<input class="input" type="text" id="no_sertifikat" name="no_sertifikat" value="<?=$dt['no_sertifikat']?>">
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="tab-pane fade in" id="perhitungan">
						<div class="row">
							<div class="span6" style="width: 450px;">
								<div class="control-group">
									<label class="control-label" style="width: 180px">Dasar Perhitungan</label>
									<div class="controls">
										<select class="input" name="dasar_id" id="dasar_id">
										<?php if( isset($dasar) && $dasar): ?>
											<?php foreach($dasar as $data): ?>
												<option value="<?php echo $data->id;?>" <?php if($dt['dasar_id']==$data->id) echo 'selected';?>><?php echo ((string)$data->id) . " - " . $data->nama;?></option>
											<?php endforeach;?>
										<?php else:?>
											<option value="">Tidak ada data!</option>
										<?php endif; ?>
										</select>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" style="width: 180px">Jenis Perolehan</label>
									<div class="controls">
										<select class="input input-xlarge" name="perolehan_id" id="perolehan_id" >
											<?php if( isset($perolehan) && $perolehan): ?>
												<?php foreach($perolehan as $data): ?>
													<option value="<?php echo $data->id;?>" <?php if($dt['perolehan_id']==$data->id) echo 'selected';?>><?php echo ((string)$data->id) . " - " . $data->nama;?></option>
												<?php endforeach;?>
											<?php else:?>
												<option value="">Tidak ada data!</option>
											<?php endif; ?>
										</select>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" style="width: 180px">NJOP</label>
									<div class="controls">
										<input class="input-small" type="text" id="njop2" name="njop2" value="<?= ($dt['njop'])?$dt['njop']:0;?>" readonly>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" style="width: 180px">Harga Transaksi (HT) / NPOP</label>
									<div class="controls">
										<input class="input-small" type="text" id="npop" name="npop" value="<?= ($dt['npop'])?$dt['npop']:0;?>">
										<span id="lblNPOP" class="label label-important">NPOP <= NJOP</span>
									</div>
								</div>
								
                                <div class="control-group">
									<label class="control-label" style="width: 180px">NPOPTKP</label>
									<div class="controls">
										<input class="input-small" type="text" id="npoptkp" name="npoptkp" value="<?= ($dt['npoptkp'])?$dt['npoptkp']:0;?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" style="width: 180px">Tarif BPHTB (%) </label>
									<div class="controls">
										<input class="input-small" style="width:40px;" type="text" id="tarif" name="tarif" value="<?= ($dt['tarif'])?$dt['tarif']:0;?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" style="width: 180px">BPHTB Terutang</label>
									<div class="controls">
										<input class="input-small" type="text" id="terhutang" name="terhutang" value="<?= ($dt['terhutang'])?$dt['terhutang']:0;?>" readonly >
									</div>
								</div>
                                
								<div class="control-group">
									<label class="control-label" style="width: 180px">BPHTB yang Telah Dibayar</label>
									<div class="controls">
										<input class="input-small" type="text" id="bphtb_sudah_dibayarkan" name="bphtb_sudah_dibayarkan" value="<?= ($dt['bphtb_sudah_dibayarkan'])?$dt['bphtb_sudah_dibayarkan']:0;?>" readonly>
									</div>
								</div>
                                
                                
								<div class="control-group">
									<label class="control-label" style="width: 180px">Pengurang BPHTB</label>
									<div class="controls">
										<input class="input-small" type="text" id="pengurang" name="pengurang" value="<?= ($dt['pengurang'])?$dt['pengurang']:0;?>" readonly>
									</div>
								</div>
                                
								<div class="control-group">
									<label class="control-label" style="width: 180px">Denda Administrasi</label>
									<div class="controls">
										<input class="input-small" type="text" id="denda" name="denda" value="<?= ($dt['denda'])?$dt['denda']:0;?>">
									</div>
								</div>
                                                    
								<div class="control-group success">
									<label class="control-label" style="width: 180px"><strong>BPHTB yang Harus Dibayar</strong></label>
									<div class="controls">
										<input class="input-small" type="text" id="bphtb_harus_dibayarkan" name="bphtb_harus_dibayarkan" value="<?= ($dt['bphtb_harus_dibayarkan'])?$dt['bphtb_harus_dibayarkan']:0;?>" readonly>
										<span id="lblKB" class="label label-important">KURANG BAYAR</span>
									</div>
								</div>        
                                
								<div class="control-group hide">
									<label class="control-label" style="width: 180px">APHB</label>
									<div class="controls">
										<input class="input-small" style="width:40px;" type="text" id="bagian" name="bagian" value="<?= ($dt['bagian'])?$dt['bagian']:1;?>">
										<span class="label">dari</span>
										<input class="input-small" style="width:40px;" type="text" id="pembagi" name="pembagi" value="<?= ($dt['pembagi']==0 || !$dt['pembagi'])?1:$dt['pembagi'];?>">
									</div>
								</div>
							</div>
							<div class="span5">
								<div class="control-group">
									<label class="control-label"><strong>Dasar Ketetapan:</strong></label>
									<div class="controls">&nbsp;</div>
								</div>
								<div class="control-group">
									<label class="control-label" style="width: 180px">Nomor Ketetapan</label>
									<div class="controls">
										<input class="input-medium" type="text" id="ketetapan_no" name="ketetapan_no" value="<?=$dt['ketetapan_no'];?>" >
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" style="width: 180px">Tanggal Ketetapan</label>
									<div class="controls">
                                        <input style="width:80px;" type="text" class="input" name="ketetapan_tgl" id="ketetapan_tgl" value="<?=$dt['ketetapan_tgl']?>" placeholder="dd-mm-yyyy" >
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" style="width: 180px">Atas No.SSPD BPHTB</label>
									<div class="controls">
										<input class="input-small" type="text" id="ketetapan_atas_sspd_no" name="ketetapan_atas_sspd_no" value="<?=$dt['ketetapan_atas_sspd_no'];?>" >
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" style="width: 180px">Tanggal Jatuh Tempo</label>
									<div class="controls">
                                        <input style="width:80px;" type="text" class="input" name="ketetapan_jatuh_tempo_tgl" id="ketetapan_jatuh_tempo_tgl" value="<?=$dt['ketetapan_jatuh_tempo_tgl']?>" placeholder="dd-mm-yyyy" >
									</div>
								</div>
							</div>
							<div class="span5">
								<div class="control-group">
                                    <label class="control-label" style="width: 180px">&nbsp;</label>
                                </div>
								<div class="control-group">
									<label class="control-label"><strong>Dasar Pengurangan:</strong></label>
									<div class="controls">&nbsp;</div>
								</div>
								<div class="control-group">
									<label class="control-label" style="width: 180px">Nomor SK</label>
									<div class="controls">
										<input class="input-medium" type="text" id="pengurangan_sk" name="pengurangan_sk" value="<?=$dt['pengurangan_sk'];?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" style="width: 180px">Tanggal SK</label>
									<div class="controls">
                                        <input style="width:80px;" type="text" class="input" name="pengurangan_sk_tgl" id="pengurangan_sk_tgl" value="<?=$dt['pengurangan_sk_tgl']?>" placeholder="dd-mm-yyyy">
									</div>
								</div>          
								<div class="control-group">
									<label class="control-label" style="width: 180px">Besar Pengurang (%)</label>
									<div class="controls">
										<input class="input-small" style="width:40px;" type="text" id="tarif_pengurang" name="tarif_pengurang" value="<?= ($dt['tarif_pengurang'])?$dt['tarif_pengurang']:0;?>">
									</div>
								</div>                        
                                <div class="control-group">
                                    <label class="control-label" style="width: 180px">Tanggal Jatuh Tempo</label>
                                    <div class="controls">
                                        <input style="width:80px;" type="text" class="input" name="pengurangan_jatuh_tempo_tgl" id="pengurangan_jatuh_tempo_tgl" value="<?=$dt['pengurangan_jatuh_tempo_tgl']?>" placeholder="dd-mm-yyyy">
                                    </div>
                                </div>
							</div>
						</div>
					</div>

					<!-- dokumen -->
					<div class="tab-pane fade in" id="dok">
						<div class="row">
							<div class="span5" style="width: 420px;">

                                <input type="hidden" name="file1" value="<?=$dt['file1']?>"/>
                                <input type="hidden" name="file2" value="<?=$dt['file2']?>"/>
                                <input type="hidden" name="file3" value="<?=$dt['file3']?>"/>
                                <input type="hidden" name="file4" value="<?=$dt['file4']?>"/>
                                <input type="hidden" name="file5" value="<?=$dt['file5']?>"/>
                                <input type="hidden" name="file6" value="<?=$dt['file6']?>"/>
                                <input type="hidden" name="file7" value="<?=$dt['file7']?>"/>
                                <input type="hidden" name="file8" value="<?=$dt['file8']?>"/>
                                <input type="hidden" name="file9" value="<?=$dt['file9']?>"/>
                                <input type="hidden" name="file10" value="<?=$dt['file10']?>"/>

                                <div class="control-group">
                                    <label class="control-label" style="width: 30px;">1.</label>
                                    <div class="controls" style="margin-left: 40px;">
                                        <input class="input" type="file" id="attach1" name="attach1" size="40" style="height:22px !important;">
                                        <? if ($dt['file1']!='') : ?>
                                            <br/><span class="label label-info"><a style="color: inherit;;" href="<?=active_module_url('download/download_file') . $dt['file1'];?>" target="_blank"><? echo $dt['file1'];?></a></span>
                                        <? else : ?>
                                            <br/><span class="label">No file</span>
                                        <? endif ?>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" style="width: 30px;">2.</label>
                                    <div class="controls" style="margin-left: 40px;">
                                        <input class="input" type="file" id="attach2" name="attach2" size="40" style="height:22px !important;">
                                        <? if ($dt['file2']!='') : ?>
                                            <br/><span class="label label-info"><a style="color: inherit;;" href="<?=active_module_url('download/download_file') . $dt['file2'];?>" target="_blank"><? echo $dt['file2'];?></a></span>
                                        <? else : ?>
                                            <br/><span class="label">No file</span>
                                        <? endif ?>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" style="width: 30px;">3.</label>
                                    <div class="controls" style="margin-left: 40px;">
                                        <input class="input" type="file" id="attach3" name="attach3" size="40" style="height:22px !important;">
                                        <? if ($dt['file3']!='') : ?>
                                            <br/><span class="label label-info"><a style="color: inherit;;" href="<?=active_module_url('download/download_file') . $dt['file3'];?>" target="_blank"><? echo $dt['file3'];?></a></span>
                                        <? else : ?>
                                            <br/><span class="label">No file</span>
                                        <? endif ?>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" style="width: 30px;">4.</label>
                                    <div class="controls" style="margin-left: 40px;">
                                        <input class="input" type="file" id="attach4" name="attach4" size="40" style="height:22px !important;">
                                        <? if ($dt['file4']!='') : ?>
                                            <br/><span class="label label-info"><a style="color: inherit;;" href="<?=active_module_url('download/download_file') . $dt['file4'];?>" target="_blank"><? echo $dt['file4'];?></a></span>
                                        <? else : ?>
                                            <br/><span class="label">No file</span>
                                        <? endif ?>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" style="width: 30px;">5.</label>
                                    <div class="controls" style="margin-left: 40px;">
                                        <input class="input" type="file" id="attach5" name="attach5" size="40" style="height:22px !important;">
                                        <? if ($dt['file5']!='') : ?>
                                            <br/><span class="label label-info"><a style="color: inherit;;" href="<?=active_module_url('download/download_file') . $dt['file5'];?>" target="_blank"><? echo $dt['file5'];?></a></span>
                                        <? else : ?>
                                            <br/><span class="label">No file</span>
                                        <? endif ?>
                                    </div>
                                </div>
							</div>

							<div class="span5" style="width: 420px;">
                                <div class="control-group">
                                    <label class="control-label" style="width: 30px;">6.</label>
                                    <div class="controls" style="margin-left: 40px;">
                                        <input class="input" type="file" id="attach6" name="attach6" size="40" style="height:22px !important;">
                                        <? if ($dt['file6']!='') : ?>
                                            <br/><span class="label label-info"><a style="color: inherit;;" href="<?=active_module_url('download/download_file') . $dt['file6'];?>" target="_blank"><? echo $dt['file6'];?></a></span>
                                        <? else : ?>
                                            <br/><span class="label">No file</span>
                                        <? endif ?>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" style="width: 30px;">7.</label>
                                    <div class="controls" style="margin-left: 40px;">
                                        <input class="input" type="file" id="attach7" name="attach7" size="40" style="height:22px !important;">
                                        <? if ($dt['file7']!='') : ?>
                                            <br/><span class="label label-info"><a style="color: inherit;;" href="<?=active_module_url('download/download_file') . $dt['file7'];?>" target="_blank"><? echo $dt['file7'];?></a></span>
                                        <? else : ?>
                                            <br/><span class="label">No file</span>
                                        <? endif ?>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" style="width: 30px;">8.</label>
                                    <div class="controls" style="margin-left: 40px;">
                                        <input class="input" type="file" id="attach8" name="attach8" size="40" style="height:22px !important;">
                                        <? if ($dt['file8']!='') : ?>
                                            <br/><span class="label label-info"><a style="color: inherit;;" href="<?=active_module_url('download/download_file') . $dt['file8'];?>" target="_blank"><? echo $dt['file8'];?></a></span>
                                        <? else : ?>
                                            <br/><span class="label">No file</span>
                                        <? endif ?>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" style="width: 30px;">9.</label>
                                    <div class="controls" style="margin-left: 40px;">
                                        <input class="input" type="file" id="attach9" name="attach9" size="40" style="height:22px !important;">
                                        <? if ($dt['file9']!='') : ?>
                                            <br/><span class="label label-info"><a style="color: inherit;;" href="<?=active_module_url('download/download_file') . $dt['file9'];?>" target="_blank"><? echo $dt['file9'];?></a></span>
                                        <? else : ?>
                                            <br/><span class="label">No file</span>
                                        <? endif ?>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" style="width: 30px;">10.</label>
                                    <div class="controls" style="margin-left: 40px;">
                                        <input class="input" type="file" id="attach10" name="attach10" size="40" style="height:22px !important;">
                                        <? if ($dt['file10']!='') : ?>
                                            <br/><span class="label label-info"><a style="color: inherit;;" href="<?=active_module_url('download/download_file') . $dt['file10'];?>" target="_blank"><? echo $dt['file10'];?></a></span>
                                        <? else : ?>
                                            <br/><span class="label">No file</span>
                                        <? endif ?>
                                    </div>
                                </div>
							</div>
                            
						</div>
					</div>

				</div>
			</div>

			<p>&nbsp;</p>
			<button type="submit" id="btn_proses" class="btn btn-primary">Proses</button>
			<button type="button" class="btn" id="btn_cancel">Batal / Kembali</button>
		</form>
	</div>
</div>
<? $this->load->view('_foot'); ?>