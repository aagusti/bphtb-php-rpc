<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?=APP_TITLE?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="sistem informasi keuangan daerah">
	<meta name="author" content="irul">

	<!-- Fav and touch icons -->
	<link rel="shortcut icon" href="<?=base_url()?>assets/img/favicon.ico">

	<!-- Le styles -->
	<link href="<?=base_url()?>assets/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="<?=base_url()?>assets/css/font-static.css" rel="stylesheet">
	<style>
		@media print { 
			.navbar { display: none; }
		}
		body {
			margin: 0; padding: 0; height: 100%; overflow: hidden;
		}
		#rcontent
		{
			position:absolute; left: 0; right: 0; bottom: 0; top: 0px; background: #E5E5E5; height: expression(document.body.clientHeight-40);
		}
	</style>
	
	<script src="<?=base_url()?>assets/jq/js/jquery-1.8.2.min.js"></script>
	<script>
		$(document).ready(function() {	
			$('#rcontent').append('<iframe id="rpt_ifrm" name="rpt_ifrm" width="100%" height="100%" frameborder="0" src="<?=$rpt_html;?>" ><p>Your browser does not support iframes.</p></iframe>');
			$('iframe#rpt_ifrm').load(function() {
				$('#loading').hide();
				var kosong = '<td width="50%">&nbsp;</td><td align="center"></td><td width="50%">&nbsp;</td>';
				if($.trim($("iframe").contents().find('table tr').html().replace(/(\r\n|\n|\r)/gm, '')) == kosong)
					$('#loading').html('<center><strong><p>Tidak ada data untuk ditampilkan...</p></strong></center>').show();
			});
		});
		function pdf()   { 
			window.location = "<?=$rpt_pdf;?>"; 
		}
		function cetak() { 
			/* window.print(); */ 
			window.frames["rpt_ifrm"].focus();
			window.frames["rpt_ifrm"].print();
		}
		function tutup() { window.close(); }
	</script>
</head>
<body>
	<div id="rcontent">
		<div id="loading" style="padding:10px;">
			<center><img border='0' src='<?=base_url('assets/pad/img/ajax-loader-bert.gif')?>' /></center>
		</div>
	</div>
</body>
</html>