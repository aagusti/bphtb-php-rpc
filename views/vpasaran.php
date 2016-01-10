<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>

<script>
var mID;
var oTable;
var xRow;

$(document).ready(function() {    
	oTable = $('#table1').dataTable({
		"iDisplayLength": 15,
		"bJQueryUI": true,
		"bAutoWidth": false,
		"sPaginationType": "full_numbers",
		"sDom": '<"toolbar">frtip',
		"aaSorting": [[ 2, "desc" ]],
		"aoColumnDefs": [
			{ "aTargets": [0], "bSearchable": false, "bVisible": false, "bSortable": false, "sWidth": "", "sClass": "" },
			{ "aTargets": [1], "bSearchable": false, "bVisible": true,  "bSortable": false, "sWidth": "40px", "sClass": "center" },
			{ "aTargets": [2], "bSearchable": true,  "bVisible": true,  "sWidth": "140px", "sClass": "center" },
			{ "aTargets": [4], "bSearchable": true,  "bVisible": true,  "sWidth": "100px", "sClass": "right" },
		],
		"fnRowCallback": function (nRow, aData, iDisplayIndex) {
			$(nRow).on("click", function (event) {
				if ($(this).hasClass('row_selected')) {
					mID = '';
					$(this).removeClass('row_selected');
				} else {
					var data = oTable.fnGetData( this );
					mID = data[0];
					
					oTable.$('tr.row_selected').removeClass('row_selected');
					$(this).addClass('row_selected');
				}
			});
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
		"sAjaxSource": "<?=active_module_url($this->uri->segment(2));?>grid",
	});
    
    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
        if(e.keyCode == 13) {
            oTable.fnFilter(this.value);   
        }
    });

	$("div.toolbar").html('<div class="btn-group pull-left">' +
                        '<button id="btn_tambah" class="btn pull-left btn-static" type="button">Tambah</button>' +
                        '<button id="btn_edit" class="btn pull-left btn-static" type="button">Edit</button>' +
                        '<button id="btn_delete" class="btn pull-left btn-static" type="button">Hapus</button></div>');

	$('#btn_tambah').click(function() {
		window.location = '<?=active_module_url();?>pasaran/add/';
	});

	$('#btn_edit').click(function() {
		if(mID) {
			window.location = '<?=active_module_url();?>pasaran/edit/'+mID;
		}else{
			alert('Silahkan pilih data yang akan diedit');
		}
	});

	$('#btn_delete').click(function() {
		if(mID) {
			var hapus = confirm('Hapus data ini?');
			if(hapus==true) {
				window.location = '<?=active_module_url();?>pasaran/delete/'+mID;
			};
		}else{
			alert('Silahkan pilih data yang akan dihapus');
		}
	});    
});
</script>

<div class="content">
    <div class="container-fluid">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#main_grid"><strong>Harga Pasar</strong></a></li>
        </ul>
        <?=msg_block();?>
    
        <table class="table display" id="table1">
			<thead class="head">
				<tr>
					<th>ID</th>
					<th>No</th>
					<th>Kode</th>
					<th>Uraian</th>
					<th>Harga Pasar</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
    </div>
</div>
<? $this->load->view('_foot'); ?>