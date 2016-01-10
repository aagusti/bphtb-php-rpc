<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>

<style>
.modal {
    top: 10%;
	width: 800px;
	margin-left: -400px;
	--max-height: 250px;
}
.modal-body {
	--padding: 5px;
}
</style>

<script>
var oTableDtl;
var oTableDlg;
var mID;
var dtDlg;

function reload_sspdGrid() {
	oTableDlg.fnReloadAjax("<?=active_module_url($this->uri->segment(2));?>grid_sspd/"+$('#ppat_id').val());
    oTableDtl.fnClearTable();
}

$(document).ready(function() {
	oTableDtl = $('#dTable').dataTable({
		"iDisplayLength": 10,
		"bJQueryUI": true,
		"bAutoWidth": false,
		"sPaginationType": "full_numbers",
		"sDom": '<"toolbar">frtip',
		"aaSorting": [[ 2, "desc" ]],
		"aoColumnDefs": [
			{ "aTargets": [0], "bSearchable": false, "bVisible": false, "bSortable": false, "sWidth": "", "sClass": "" },
			{ "aTargets": [1], "bSearchable": false, "bVisible": true, "bSortable": false, "sWidth": "40px", "sClass": "center" },
			{ "aTargets": [2], "bSearchable": true,  "bVisible": true,  "sWidth": "120px", "sClass": "center" },
			{ "aTargets": [3], "bSearchable": true,  "bVisible": true,  "sWidth": "120px", "sClass": "center" },
			{ "aTargets": [5], "bSearchable": true,  "bVisible": true,  "sWidth": "100px", "sClass": "right" },
			{ "aTargets": [6], "bSearchable": false, "bVisible": true, "bSortable": false, "sWidth": "100px", "sClass": "center" },
		],
		"fnDrawCallback": function ( oSettings ) {
			/* Need to redo the counters if filtered or sorted */
			if ( oSettings.bSorted || oSettings.bFiltered )
				for ( var i=0, iLen=oSettings.aiDisplay.length ; i<iLen ; i++ )
					$('td:eq(0)', oSettings.aoData[ oSettings.aiDisplay[i] ].nTr ).html( i+1 );
		},
		"fnRowCallback": function (nRow, aData, iDisplayIndex) {
			$(nRow).on("click", function (event) {
				if ($(this).hasClass('row_selected')) {
					$(this).removeClass('row_selected');
				} else {
					oTableDtl.$('tr.row_selected').removeClass('row_selected');
					$(this).addClass('row_selected');
				}
			})
		},
		"bFilter": false,
		"bInfo": false,
		"sAjaxSource": "<? echo active_module_url($this->uri->segment(2)).'grid_detail/'.$dt['id']; ?>",
	});

	oTableDlg = $('#sspdTable').dataTable({
		"iDisplayLength": 10,
		"bJQueryUI": true,
		"bAutoWidth": false,
		"sPaginationType": "full_numbers",
		"sDom": '<"toolbar">frtip',
		"aaSorting": [[ 2, "desc" ]],
		"aoColumnDefs": [
			{ "aTargets": [0], "bSearchable": false, "bVisible": false, "bSortable": false, "sWidth": "", "sClass": "" },
			{ "aTargets": [1], "bSearchable": false, "bVisible": true, "bSortable": false, "sWidth": "40px", "sClass": "center" },
			{ "aTargets": [2], "bSearchable": true,  "bVisible": true,  "sWidth": "120px", "sClass": "center" },
			{ "aTargets": [3], "bSearchable": true,  "bVisible": true,  "sWidth": "120px", "sClass": "center" },
			{ "aTargets": [5], "bSearchable": true,  "bVisible": true,  "sWidth": "100px", "sClass": "right" },
		],
		"fnRowCallback": function (nRow, aData, iDisplayIndex) {
			$(nRow).on("click", function (event) {
				if ($(this).hasClass('row_selected')) {
					mID = '';
					$(this).removeClass('row_selected');
				} else {
					dtDlg = oTableDlg.fnGetData( this );
					mID = dtDlg[0];

					oTableDlg.$('tr.row_selected').removeClass('row_selected');
					$(this).addClass('row_selected');
				}
			});
		},
		"fnDrawCallback": function( oSettings ) {
			mID = '';
		},
		"oLanguage": {
			"sProcessing":   "<img border='0' src='<?=base_url('assets/pad/img/ajax-loader-big-circle-ball.gif')?>' />",
			"sLengthMenu":   "Tampilkan _MENU_",
			"sZeroRecords":  "Tidak ada data",
			"sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
			"sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
			"sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
			"sInfoPostFix":  "",
			"sSearch":       "Cari : ",
			"sUrl":          "",
		},
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?=active_module_url($this->uri->segment(2));?>grid_sspd/"+$('#ppat_id').val(),
	});

    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
        if(e.keyCode == 13) {
            oTableDlg.fnFilter(this.value);
        }
    });

	$('#btn_cancel').click(function() {
		window.location = '<?=active_module_url($this->uri->segment(2));?>';
	});

	$('#ppat_id').change(function(){
        reload_sspdGrid();
    });

	$('#btn_dialog_ok').click(function() {
		if (mID == '' || mID == null)
			alert('Data belum dipilih.');
		else {

            var rows = oTableDtl.fnGetNodes();
            for(var i=0;i<rows.length;i++) {
                if ($(rows[i]).find("td:eq(1)").html()==dtDlg[2]) {
                    alert('Berkas sudah ada dalam daftar');
                    return true;
                }
            }

            var aiNew = oTableDtl.fnAddData( [
                dtDlg[0],
                '',
                dtDlg[2],
                dtDlg[3],
                dtDlg[4],
                dtDlg[5],
                '<a class="delete" href="">Hapus</a>',
            ] );
            var nRow = oTableDtl.fnGetNodes( aiNew[0] );

			$('#sspdDialog').modal('hide');
		}
	});

	$('#dTable a.delete').live('click', function (e) {
		e.preventDefault();

		var nRow = $(this).parents('tr')[0];
		oTableDtl.fnDeleteRow( nRow );
        oTableDtl.fnDraw();
	});

    $('#tgl_keluar').formatter({
        'pattern': '{{99}}-{{99}}-{{9999}}',
    });

    $("#tgl_keluar").datepicker({
		dateFormat:'dd-mm-yy',
		changeMonth:true, 
		changeYear:true,
    });

	$("#myform").submit(function(eventObj){
		if ($('#penerima').val()=='' || $('#tgl_keluar').val()=='' ) {
			alert('Harap melengkapi isian data!');
			return false;
		}
		var data = JSON.stringify({ "dtDetail" : oTableDtl.fnGetData() });
		$('<input type="hidden" name="dtDetail"/>').val(data).appendTo('#myform');
		return true;
	});
});
</script>

<div class="content">
	<!-- Modal -->
	<div id="sspdDialog" class="modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="sspdDialogLabel">Berkas SSPD</h3>
		</div>
		<div class="modal-body">
			<table class="display dataTables" id="sspdTable">
				<thead>
					<tr>
						<th>index</th>
						<th>No.</th>
						<th>No. Registrasi/<br>SSPD BPHTB</th>
						<th>NOP - Tahun SPPT</th>
						<th>Nama WP</th>
						<th>Nominal<br>BPHTB</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<div class="modal-footer">
			<button class="btn btn-primary" id="btn_dialog_ok">OK!</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Batal</button>
		</div>
	</div>

    <div class="container-fluid">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#"><strong>Berkas Keluar</strong></a>
			</li>
		</ul>

		<?php
		if(validation_errors()){
			echo '<blockquote><strong>Harap melengkapi data berikut :</strong>';
			echo validation_errors('<small>','</small>');
			echo '</blockquote>';
		} ?>

		<?php echo form_open($faction, array('id'=>'myform','class'=>'form-horizontal'));?>
			<input type="hidden" name="id" value="<?=$dt['id']?>"/>
			<div class="control-group">
				<label class="control-label" for="tahun">No. Berkas</label>
				<div class="controls">
					<input class="input-small" type="text" name="berkasno" id="berkasno" value="<?=$dt['berkasno']?>" readonly />
                    &nbsp;&nbsp;&nbsp;Tgl. Keluar&nbsp;&nbsp;&nbsp;
					<input class="input" type="text" name="tgl_keluar" id="tgl_keluar" value="<?=!empty($dt['tgl_keluar']) ? $dt['tgl_keluar'] : date('d-m-Y'); ?>" style="width:68px;" placeholder="dd-mm-yyyy"/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="ppat_id">PPAT</label>
				<div class="controls">
					<select class="input input-xlarge" name="ppat_id" id="ppat_id">
					<?php if( isset($ppat) && $ppat): ?>
                        <option value="">--Pilih PPAT--</option>
						<?php foreach($ppat as $data): ?>
							<option value="<?php echo $data->id;?>" <?php if($dt['ppat_id']==$data->id) echo 'selected';?>><?php echo $data->kode . " - " . $data->nama;?></option>
						<?php endforeach;?>
                    <?php else:?>
                        <option value="">Tidak ada data!</option>
                    <?php endif; ?>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="penerima">Penerima</label>
				<div class="controls">
					<input class="input-xlarge" style="width:256px;" type="text" name="penerima" id="penerima" value="<?=$dt['penerima']?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="notes">Catatan</label>
				<div class="controls">
					<input class="input input-xxlarge" type="text" name="notes" id="notes" value="<?=$dt['notes']?>" />
				</div>
			</div>

			<!--- Detail -->
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#"><strong>Daftar Berkas</strong></a>
				</li>
			</ul>
			<div class="row">
				<div class="span10">
                    <table class="display dataTables" id="dTable">
						<thead>
							<tr>
                                <th>index</th>
                                <th>No.</th>
                                <th>No. Registrasi/<br>SSPD BPHTB</th>
                                <th>NOP - Tahun SPPT</th>
                                <th>Nama WP</th>
                                <th>Nominal<br>BPHTB</th>
								<th>Batal</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>

			<hr>
			<button type="button" class="btn btn-success" data-toggle="modal" data-target="#sspdDialog">Tambah Berkas</button>
			<button type="submit" class="btn btn-primary">Simpan</button>
			<button type="button" class="btn" id="btn_cancel">Batal</button>
			<div class="control-group">
				<div class="controls">
				</div>
			</div>
		<?php echo form_close();?>
    </div>
</div>
<script type="text/javascript">document.forms['myform'].elements['id'].focus();</script>
<? $this->load->view('_foot'); ?>