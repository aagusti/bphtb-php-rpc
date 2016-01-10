<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>

<script>
$(document).ready(function() {
	$('#btn_cancel').click(function() {
		window.location = '<?=active_module_url();?>ppat';
	});
	
	$('#tgl_sk').datepicker({
		dateFormat:'dd-mm-yy',
		changeMonth:true, 
		changeYear:true,
	});
});
</script>

<div class="content">
    <div class="container-fluid">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#"><strong>Daftar PPAT</strong></a>
			</li>
		</ul>
		
		<?php
		if(validation_errors()){
			echo '<blockquote><strong>Harap melengkapi data berikut :</strong>';
			echo validation_errors('<small>','</small>');
			echo '</blockquote>';
		} ?>
		
		<?php echo form_open($faction, array('id'=>'myform','class'=>'form-horizontal','enctype'=>'multipart/form-data'));?>
			<input type="hidden" name="id" value="<?=$dt['id']?>"/>
			<div class="control-group">
				<label class="control-label">Kode</label>
				<div class="controls">
					<input class="input-mini" type="text" name="kode" value="<?=$dt['kode']?>">
				</div>
			</div>
            <div class="control-group">
				<label class="control-label">Nama</label>
				<div class="controls">
					<input class="input-large" type="text" name="nama" value="<?=$dt['nama']?>">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Alamat</label>
				<div class="controls">
					<input class="input-xxlarge" type="text" name="alamat" value="<?=$dt['alamat']?>">&nbsp;
                </div>
            </div>
			<div class="control-group">
				<label class="control-label">Kecamatan</label>
				<div class="controls">
                    <input class="input-medium" type="text" name="kecamatan" value="<?=$dt['kecamatan']?>">&nbsp;Kelurahan
                    <input class="input-medium" type="text" name="kelurahan" value="<?=$dt['kelurahan']?>">
                </div>
            </div>
            <div class="control-group">
				<label class="control-label">Kota/Kab</label>
                <div class="controls">
                    <input class="input-medium" type="text" name="kota" value="<?=$dt['kota']?>">
				</div>
			</div>
            <div class="control-group">
				<label class="control-label">Wilayah Kerja</label>
				<div class="controls">
					<input class="input-medium" type="text" name="wilayah_kerja" value="<?=$dt['wilayah_kerja']?>">&nbsp;Kode Wilayah
					<input class="input-mini" type="text" name="kd_wilayah" value="<?=$dt['kd_wilayah']?>">
				</div>
			</div>
            <div class="control-group">
				<label class="control-label">Telp / Fax</label>
                <div class="controls">
                    <input class="input-small" type="text" name="no_telp" value="<?=$dt['no_telp']?>">&nbsp;/&nbsp;
                    <input class="input-small" type="text" name="no_fax" value="<?=$dt['no_fax']?>">
				</div>
			</div>
            <div class="control-group">
				<label class="control-label">No. SK</label>
                <div class="controls">
                    <input class="input-medium" type="text" name="no_sk" value="<?=$dt['no_sk']?>">&nbsp;Tanggal SK
					<input class="input-small" type="text" id="tgl_sk" name="tgl_sk" value="<?=$dt['tgl_sk']?>">
				</div>
			</div>
            <div class="control-group">
				<label class="control-label">NPWP</label>
				<div class="controls">
					<input class="input-large" type="text" name="npwp" value="<?=$dt['npwp']?>">
				</div>
			</div>

			<div class="control-group">
				<label class="control-label">Pejabat</label>
				<div class="controls">
					<select class="input-xlarge" name="pejabat_id" id="pejabat_id">
					<?php if( isset($pejabat) && $pejabat): ?>
						<option value="">Tidak ada</option>
						<?php foreach($pejabat as $data): ?>
							<option value="<?php echo $data->id;?>" <?php if($dt['pejabat_id']==$data->id) echo 'selected';?>><?php echo $data->nama;?></option>
						<?php endforeach;?>
					<?php else:?>
						<option value="">Tidak ada data!</option>
					<?php endif; ?>
					</select>
				</div>
			</div>
            
			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn btn-primary">Simpan</button>
					<button type="button" class="btn" id="btn_cancel">Batal</button>
				</div>
			</div>
		</form>
    </div>
</div><script type="text/javascript">document.forms['myform'].elements['kode'].focus();</script>
<? $this->load->view('_foot'); ?>