<?php

echo $_POST[target].'|'.$_POST[field].'|'.stripslashes($_POST[value]).'|<div style="display:none"><img src="'.stripslashes($_POST[value]).'"></div>';

?>