<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>

<script>
var mID;
var oTable;
var xRow;

$(document).ready(function() {
    
    $("#main_tabs").tabs();
    
	oTable = $('#table1').dataTable({
		"bScrollCollapse": true,
		"bJQueryUI": true,
		"bPaginate": true,
		"sPaginationType": "full_numbers", 
		"sDom": '<"toolbar">frtip',

		"aoColumnDefs": [
			{ "bSearchable": false, "bVisible": false, "aTargets": [ 0 ] },
            { "bSearchable": false, "bSortable": false, "aTargets": [ 1 ] }
		],
		"aoColumns": [
            null,
            { "sWidth": "5%" },
			{ "sWidth": "6%" },
            null,
            null,
            { "sWidth": "20%"}
		],
		"fnRowCallback": function (nRow, aData, iDisplayIndex) {
            $('td:eq(0)', nRow).html( numberFormatter(iDisplayIndex + oTable.fnSettings()._iDisplayStart + 1, 0, '.', ',') );
			$(nRow).on("click", function (event) {
				if(aData[0]!=xRow) {
					if ($(this).hasClass('row_selected')) {
						$(this).removeClass('row_selected');
					} else {
						oTable.$('tr.row_selected').removeClass('row_selected');
						$(this).addClass('row_selected');
					}

					var data = oTable.fnGetData( this );
					mID = data[0];
				}
				xRow = aData[0];
			})
		},
		"bSort": true,
		"bInfo": true,
		"bProcessing": false,
		"sAjaxSource": "<?=active_module_url();?>ppat/grid"
	});
    
    var btn = '<div class="btn-group pull-left">' +
              '<button id="btn_tambah" class="btn pull-left" type="button">Tambah</button>' +
              '<button id="btn_edit" class="btn pull-left" type="button">Edit</button>' +
              '<button id="btn_delete" class="btn pull-left" type="button">Hapus</button></div>';
    
	$("div.toolbar").html(btn);
    
	$('#btn_tambah').click(function() {
		window.location = '<?=active_module_url();?>ppat/add/';
	});

	$('#btn_edit').click(function() {
		if(mID) {
			window.location = '<?=active_module_url();?>ppat/edit/'+mID;
		}else{
			alert('Silahkan pilih data yang akan diedit');
		}
	});

	$('#btn_delete').click(function() {
		if(mID) {
			var hapus = confirm('Hapus data ini?');
			if(hapus==true) {
				window.location = '<?=active_module_url();?>ppat/delete/'+mID;
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
			<li class="active">
				<a href="#"><strong>Daftar PPAT</strong></a>
			</li>
		</ul>

		<?=msg_block();?>
		
		<table class="table display" id="table1">
			<thead>
				<tr>
					<th>ID</th>
					<th>No</th>
					<th>Kode</th>
					<th>Nama</th>
					<th>Alamat</th>
					<th>Wilayah Kerja</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
    </div>
</div>

<? $this->load->view('_foot'); ?>