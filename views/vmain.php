<? $this->load->view('_head'); ?>
<? $this->load->view(active_module().'/_navbar'); ?>
<script>
function get_realisasi() {
	$.ajax({
		url: "<?php echo active_module_url($this->uri->segment(2))?>get_realisasi",
		async: true,
		success: function (j) {
			var data = $.parseJSON(j);
			$('#amt_daily').html('Rp. '+data['amt_daily']);
			$('#amt_weekly').html('Rp. '+data['amt_weekly']);
			$('#amt_monthly').html('Rp. '+data['amt_monthly']);
			$('#amt_yearly').html('Rp. '+data['amt_yearly']);
		},
		error: function (xhr, desc, er) {
			alert(er);
		}
	});
}

$(document).ready(function() {
    get_realisasi();
});
</script>

<div class="content">
	<div class="container-fluid">
    <?=msg_block();?>
		<div class="well">            
      <center>
          <div class="row">
              <div class="span12">
                  <div class="row">
                      <div class="span2">
                          &nbsp;
                      </div>
                      
                      <div class="span8">
                          <center>
                              <h3>PENERIMAAN PEMBAYARAN</h3>
                              <h3>BEA PEROLEHAN HAK ATAS TANAH DAN BANGUNAN</h3>
                              <h3>TAHUN <?=date('Y');?></h3>
                          </center>
                          
                          <div class="row">
                              <div class="span4">
                                  <div class="alert alert-success">
                                      <h4><u>Hari ini</u></h4>
                                      <h2 id="amt_daily"><span class="label label-success">sedang menghitung...</span></h2>
                                  </div>				
                              </div>
                              <div class="span4">
                                  <div class="alert alert-info">
                                      <h4><u>Minggu ini</u></h4>
                                      <h2 id="amt_weekly"><span class="label label-info">sedang menghitung...</span></h2>
                                  </div>
                              </div>
                          </div>
                          <div class="row">
                              <div class="span4">
                                  <div class="alert alert-warning">
                                      <h4><u>Bulan ini</u></h4>
                                      <h2 id="amt_monthly"><span class="label label-warning">sedang menghitung...</span></h2>
                                  </div>				
                              </div>
                              <div class="span4">
                                  <div class="alert alert-error">
                                      <h4><u>Tahun ini</u></h4>
                                      <h2 id="amt_yearly"><span class="label label-error">sedang menghitung...</span></h2>
                                  </div>
                              </div>
                          </div>
                      </div>
                      
                  </div>
              </div>
          </div>
      </center>
		</div>
        
		<!--div class="hero-unit">
		  <center>
  			<h2>PEMERINTAH <?=LICENSE_TO?></h2>
  			<h3><?=LICENSE_TO_SUB?></h3>
  			<img src="<?=app_img_logo('assets/img/img_logo.png')?>" alt="logo" style="max-height:250px;">
  			<h2>Module BPHTB</h2>  			
  			<P>Module BPHTB merupakan bagian dari module Aplikai PBB BPHTB P2</P>
  			<P>Module ini digunakan untuk memproses validasi data BPHTB</P>
  			<P>SELAMAT BEKERJA</P>
			</center>
		</div-->
		 
	</div>
</div>
<? $this->load->view('_foot'); ?>