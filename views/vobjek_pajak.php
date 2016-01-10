<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>
<script>
$(document).ready(function() {
    var oTable = $('#datatable').dataTable( {
        "bJQueryUI" : true,
        "sPaginationType" : "full_numbers",
        "aoColumns" : [
            { sWidth: '6%' },
            null ,
            { sWidth: '8%' },
            { sWidth: '10%', sClass: "alignRight" },
            { sWidth: '8%', sClass: "alignRight" },
            { sWidth: '10%', sClass: "alignRight" },
            { sWidth: '10%', sClass: "alignRight" },
            { sWidth: '10%' },
        ] ,
        "aoColumnDefs": [
            { "bSearchable": false, "aTargets": [ 0 ], "bSortable": true, "aTargets": [ 0 ] },
            { "bSearchable": false, "aTargets": [ 1 ], "bSortable": true, "aTargets": [ 1 ] },
            { "bSearchable": false, "aTargets": [ 2 ], "bSortable": true, "aTargets": [ 2 ] },
            { "bSearchable": false, "aTargets": [ 3 ], "bSortable": true, "aTargets": [ 3 ] },
            { "bSearchable": false, "aTargets": [ 4 ], "bSortable": true, "aTargets": [ 4 ] }
        ],
        "sDom": '<"toolbar">rtip',
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
    });

    $("tfoot").removeClass();
    //$("#nop").mask("99.99-999.999-999.9999-9",{placeholder:"99.99-999.999-999.9999-9"});
  
    $('#nop').formatter({
        'pattern': '{{99}}.{{99}}.{{999}}.{{999}}.{{999}}-{{9999}}.{{9}}',
    });
});
</script>

<style>
.page-header {
    padding: 4px 10px;
    margin-top: 2px;
    margin-bottom: 4px;
}
</style>

<div class="content">
    <div class="container-fluid">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#op"><strong>Objek Pajak</strong></a></li>
        </ul>
        <?php echo form_open(active_module_url('objek_pajak'),array(
            'id' => 'myform', 
            'class' => 'form-horizontal',
            'method' => 'get',
            'style' => 'margin-bottom:2px;',
        ));?>
        <div class="control-group">
            <label class="control-label">Nomor Objek Pajak</label>
            <div class="controls">
                <input style="width:145px;" type="text" id="nop" class="input" value="<?=($nop != 0 ? $nop : '');?>" name="nop" autocomplete="off" required="required" placeholder="00.00.000.000.000-0000.0" size="30"/>
                <button class="btn btn-primary" type="submit">Cari</button>
                </select>
            </div>
        </div>
        </form>
        
        <?php if(!$data_source && !empty($nop)) { ?>
        <div style="margin-top:2px; margin-bottom:2px;" id="msg_helper" class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>Data tidak ditemukan !</div>
        <?php } ?>
        
        <hr>
        <div class="row">
            <div class="span6">
                <div class="page-header">
                    <strong>Objek Pajak</strong>
                </div>
                <div class="form-horizontal">
                    <div class="control-group">
                        <label class="control-label">N O P</label>
                        <div class="controls">
                            <label class="input">: <?=$data_source[0]['nop'];?></label>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Alamat OP</label>
                        <div class="controls">
                            <label class="input">: <?=$data_source[0]['alamat_op'];?></label>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">RT/RW OP</label>
                        <div class="controls">
                            <label class="input">: <?=$data_source[0]['rt_rw_op'];?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="span6">
                <div class="page-header">
                    <strong>Subjek Pajak</strong>
                </div>
                <div class="form-horizontal">
                    <div class="control-group">
                        <label class="control-label">Nama WP</label>
                        <div class="controls">
                            <label class="input">: <?=$data_source[0]['nm_wp'];?></label>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Alamat WP</label>
                        <div class="controls">
                            <label class="input">: <?=$data_source[0]['alamat_wp'];?> RT/RW: <?=$data_source[0]['rt_rw_wp'];?></label>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Kelurahan WP</label>
                        <div class="controls">
                            <label class="input">: <?=$data_source[0]['alamat_wp'];?> Kota WP : <?=$data_source[0]['kota_wp'];?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <hr>
        <div class="page-header">
            <strong>SPPT</strong>
        </div>
        <table class="" id="datatable">
            <thead>
                <tr>
                    <th>Tahun</th>
                    <th>Nama WP.</th>
                    <th>Luas Tanah</th>
                    <th>NJOP Tanah</th>
                    <th>Luas Bng</th>
                    <th>NJOP Bng</th>
                    <th>Ketetapan</th>
                    <th>Status Bayar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if($data_source && count($data_source)) {
                    foreach($data_source as $val) {
                    ?>
                <tr>
                    <td><?=$val['thn_pajak_sppt'];?></td>
                    <td><?=$val['nm_wp'];?></td>
                    <td align="right"><?=number_format ($val['luas_tanah'], 0 ,  ',' , '.' );?></td>
                    <td align="right"><?=number_format ($val['njop_tanah'], 0 ,  ',' , '.' );?></td>
                    <td align="right"><?=number_format ($val['luas_bng'], 0 ,  ',' , '.' );?></td>
                    <td align="right"><?=number_format ($val['njop_bng'], 0 ,  ',' , '.' );?></td>
                    <td align="right"><?=number_format ($val['ketetapan'], 0 ,  ',' , '.' );?></td>
                    <td align="center"><? echo $val['status_bayar']==0 ? "Belum" : "Sudah";?></td>
                </tr>
                <?php
                    }
                    }
                    ?>
            </tbody>
        </table>
    </div>
</div>
<? $this->load->view('_foot'); ?>