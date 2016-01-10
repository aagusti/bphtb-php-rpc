<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>

<style>
.page-header {
    background-color: #fff;
    padding: 4px 4px;
    padding-left: 0px;
    margin: 0px 0px 8px;
}
</style>

<script type="text/javascript">
function ceklik() {
    if($(".cek_sspd_id").length == $(".cek_sspd_id:checked").length) 
        $("#cek_selectall").attr("checked", "checked");
    else
        $("#cek_selectall").removeAttr("checked");
}
    
$(function(){
    $("#cek_selectall").click(function () {
          $('.cek_sspd_id').attr('checked', this.checked);
    });
});

function clear_form() {
    $("#myform").trigger("reset");
}

function load_data() {
    if (mID) {
        clear_form();
        $.ajax({
            url: "<?=active_module_url('bphtb_unposted/load_data')?>"+mID,
            success: function (json) {
                data = JSON.parse(json);
                if(data['found']!=0) {
                    $("#bphtb_sspd_id").val(mID);
                    $("#nop").val(data['bphtb_nop']);
                    $("#sp_id").val(data['subjek_pajak_id']);
                    
                    //BPHTB
                    $("#wp_nama").val(data['bphtb_wp_nama']);
                    $("#wp_npwp").val(data['bphtb_wp_npwp']);
                    $("#wp_alamat").val(data['bphtb_wp_alamat']);
                    $("#wp_blok_kav").val(data['bphtb_wp_blok_kav']);
                    $("#wp_kelurahan").val(data['bphtb_wp_kelurahan']);
                    $("#wp_rt").val(data['bphtb_wp_rt']);
                    $("#wp_rw").val(data['bphtb_wp_rw']);
                    $("#wp_kecamatan").val(data['bphtb_wp_kecamatan']);
                    $("#wp_kota").val(data['bphtb_wp_kota']);
                    $("#wp_provinsi").val(data['bphtb_wp_provinsi']);
                    $("#wp_identitas").val(data['bphtb_wp_identitas']);
                    
                    $("#bphtb_nop").val(data['bphtb_nop_formated']);
                    $("#op_alamat").val(data['bphtb_op_alamat']);
                    $("#op_blok_kav").val(data['bphtb_op_blok_kav']);
                    $("#op_rt").val(data['bphtb_op_rt']);
                    $("#op_rw").val(data['bphtb_op_rw']);
                    $("#bumi_luas").val(data['bphtb_bumi_luas']);
                    $("#bumi_njop").val(data['bphtb_bumi_njop']);
                    $("#bng_luas").val(data['bphtb_bng_luas']);
                    $("#bng_njop").val(data['bphtb_bng_njop']);
                    
                    
                    //SISMIOP
                    $("#wp_nama2").val(data['nm_wp']);
                    $("#wp_npwp2").val(data['npwp']);
                    $("#wp_alamat2").val(data['jalan_wp']);
                    $("#wp_blok_kav2").val(data['blok_kav_no_wp']);
                    $("#wp_kelurahan2").val(data['kelurahan_wp']);
                    $("#wp_rt2").val(data['rt_wp']);
                    $("#wp_rw2").val(data['rw_wp']);
                    $("#wp_kecamatan2").val(data['kecamatan_wp']);
                    $("#wp_kota2").val(data['kota_wp']);
                    $("#wp_provinsi2").val(data['propinsi_wp']);
                    $("#wp_identitas2").val(data['subjek_pajak_id']);
                    
                    $("#pbb_nop").val(data['bphtb_nop_formated']);
                    $("#op_alamat2").val(data['jalan_op']);
                    $("#op_blok_kav2").val(data['blok_kav_no_op']);
                    $("#op_rt2").val(data['rt_op']);
                    $("#op_rw2").val(data['rw_op']);
                    $("#bumi_luas2").val(data['total_luas_bumi']);
                    $("#bumi_njop2").val(data['njop_bumi']);
                    $("#bng_luas2").val(data['total_luas_bng']);
                    $("#bng_njop2").val(data['njop_bng']);
                    
                } else {
                    alert('Data tidak ditemukan');
                }
                compare_data();
            },
            error: function (xhr, desc, er) {
                alert(er);
            }
        });
    } else {
        alert ('NOP belum terpilih!');
    }
    return false;
}

function compare_data() {
    $("#bphtb_wp_nama").removeClass('error');
    $("#bphtb_wp_npwp").removeClass('error');
    $("#bphtb_wp_alamat").removeClass('error');
    $("#bphtb_wp_blok_kav").removeClass('error');
    $("#bphtb_wp_kelurahan").removeClass('error');
    $("#bphtb_wp_rt_rw").removeClass('error');
    $("#bphtb_wp_kecamatan").removeClass('error');
    $("#bphtb_wp_kota").removeClass('error');
    $("#bphtb_wp_provinsi").removeClass('error');
    $("#bphtb_wp_identitas").removeClass('error');
    $("#bphtb_op_alamat").removeClass('error');
    $("#bphtb_op_blok_kav").removeClass('error');
    $("#bphtb_op_rt_rw").removeClass('error');
    $("#bphtb_bumi_luas").removeClass('error');
    $("#bphtb_bumi_njop").removeClass('error');
    $("#bphtb_bng_luas").removeClass('error');
    $("#bphtb_bng_njop").removeClass('error');
    
    $("#sismiop_wp_nama").removeClass('error');
    $("#sismiop_wp_npwp").removeClass('error');
    $("#sismiop_wp_alamat").removeClass('error');
    $("#sismiop_wp_blok_kav").removeClass('error');
    $("#sismiop_wp_kelurahan").removeClass('error');
    $("#sismiop_wp_rt_rw").removeClass('error');
    $("#sismiop_wp_kecamatan").removeClass('error');
    $("#sismiop_wp_kota").removeClass('error');
    $("#sismiop_wp_provinsi").removeClass('error');
    $("#sismiop_wp_identitas").removeClass('error');
    $("#sismiop_op_alamat").removeClass('error');
    $("#sismiop_op_blok_kav").removeClass('error');
    $("#sismiop_op_rt_rw").removeClass('error');
    $("#sismiop_bumi_luas").removeClass('error');
    $("#sismiop_bumi_njop").removeClass('error');
    $("#sismiop_bng_luas").removeClass('error');
    $("#sismiop_bng_njop").removeClass('error');
    
    if($("#wp_nama").val() != $("#wp_nama2").val()) {
        $('#bphtb_wp_nama').addClass('error');
        $('#sismiop_wp_nama').addClass('error');
    }
    
    if($("#wp_npwp").val() != $("#wp_npwp2").val()) {
        $('#bphtb_wp_npwp').addClass('error');
        $('#sismiop_wp_npwp').addClass('error');
    }
    
    if($("#wp_alamat").val() != $("#wp_alamat2").val()) {
        $('#bphtb_wp_alamat').addClass('error');
        $('#sismiop_wp_alamat').addClass('error');
    }
    
    if($("#wp_blok_kav").val() != $("#wp_blok_kav2").val()) {
        $('#bphtb_wp_blok_kav').addClass('error');
        $('#sismiop_wp_blok_kav').addClass('error');
    }
    
    if($("#wp_kelurahan").val() != $("#wp_kelurahan2").val()) {
        $('#bphtb_wp_kelurahan').addClass('error');
        $('#sismiop_wp_kelurahan').addClass('error');
    }
    
    if($("#wp_rt").val() != $("#wp_rt2").val()) {
        $('#bphtb_wp_rt_rw').addClass('error');
        $('#sismiop_wp_rt_rw').addClass('error');
    }
    
    if($("#wp_rw").val() != $("#wp_rw2").val()) {
        $('#bphtb_wp_rt_rw').addClass('error');
        $('#sismiop_wp_rt_rw').addClass('error');
    }
    
    if($("#wp_kecamatan").val() != $("#wp_kecamatan2").val()) {
        $('#bphtb_wp_kecamatan').addClass('error');
        $('#sismiop_wp_kecamatan').addClass('error');
    }
    
    if($("#wp_kota").val() != $("#wp_kota2").val()) {
        $('#bphtb_wp_kota').addClass('error');
        $('#sismiop_wp_kota').addClass('error');
    }
    
    if($("#wp_provinsi").val() != $("#wp_provinsi2").val()) {
        $('#bphtb_wp_provinsi').addClass('error');
        $('#sismiop_wp_provinsi').addClass('error');
    }
    
    if($("#wp_identitas").val().trim() != $("#wp_identitas2").val().trim()) {
        $('#bphtb_wp_identitas').addClass('error');
        $('#sismiop_wp_identitas').addClass('error');
    }
    
    
    if($("#op_alamat").val() != $("#op_alamat2").val()) {
        $('#bphtb_op_alamat').addClass('error');
        $('#sismiop_op_alamat').addClass('error');
    }
    
    if($("#op_blok_kav").val() != $("#op_blok_kav2").val()) {
        $('#bphtb_op_blok_kav').addClass('error');
        $('#sismiop_op_blok_kav').addClass('error');
    }
    
    if($("#op_rt").val() != $("#op_rt2").val()) {
        $('#bphtb_op_rt_rw').addClass('error');
        $('#sismiop_op_rt_rw').addClass('error');
    }
    
    if($("#op_rw").val() != $("#op_rw2").val()) {
        $('#bphtb_op_rt_rw').addClass('error');
        $('#sismiop_op_rt_rw').addClass('error');
    }
    
    if($("#bumi_luas").val() != $("#bumi_luas2").val()) {
        $('#bphtb_op_bumi').addClass('error');
        $('#sismiop_op_bumi').addClass('error');
    }
    
    if($("#bumi_njop").val() != $("#bumi_njop2").val()) {
        $('#bphtb_op_bumi').addClass('error');
        $('#sismiop_op_bumi').addClass('error');
    }
    
    if($("#bng_luas").val() != $("#bng_luas2").val()) {
        $('#bphtb_op_bng').addClass('error');
        $('#sismiop_op_bng').addClass('error');
    }
    
    if($("#bng_njop").val() != $("#bng_njop2").val()) {
        $('#bphtb_op_bng').addClass('error');
        $('#sismiop_op_bng').addClass('error');
    }
}

var mID;
var oTable;
var xRow;
var canEditDelete=true;

$(document).ready(function() {    
	oTable = $('#table1').dataTable({
		"iDisplayLength": 16,
		"bJQueryUI": true,
		"bSort": true,
		"bInfo": true,
	
		"bPaginate": true,
		"bLengthChange": false,

		"sPaginationType": "full_numbers",
		"sDom": '<"toolbar">frtip',
		"aaSorting": [[ 1, "desc" ]],
        
		"aoColumnDefs": [
			{ "bSearchable": false, "bVisible": false, "aTargets": [ 0 ] },
		],
		"aoColumns": [
            null,
            { "swidth": "4%", "bSearchable": false, "bSortable": false},
			null,
            { "swidth": "4%", "bSearchable": false, "bSortable": false},
		],
		"fnRowCallback": function (nRow, aData, iDisplayIndex) {
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
                    
                    load_data();
				}
				xRow = aData[0];
			});
            $(nRow).children("td.cek_sspd_id").on("click", function(event) {
                /*
                if($(nRow).children("td.cek_sspd_id").length > 0)
                    alert('a');
                else
                    alert('b');
                */
            });
		},
		"oLanguage": {
			"sLengthMenu":   "Tampilkan _MENU_",
			"sZeroRecords":  "Tidak ada data",
			"sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
			"sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
			"sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
			"sInfoPostFix":  "",
			"sSearch":       "Cari : ",
			"sUrl":          "",
			"oPaginate": {
				"sFirst":    "&laquo;",
				"sPrevious": "&lsaquo;",
				"sNext":     "&rsaquo;",
				"sLast":     "&raquo;",
			}
		},
		"bSort": true,
		"bInfo": false,
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?=active_module_url($this->uri->segment(2));?>grid"
	});

	$('#btn_update').click(function() {
		if(mID) {
            if ($('#bphtb_nop').val()!=$('#pbb_nop').val()) {
                var c = confirm('Yakin akan dipecah ?');
                if(!c)
                    return;
            }
            $("#myform").attr("action", "<?echo active_module_url('bphtb_unposted/update');?>");
            $("#myform").submit();
		}else{
			alert('Silahkan pilih NOP terlebih dahulu!');
		}
	});
    
	$('#btn_pecah').click(function() {
		if(mID) {
            $("#myform").attr("action", "<?echo active_module_url('bphtb_unposted/pecah');?>");
            $("#myform").submit();
		}else{
			alert('Silahkan pilih NOP terlebih dahulu!');
		}
	});
    
	$('#btn_gabung').click(function() {
		if(mID) {
            $("#myform").attr("action", "<?echo active_module_url('bphtb_unposted/gabung');?>");
            $("#myform").submit();
		}else{
			alert('Silahkan pilih NOP terlebih dahulu!');
		}
	});
});
</script>

<div class="content">
    <div class="container-fluid"> 
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#main_grid"><strong>BPHTB Unposted</strong></a></li>
        </ul>
        <?=msg_block();?>
    
        <div class="row">
            <div class="span3" style="width:255px;">
                <table class="table display" id="table1">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>No</th>
                            <th>NOP</th>
                            <th><input type="checkbox" id="cek_selectall" /></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            
            
			<?php echo form_open("", array('id'=>'myform', 'enctype'=>'multipart/form-data', 'style'=>'margin: 0px;'));?> 	
            <input type="hidden" value="" name="bphtb_sspd_id" id="bphtb_sspd_id">
            <input type="hidden" value="" name="nop" id="nop">
            <input type="hidden" value="" name="sp_id" id="sp_id">
            <div class="span9">
                <div class="row"> 
                    <div class="span4" style="width:340px;">
                        <div class="page-header">
                            <strong><i class="icon-th-list"></i> Wajib Pajak [BPHTB]</strong>
                        </div>
                        <div class="form-horizontal">
                            <div class="control-group" id="bphtb_wp_nama">
                                <label class="control-label">Nama WP</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_nama" id="wp_nama">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_wp_npwp">
                                <label class="control-label">NPWP</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_npwp" id="wp_npwp">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_wp_alamat">
                                <label class="control-label">Alamat</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_alamat" id="wp_alamat">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_wp_blok_kav">
                                <label class="control-label">Blok/Kav</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_blok_kav" id="wp_blok_kav">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_wp_rt_rw">
                                <label class="control-label">RT/RW</label>
                                <div class="controls">
                                    <input type="text" class="input" style="width:20px;" value="" name="wp_rt" id="wp_rt"> / 
                                    <input type="text" class="input" style="width:20px;" value="" name="wp_rw" id="wp_rw">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_wp_kelurahan">
                                <label class="control-label">Kelurahan</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_kelurahan" id="wp_kelurahan">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_wp_kecamatan">
                                <label class="control-label">Kecamatan</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_kecamatan" id="wp_kecamatan">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_wp_kota">
                                <label class="control-label">Kabupaten/Kota</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_kota" id="wp_kota">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_wp_provinsi">
                                <label class="control-label">Propinsi</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_provinsi" id="wp_provinsi">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_wp_identitas">
                                <label class="control-label">No. Identitas</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_identitas" id="wp_identitas">
                                </div>
                            </div>
                        </div>
                        
                        <div class="page-header">
                            <strong><i class="icon-th-list"></i> Objek Pajak [BPHTB]<?echo !empty($data_source[0]['nop']) ? " - NOP : ".$data_source[0]['nop'] : "";?></strong>
                        </div>
                        <div class="form-horizontal">
                            <div class="control-group" id="bphtb_bphtb_nop">
                                <label class="control-label">BPHTB NOP</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="bphtb_nop" id="bphtb_nop">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_op_alamat">
                                <label class="control-label">Letak OP</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="op_alamat" id="op_alamat">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_op_blok_kav">
                                <label class="control-label">BLok/Kav</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="op_blok_kav" id="op_blok_kav">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_op_rt_rw">
                                <label class="control-label">RT/RW</label>
                                <div class="controls">
                                    <input type="text" class="input" style="width:20px;" value="" name="op_rt" id="op_rt"> / 
                                    <input type="text" class="input" style="width:20px;" value="" name="op_rw" id="op_rw">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_op_bumi">
                                <label class="control-label">Luas/NJOP Bumi</label>
                                <div class="controls">
                                    <input type="text" class="input" style="width:30px;" value="" name="bumi_luas" id="bumi_luas"> m2 / Rp.
                                    <input type="text" class="input" style="width:80px;" value="" name="bumi_njop" id="bumi_njop">
                                </div>
                            </div>
                            <div class="control-group" id="bphtb_op_bng">
                                <label class="control-label">Luas/NJOP Bng</label>
                                <div class="controls">
                                    <input type="text" class="input" style="width:30px;" value="" name="bng_luas" id="bng_luas"> m2 / Rp.
                                    <input type="text" class="input" style="width:80px;" value="" name="bng_njop" id="bng_njop">
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="span4" style="width:340px;">
                        <div class="page-header">
                            <strong><i class="icon-th-list"></i> Wajib Pajak [SISMIOP]</strong>
                        </div>
                        <div class="form-horizontal">
                            <div class="control-group" id="sismiop_wp_nama">
                                <label class="control-label">Nama WP</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_nama2" id="wp_nama2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_wp_npwp">
                                <label class="control-label">NPWP</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_npwp2" id="wp_npwp2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_wp_alamat">
                                <label class="control-label">Alamat</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_alamat2" id="wp_alamat2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_wp_blok_kav">
                                <label class="control-label">Blok/Kav</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_blok_kav2" id="wp_blok_kav2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_wp_rt_rw">
                                <label class="control-label">RT/RW</label>
                                <div class="controls">
                                    <input type="text" class="input" style="width:20px;" value="" name="wp_rt2" id="wp_rt2"> / 
                                    <input type="text" class="input" style="width:20px;" value="" name="wp_rw2" id="wp_rw2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_wp_kelurahan">
                                <label class="control-label">Kelurahan</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_kelurahan2" id="wp_kelurahan2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_wp_kecamatan">
                                <label class="control-label">Kecamatan</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_kecamatan2" id="wp_kecamatan2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_wp_kota">
                                <label class="control-label">Kabupaten/Kota</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_kota2" id="wp_kota2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_wp_provinsi">
                                <label class="control-label">Propinsi</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_provinsi2" id="wp_provinsi2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_wp_identitas">
                                <label class="control-label">Subjek Pajak ID</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="wp_identitas2" id="wp_identitas2">
                                </div>
                            </div>
                        </div>
                        
                        <div class="page-header">
                            <strong><i class="icon-th-list"></i> Objek Pajak [SISMIOP]<?echo !empty($data_source[0]['nop']) ? " - NOP : ".$data_source[0]['nop'] : "";?></strong>
                        </div>
                        <div class="form-horizontal">
                            <div class="control-group" id="sismiop_pbb_nop">
                                <label class="control-label">PBB NOP</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="pbb_nop" id="pbb_nop">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_op_alamat">
                                <label class="control-label">Letak OP</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="op_alamat2" id="op_alamat2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_op_blok_kav">
                                <label class="control-label">BLok/Kav</label>
                                <div class="controls">
                                    <input type="text" class="input" value="" name="op_blok_kav2" id="op_blok_kav2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_op_rt_rw">
                                <label class="control-label">RT/RW</label>
                                <div class="controls">
                                    <input type="text" class="input" style="width:20px;" value="" name="op_rt2" id="op_rt2"> / 
                                    <input type="text" class="input" style="width:20px;" value="" name="op_rw2" id="op_rw2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_op_bumi">
                                <label class="control-label">Luas/NJOP Bumi</label>
                                <div class="controls">
                                    <input type="text" class="input" style="width:30px;" value="" name="bumi_luas2" id="bumi_luas2"> m2 / Rp.
                                    <input type="text" class="input" style="width:80px;" value="" name="bumi_njop2" id="bumi_njop2">
                                </div>
                            </div>
                            <div class="control-group" id="sismiop_op_bng">
                                <label class="control-label">Luas/NJOP Bng</label>
                                <div class="controls">
                                    <input type="text" class="input" style="width:30px;" value="" name="bng_luas2" id="bng_luas2"> m2 / Rp.
                                    <input type="text" class="input" style="width:80px;" value="" name="bng_njop2" id="bng_njop2">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="span8">
                        <hr/>
                        <button id="btn_update" class="btn btn-primary" type="button">Proses</button>
                        <!--button id="btn_pecah" class="btn btn-primary" type="button">Pecah</button>
                        <button id="btn_gabung" class="btn btn-primary" type="button">Gabung</button-->
                    </div>
                </div>
                
            </div>
            <? echo form_close();?>
        </div>
        
	</div>
</div>

<? $this->load->view('_foot'); ?>