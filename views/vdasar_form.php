<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>

<script>
$(document).ready(function() {
	$('#btn_cancel').click(function() {
		window.location = '<?=active_module_url();?>dasar';
	});
});
</script>

<div class="content">
    <div class="container-fluid">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#"><strong>Dasar Perhitungan</strong></a>
			</li>
		</ul>
		
		<?php
		if(validation_errors()){
			echo '<blockquote><strong>Harap melengkapi data berikut :</strong>';
			echo validation_errors('<small>','</small>');
			echo '</blockquote>';
		} ?>
		
		<?php echo form_open($faction, array('id'=>'myform','class'=>'form-horizontal','enctype'=>'multipart/form-data'));?>
			<input type="hidden" name="cur_id" value="<?=$dt['cur_id']?>"/>
			<div class="control-group">
				<label class="control-label">Kode</label>
				<div class="controls">
					<input class="input-mini" type="text" name="id" value="<?=$dt['id']?>">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Uraian</label>
				<div class="controls">
					<input class="input-xxlarge" type="text" name="nama" value="<?=$dt['nama']?>">
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
</div>
<script type="text/javascript">document.forms['myform'].elements['id'].focus();</script>
<? $this->load->view('_foot'); ?>