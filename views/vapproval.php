<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>
<script>

function switch_btn() {
    var proses_id  = $('#proses_id').val();
    if (proses_id == 2) {
        $('#btn_proses').hide();
        $('#btn_batal').show();
    } else {
        $('#btn_proses').show();
        $('#btn_batal').hide();
    }
}

function reload_grid() {
	var proses_id  = $('#proses_id').val();	
    var tgl1 = $('#tgl1').val();
    var tgl2 = $('#tgl2').val();
	oTable.fnReloadAjax("<? echo active_module_url($this->uri->segment(2)); ?>grid/"+tgl1+"/"+tgl2+"/"+proses_id);
}

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
		"aaSorting": [[ 8, "desc" ]],
		"aoColumnDefs": [
			{ "aTargets": [0], "bSearchable": false, "bVisible": false, "bSortable": false, "sWidth": "", "sClass": "" },
			{ "aTargets": [1], "bSearchable": false, "bVisible": true, "bSortable": false, "sWidth": "40px", "sClass": "center" },
			{ "aTargets": [2], "bSearchable": true,  "bVisible": true,  "sWidth": "80px", "sClass": "" },
			{ "aTargets": [3], "bSearchable": true,  "bVisible": true,  "sWidth": "77px", "sClass": "center" },
			{ "aTargets": [4], "bSearchable": true,  "bVisible": true,  "sWidth": "140px", "sClass": "center" },
			{ "aTargets": [5], "bSearchable": true,  "bVisible": true,  "sWidth": "60px", "sClass": "center" },
			{ "aTargets": [7], "bSearchable": true,  "bVisible": true,  "sWidth": "100px", "sClass": "right" },
			{ "aTargets": [8], "bSearchable": true,  "bVisible": true,  "sWidth": "100px", "sClass": "center" },
			{ "aTargets": [9], "bSearchable": true,  "bVisible": true,  "sWidth": "77px", "sClass": "center" },
			{ "aTargets": [10], "bSearchable": true,  "bVisible": true,  "sWidth": "180px", "sClass": "" },
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
        "fnServerParams": function ( aoData ) {
            var proses_id = $('#proses_id').val();
            var tgl1 = $('#tgl1').val();
            var tgl2 = $('#tgl2').val();
            aoData.push({"name": "proses_id", "value": proses_id});
            aoData.push({"name": "tgl1", "value": tgl1});
            aoData.push({"name": "tgl2", "value": tgl2});
        }
	});
    
    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
        if(e.keyCode == 13) {
            oTable.fnFilter(this.value);   
        }
    });
		
    var mytoolbar = '<div class="btn-group pull-left">' +
                    '<button id="btn_proses" class="btn btn-primary" type="button">Proses</button>' +
                    '<button id="btn_batal" class="btn btn-primary hide" type="button">Pembatalan</button>' +
                    '</div>' +
                    '<div class="btn-group pull-left">'+
                    '<?=$select_proses;?>'+
                    '</div>'+
                    
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

	$('#btn_proses').click(function() {
        var proses_id = $('#proses_id').val();
        if(proses_id != 2) {
            if(mID) 
                window.location = '<?=active_module_url($this->uri->segment(2));?>approve/'+mID;
            else
                alert('Silahkan pilih data yang akan diproses');
        } else
            alert('Data ini sudah diproses!');
	});
    
	$('#btn_batal').click(function() {
        var proses_id = $('#proses_id').val();
        if(proses_id == 2) {
            if(mID) {
                if(confirm('Proses pembatalan untuk data ini?')) {
                    $.ajax({
                        url: '<?=active_module_url($this->uri->segment(2));?>batal/'+mID,
                        async: false,
                        success: function (data) {
                            if (data != 'ok') 
                                alert('Pembatalan gagal');
                            else reload_grid();
                        },
                        error: function (xhr, desc, er) {
                            alert(er);
                        }
                    });
                }
            } else
                alert('Silahkan pilih data yang akan diproses');
        }
	});
    
	$("#proses_id").change(function() {
        switch_btn();
		reload_grid();
	});
	
});
</script>

<div class="content">
    <div class="container-fluid">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#main_grid"><strong>Approval</strong></a></li>
        </ul>
        <?=msg_block();?>
        <table class="display dataTables" id="table1">
            <thead class="head">
                <tr>
                    <th>xx</th>
                    <th>No.</th>
                    <th>No.<br>Approval</th>
                    <th>Tgl.<br>Approval</th>
                    <th>NOP</th>
                    <th>Thn.<br>SPPT</th>
                    <th>Nama WP</th>
                    <th>Jumlah<br>Bayar</th>
                    <th>No. SSPD</th>
                    <th>Tgl. SSPD</th>
                    <th>PPAT</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<? $this->load->view('_foot'); ?>