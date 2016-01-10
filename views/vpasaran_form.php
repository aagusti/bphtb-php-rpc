<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>

<script type="text/javascript">

var num_fields = new Array('kd_kecamatan', 'kd_kelurahan', 'no_urut', 'kd_blok');

function only_number_input(txt, userParse) {
    var ntext = txt.replace(/[^0-9]/g, '');
    if (ntext=='') { ntext = 0; }
    if (userParse==true) {
        return parseFloat(ntext);
    }
    return ntext;
}

function get_namaku(f, kode, h) {
    if (f=='' || h=='' || kode=='') { return false; }
    $.ajax({
        url: "<?=active_module_url($this->uri->segment(2))?>" + f + "/" + kode,
        success: function (json) {
            data = JSON.parse(json);
            $("#" + h).val(data['result']);
        },
        error: function (xhr, desc, er) {
            alert(er);
        }
    });
}

$(document).ready(function() {
    
    $("#main_tabs").tabs();
    
	$('#btn_cancel').click(function() {
		window.location = '<?=active_module_url();?>pasaran';
	});
    
    $("#btn_submit").click(function() {
        $("#myform").submit();
    });
    
    $("#myform").submit(function() {
        var vh = $("#harga").val();
        $("#harga").val(only_number_input(vh, true));
    });
    
    var cv;
    
    for (xn=0; xn<num_fields.length; xn++) {
        $("#" + num_fields[xn])
            .focus(function() {
                    var nt = $(this).val();
                    if (nt=='') { return; }
                    nt = only_number_input(nt, false);
                    $(this).val(nt);
                })
            .blur(function() {
                    var nt = only_number_input($(this).val(), false);
                    if (nt=='') { return false; }
                    $(this).val(nt);
                })
            .keypress(function(e) {
                    var k;
                    if (e.keyCode) {
                        k = e.keyCode;
                    } else {
                        k = e.which;
                    }
                    if ((k<48 || k>57) && k!=8 && k!=9) {
                        return false;
                    }
                });
            
        if (num_fields[xn]=='kd_kecamatan'){
            $("#" + num_fields[xn])
                .keyup(function () {
                    var kode = $("#kd_propinsi").val() + "." + $("#kd_dati2").val() + "." + $(this).val();
                    get_namaku("get_kecamatan_nama", kode, "kecamatan");
                    $("#kd_kelurahan").val("");
                    $("#kelurahan").val("");
                })
                .change(function () {
                    var kode = $("#kd_propinsi").val() + "." + $("#kd_dati2").val() + "." + $(this).val();
                    get_namaku("get_kecamatan_nama", kode, "kecamatan");
                    $("#kd_kelurahan").val("");
                    $("#kelurahan").val("");
                });
        }
        
        if (num_fields[xn]=='kd_kelurahan'){
            $("#" + num_fields[xn])
                .keyup(function () {
                    var kode = $("#kd_propinsi").val() + "." + $("#kd_dati2").val() + "." + $("#kd_kecamatan").val() + "." + $(this).val();
                    get_namaku("get_kelurahan_nama", kode, "kelurahan");
                })
                .change(function () {
                    var kode = $("#kd_propinsi").val() + "." + $("#kd_dati2").val() + "." + $("#kd_kecamatan").val() + "." + $(this).val();
                    get_namaku("get_kelurahan_nama", kode, "kelurahan");
                })
        }
    }
    
    $("#harga")
        .focus(function() {
                var nt = $(this).val();
                nt = only_number_input(nt, true);
                $(this).val(nt);
            })
        .blur(function() {
                var nt = only_number_input($(this).val(), true);
                $(this).val(numberFormatter(nt, 0, '.', ','));
            })
        .keyup(function() {
                var nt = only_number_input($(this).val(), true);
                $(this).val(nt);
            })
        .keypress(function(e) {
                var k;
                if (e.keyCode) {
                    k = e.keyCode;
                } else {
                    k = e.which;
                }
                if ((k<48 || k>57) && k!=8 && k!=9) {
                    return false;
                }
            });
            
    var tv = $("#harga").val();
    tv = only_number_input(tv, true);
    $("#harga").val(numberFormatter(tv, 0, '.', ','));
});

</script>

<div class="content">
	<div class="container-fluid">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#"><strong>Harga Pasar</strong></a>
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
		
		
            
		<?php echo form_open($faction, array('id'=>'myform','class'=>'form-horizontal','enctype'=>'multipart/form-data'));?>
			<input type="hidden" name="id" value="<?=$dt['id']?>"/>
			<div class="control-group">
				<label class="control-label">Propinsi</label>
				<div class="controls">
					<input style="width:40px;" class="input" type="text" id="kd_propinsi" name="kd_propinsi" maxlength="2" value="<?=$dt['kd_propinsi'];?>" readonly>
					<input class="input" type="text" id="propinsi" name="propinsi" value="<?=$propinsi; ?>" readonly>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Kota / Kabupaten</label>
				<div class="controls">
					<input style="width:40px;" class="input" type="text" id="kd_dati2" name="kd_dati2" maxlength="2" value="<?=$dt['kd_dati2'];?>" readonly>
					<input class="input" type="text" id="dati2" name="dati2" value="<?=$dati2; ?>" readonly>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Kecamatan</label>
				<div class="controls">
					<input style="width:40px;" class="input" type="text" id="kd_kecamatan" name="kd_kecamatan" maxlength="3" value="<?=$dt['kd_kecamatan'];?>">
					<input class="input" type="text" id="kecamatan" name="kecamatan" value="<?=$kecamatan; ?>" readonly>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Kelurahan</label>
				<div class="controls">
					<input style="width:40px;" class="input" type="text" id="kd_kelurahan" name="kd_kelurahan" maxlength="3" value="<?=$dt['kd_kelurahan'];?>">
					<input class="input" type="text" id="kelurahan" name="kelurahan" value="<?=$kelurahan; ?>" readonly>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Blok</label>
				<div class="controls">
					<input style="width:40px;" class="input" type="text" id="kd_blok" name="kd_blok" maxlength="3" value="<?= $dt['kd_blok']; ?>">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">No Urut</label>
				<div class="controls">
					<input style="width:40px;" class="input" maxlength="4" type="text" id="no_urut" name="no_urut" value="<?= $dt['no_urut'];?>">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Uraian</label>
				<div class="controls">
					<input class="input input-x75" style="width: 476px;" type="text" maxlength="100" name="uraian" value="<?= $dt['uraian']; ?>">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Harga</label>
				<div class="controls">
					<input style="width:140px;" class="input" type="text" id="harga" name="harga" value="<?= ($dt['harga'])?$dt['harga']:0;?>">
				</div>
			</div>
			<p>&nbsp;</p>
			<div class="control-group">
				<div class="controls">
					<button type="submit" id="btn_proses" class="btn btn-primary">Simpan</button>
					<button type="button" class="btn" id="btn_cancel">Batal</button>
				</div>
			</div>
		</form>
	</div>
</div>
<? $this->load->view('_foot'); ?>