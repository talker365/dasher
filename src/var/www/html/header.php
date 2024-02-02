<!-- Top container -->
<div class="w3-bar w3-top w3-black w3-small" style="z-index:4">
	<button class="w3-bar-item w3-button w3-hide-large w3-hover-none w3-hover-text-light-grey" onclick="w3_open();"><i class="fa fa-bars"></i>  Menu</button>
	<span class="w3-bar-item w3-left">
		<!-- Hostname -->
		<?php
			echo $_SERVER['SERVER_NAME']; 
		?>
	</span>
	<span class="w3-bar-item w3-right">
		<!-- Date -->
		<?php
			echo date("d")."-".date("M")."-".date("Y"); 
		?>
	</span>
</div>