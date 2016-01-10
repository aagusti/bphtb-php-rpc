<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>

<script type="text/javascript">
function reload_grid() {
    var tgl1 = $('#tgl1').val();
    var tgl2 = $('#tgl2').val();
	oTable.fnReloadAjax("<? echo active_module_url($this->uri->segment(2)); ?>grid/"+tgl1+"/"+tgl2);
}

var mID;
var approved;
var oTable;
var xRow;
var canEditDelete=true;

$(document).ready(function() {    
	oTable = $('#table1').dataTable({
		"iDisplayLength": 10,
		"bJQueryUI": true,
		"bAutoWidth": false,
		"sPaginationType": "full_numbers",
		"sDom": '<"toolbar">frtip',
		"aaSorting": [[ 3, "desc" ]],
		"aoColumnDefs": [
			{ "aTargets": [0], "bSearchable": false, "bVisible": false, "bSortable": false, "sWidth": "", "sClass": "" },
			{ "aTargets": [1], "bSearchable": false, "bVisible": true, "bSortable": false, "sWidth": "40px", "sClass": "center" },
			{ "aTargets": [2], "bSearchable": true,  "bVisible": true,  "sWidth": "80px", "sClass": "center" },
			{ "aTargets": [9], "bSearchable": false,  "bVisible": false,  "sWidth": "80px", "sClass": "right" },
			{ "aTargets": [10], "bSearchable": true,  "bVisible": true,  "sWidth": "80px", "sClass": "right" },
			{ "aTargets": [11], "bSearchable": true,  "bVisible": true,  "sWidth": "80px", "sClass": "right" },
			{ "aTargets": [12], "bSearchable": true,  "bVisible": true,  "sWidth": "80px", "sClass": "center" },
			{ "aTargets": [13], "bSearchable": false,  "bVisible": true,  "sWidth": "50px", "sClass": "center" },
			{ "aTargets": [14], "bSearchable": false,  "bVisible": true,  "sWidth": "50px", "sClass": "center" },
			{ "aTargets": [15], "bSearchable": false,  "bVisible": true,  "sWidth": "80px", "sClass": "center" },
			{ "aTargets": [16], "bSearchable": false,  "bVisible": true,  "sWidth": "50px", "sClass": "center" },
		],
		"fnRowCallback": function (nRow, aData, iDisplayIndex) {
			$(nRow).on("click", function (event) {
				if ($(this).hasClass('row_selected')) {
					mID = '';approved='';
					$(this).removeClass('row_selected');
				} else {
					var data = oTable.fnGetData( this );
					mID = data[0];
                    approved = data[16];
					
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
	
    var mytoolbar = '<div class="btn-group pull-left">' +
                    '<button id="btn_tambah" class="btn" type="button">Tambah</button>' +
                    '<button id="btn_edit" class="btn" type="button">Edit</button>' +
                    '<button id="btn_delete" class="btn" type="button">Hapus</button>' +
                    '</div>' +
                    
                    '<div class="btn-group pull-left">' +
                    '<button id="btn_print_form" type="button" data-value="printed" class="btn btn-info">Print Form</button>' +
                    '<button id="btn_print" type="button" data-value="printed" class="btn btn-info">Print Ploted</button>' +
                    '<button id="btn_print_approve" type="button" data-value="printed" class="btn btn-info">Print Approved</button>' +
                    '</div>' +
                    
                    '<div class="btn-group pull-left">' +
                    '    <div class="input-prepend"><span class="add-on"><i class="icon-calendar"></i></span><input style="width:64px;" id="tgl1" name="tgl1" width="5" type="text" placeholder="dd-mm-yyyy" value="<?=date('01-01-Y');?>"/></div>' +
                    '</div>' +
                    '<div class="btn-group pull-left">' +
                    '    <div class="input-prepend"><span class="add-on">s/d</span><input style="width:64px;" id="tgl2" name="tgl2" type="text" placeholder="dd-mm-yyyy" value="<?=date('d-m-Y');?>"/></div>' +
                    '</div>';

	$("div.toolbar").html(mytoolbar);
            
	$( "#tgl1, #tgl2" ).datepicker({
		dateFormat:'dd-mm-yy', 
		changeMonth:true, 
		changeYear:true,
		onSelect: function() {
            reload_grid();
		}
	});
        
	$('#btn_tambah').click(function() {
		window.location = '<?=active_module_url($this->uri->segment(2));?>add/';
	});

	$('#btn_edit').click(function() {
		if(mID) {
            if (canEditDelete==false) {
                alert(msgWarn + ' Tidak dapat diedit');
            } else {
                window.location = '<?=active_module_url($this->uri->segment(2));?>edit/'+mID;
            }
		}else{
			alert('Silahkan pilih data yang akan diedit');
		}
	});

	$('#btn_delete').click(function() {
		if(mID) {
            if (canEditDelete==false) {
                alert(msgWarn + ' Tidak dapat dihapus');
            } else {
                var hapus = confirm('Hapus data ini?');
                if(hapus==true) {
                    window.location = '<?=active_module_url($this->uri->segment(2));?>delete/'+mID;
                }
            }
		}else{
			alert('Silahkan pilih data yang akan dihapus');
		}
	});
    
    $('#btn_print_form').click(function() {
        if(mID) {				
			var rpt = 'bphtb_sspd_formated';
			var winparams = 'location=1,status=1,scrollbars=1,resizable=no,width='+screen.width+',height='+screen.height+',menubar=no,toolbar=no,fullscreen=no';
			window.open('<?=active_module_url($this->uri->segment(2));?>show_rpt/?'+rpt+'/'+mID, 'Laporan', winparams);
        } else {
            alert('Silahkan pilih data yang akan diprint');
        }
    });
    
    $('#btn_print').click(function() {
        if(mID) {			
			var rpt = 'bphtb_sspd_plotted';
			var winparams = 'location=1,status=1,scrollbars=1,resizable=no,width='+screen.width+',height='+screen.height+',menubar=no,toolbar=no,fullscreen=no';
			window.open('<?=active_module_url($this->uri->segment(2));?>show_rpt/?'+rpt+'/'+mID, 'Laporan', winparams);
        } else {
            alert('Silahkan pilih data yang akan diprint');
        }
    });
    
    $('#btn_print_approve').click(function() {
        if(mID) {
            if(approved) {
                var rpt = 'bphtb_sspd_approved';
                var winparams = 'location=1,status=1,scrollbars=1,resizable=no,width='+screen.width+',height='+screen.height+',menubar=no,toolbar=no,fullscreen=no';
                window.open('<?=active_module_url($this->uri->segment(2));?>show_rpt/?'+rpt+'/'+mID, 'Laporan', winparams);
            } else {
                alert('Data ini belum di approve!');
            }
        } else {
            alert('Silahkan pilih data yang akan diprint');
        }
    });
});
</script>

<div class="content">
    <div class="container-fluid"> 
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#main_grid"><strong>Registrasi SSPD KB</strong></a></li>
        </ul>
        <?=msg_block();?>
    
        <table class="display dataTables" id="table1">
			<thead>
				<tr>
					<th>ids</th>
					<th>No.</th>
					<th>No.SSPD</th>
					<th>Tgl. Transaksi</th>
					<th>Nama WP</th>
					<th>PPAT</th>
					<th>NOP</th>
					<th>Thn.<br>SPPT</th>
					<th>Jns. Perolehan</th>
					<th>Dasar Perhitungan</th>
					<th>NPOP</th>
					<th>BPHTB Terhutang</th>
					<th>Tgl.Jatuh Tempo</th>
					<th>Status<br>Pmb.</th>
					<th>Status<br>Pmh.</th>
					<th>Hasil<br>Penelitian</th>
					<th>Approved</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>

<? $this->load->view('_foot'); ?>