<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include_once(dirname(__FILE__).'/blocktestimonial.php');

		$blockTestimonial = new blockTestimonial();
		echo $blockTestimonial->displayTestimonials();

include_once(dirname(__FILE__).'/../../footer.php');


?>