<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>

<script>
$(document).ready(function() {
	$('#btn_cancel').click(function() {
		window.location = '<?=active_module_url($this->uri->segment(2));?>';
	});
    
	$('#tgl_ajb, #tgl_pph').datepicker({
		dateFormat:'dd-mm-yy',
	});
	$('#jml_pph').autoNumeric('init', {
		aSep: '.', aDec: ',', vMax: '999999999999999.99',  mDec: '0'
	});
});
</script>

<div class="content">
    <div class="container-fluid">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#"><strong>Entri AJB</strong></a>
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
				<label class="control-label">Nama WP</label>
				<div class="controls">
					<input class="input" type="text" name="wp_nama" value="<?=$dt['wp_nama']?>" readonly>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">NPWP WP</label>
				<div class="controls">
					<input class="input" type="text" name="wp_npwp" value="<?=$dt['wp_npwp']?>" readonly>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Nama WP Asal</label>
				<div class="controls">
					<input class="input" type="text" name="wp_nama_asal" value="<?=$dt['wp_nama_asal']?>" >
				</div>
			</div>
            
            <!-- -->
			<div class="control-group">
				<label class="control-label">No. AJB</label>
				<div class="controls">
					<input class="input" type="text" name="no_ajb" value="<?=$dt['no_ajb']?>">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Tanggal AJB</label>
				<div class="controls">
					<input class="input-small" type="text" id="tgl_ajb" name="tgl_ajb" value="<?=$dt['tgl_ajb']?>" placeholder="dd-mm-yyyy">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Jumlah PPH</label>
				<div class="controls">
					<input class="input-small" type="text" id="jml_pph" name="jml_pph" value="<?=$dt['jml_pph']?>">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Tanggal PPH</label>
				<div class="controls">
					<input class="input-small" type="text" id="tgl_pph" name="tgl_pph" value="<?=$dt['tgl_pph']?>" placeholder="dd-mm-yyyy">
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
</div><script type="text/javascript">document.forms['myform'].elements['id'].focus();</script>
<? $this->load->view('_foot'); ?>