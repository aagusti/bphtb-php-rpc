<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>
<script>

function show_rpt(){
	var rpt = $('#rpt').val();		
	var tglawal = $("#tglawal").val();
	var tglakhir = $("#tglakhir").val();
	var params = 'width='+screen.width+',height='+screen.height+',directories=0,titlebar=0,toolbar=0,location=0,status=0,menubar=0,scrollbars=no,resizable=no';
	window.open('<?=active_module_url();?>lap_penerimaan/show_rpt/?'+rpt+'/'+tglawal+'/'+tglakhir, 'Laporan', params);
}

$(document).ready(function() {
	$( "#tglawal, #tglakhir" ).datepicker({
		dateFormat:'dd-mm-yy', 
		changeMonth:true, 
		changeYear:true
	});

	$("#btnshow_rpt").click(function(){
		show_rpt();
	});
});
</script>

<div class="content">
    <div class="container-fluid">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab"><strong><?=$judul_lap?></strong></a></li>
        </ul>
        <?=msg_block();?>
        <div class="form-horizontal">
			<div class="control-group">
				<label class="control-label">Tanggal</label> 
				<div class="controls">
					<input type="hidden" id="rpt" value="<?=$rpt?>" />
					<input style="width:70px;" id="tglawal" name="tglawal" width="5" type="text" value="<?if(isset($tglawal)) echo $tglawal?>"/>
					s.d. <input style="width:70px;" id="tglakhir" name="tglakhir" type="text" value="<?if(isset($tglakhir)) echo $tglakhir?>"/>
				</div>
			</div>
			&nbsp;
			<div class="control-group">
				<div class="controls">
					<button id="btnshow_rpt" class="btn btn-primary" name="btnshow_rpt">Lihat Laporan</button>
				</div>
			</div>
        </div>
    </div>
</div>
<? $this->load->view('_foot'); ?>