<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>

<script>
var mID;
var dID;
var oTable;
var oTable2;

$(document).ready(function() {
	oTable = $('#table1').dataTable({
		"sScrollY": "320px",
		"bScrollCollapse": true,
		"bPaginate": false,
		"bJQueryUI": true,
		"sDom": '<"toolbarx">frtip',

		"aoColumnDefs": [
			{ "bSearchable": false, "bVisible": false, "aTargets": [ 0 ]
            }
		],
		"aoColumns": [
			null,
			{ "sWidth": ""},
			{ "sWidth": "20%", "sClass":"right" },
			{ "sWidth": "20%", "sClass":"right" },
		],
		"fnRowCallback": function (nRow, aData, iDisplayIndex) {
			$(nRow).on("click", function (event) {
				if ($(this).hasClass('row_selected')) {
					/* mID = '';
					$(this).removeClass('row_selected');
					oTable2.fnReloadAjax("<?=active_module_url();?>user_validasi/grid2/"); */
				} else {
					var data = oTable.fnGetData( this );
					mID = data[0];
					dID = '';
					
					oTable.$('tr.row_selected').removeClass('row_selected');
					$(this).addClass('row_selected');
					
					if($('#in_group').is(':checked')) {
						oTable2.fnReloadAjax("<?=active_module_url();?>user_validasi/grid2/"+mID+"/true");
					} else {
						oTable2.fnReloadAjax("<?=active_module_url();?>user_validasi/grid2/"+mID);
					}
				}
			})
		},
		"fnInitComplete": function(oSettings, json) {
			if (!mID) selecttopRow();
		},
		"bSort": true,
		"bInfo": false,
		"bFilter": false,
		"bProcessing": false,
		"sAjaxSource": "<?=active_module_url();?>user_validasi/grid"
	});

	oTable2 = $('#table2').dataTable({
		"sScrollY": "320px",
		"bScrollCollapse": true,
		"bPaginate": false,
		"bJQueryUI": true,
		"sDom": '<"toolbar2">frtip',

		"aoColumnDefs": [
			{ "bSearchable": false, "bVisible": false, "aTargets": [ 0 ] }
		],
		"aaSorting": [[ 2, "asc" ]],
		"aoColumns": [
			null,
			{ "sWidth": "4%",  "sClass": "center"},
			{ "sWidth": "10%" },
			{ "sWidth": "20%" },
		],
		"fnRowCallback": function (nRow, aData, iDisplayIndex) {
			$(nRow).on("click", function (event) {
				if ($(this).hasClass('row_selected')) {
					/* dID = '';
					$(this).removeClass('row_selected'); */
				} else {
					var data = oTable2.fnGetData( this );
					dID = data[0];
					
					oTable2.$('tr.row_selected').removeClass('row_selected');
					$(this).addClass('row_selected');
				}
			})
		},
		"bSort": true,
		"bInfo": false,
		"bProcessing": false,
		"bFilter": false,
		"sAjaxSource": "<?=active_module_url();?>user_validasi/grid2/"
	});

	function selecttopRow() {
		var nTop = $('#table1 tbody tr')[0];
		var iPos = oTable.fnGetPosition( nTop );

		/* Use iPos to select the row */
		var data = oTable.fnGetData(iPos);
		mID = data[0];
		dID = '';
					
		$('#table1 tbody tr:eq(0)').addClass('row_selected');
		
		if($('#in_group').is(':checked')) {
			oTable2.fnReloadAjax("<?=active_module_url();?>user_validasi/grid2/"+mID+"/true");
		} else {
			oTable2.fnReloadAjax("<?=active_module_url();?>user_validasi/grid2/"+mID);
		}
	}
});

function update_stat(gid, id, a) {
	var val = Number(a);
	$.ajax({
	  url: '<?php echo active_module_url()?>user_validasi/update_stat/' + gid +'/' + id + '/' + val,
	  success: function(data) {
		/*  */
	  }
	});
}
</script>

<div class="content">
    <div class="container-fluid">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#"><strong>Level User (Validasi)</strong></a>
			</li>
		</ul>
		
		<?=msg_block();?>
		
		<div class="row-fluid">
			<div class="span4">
				<p>Level (Per Nilai NPOP):</p>
				<table class="table" id="table1">
					<thead>
						<tr>
							<th>Index</th>
							<th>Uraian</th>
							<th>Min.Nilai</th>
							<th>Max.Nilai</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<div class="span6">
				<p>Daftar User BPHTB:</p>
				<table class="table" id="table2">
					<thead>
						<tr>
							<th>Index</th>
							<th>In Group</th>
							<th>Nama</th>
							<th>Jabatan</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<? $this->load->view('_foot'); ?>