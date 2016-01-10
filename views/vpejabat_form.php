<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>

<script>
$(document).ready(function() {
	$('#btn_cancel').click(function() {
		window.location = '<?=active_module_url();?>pejabat';
	});
});
</script>

<div class="content">
    <div class="container-fluid">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#"><strong>Pejabat</strong></a>
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
				<label class="control-label">NIP</label>
				<div class="controls">
					<input class="input-large" type="text" name="nip" value="<?=$dt['nip']?>">
				</div>
			</div>
            <div class="control-group">
				<label class="control-label">Nama</label>
				<div class="controls">
					<input class="input-xlarge" type="text" name="nama" value="<?=$dt['nama']?>">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Jabatan</label>
				<div class="controls">
					<select class="input-xlarge" name="kd_jabatan" id="kd_jabatan">
					<?php if( isset($jabatan) && $jabatan): ?>
						<?php foreach($jabatan as $data): ?>
							<option value="<?php echo $data->kd_jabatan;?>" <?php if($dt['kd_jabatan']==$data->kd_jabatan) echo 'selected';?>><?php echo $data->nm_jabatan;?></option>
						<?php endforeach;?>
					<?php else:?>
						<option value="">Tidak ada data!</option>
					<?php endif; ?>
					</select>
				</div>
			</div>
            <div class="control-group">
				<label class="control-label">Enable</label>
				<div class="controls">
					<input type="checkbox" name="enabled" <? echo $dt['enabled']?'checked':''; ?>>
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
</div><script type="text/javascript">document.forms['myform'].elements['nip'].focus();</script>
<? $this->load->view('_foot'); ?>