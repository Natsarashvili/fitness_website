<?php


require_once 'includes/functions.php';

session_start();
session_unset();
session_destroy();


show_message('წარმატებით გახვედით სისტემიდან', 'success');


redirect('index.php');
?>