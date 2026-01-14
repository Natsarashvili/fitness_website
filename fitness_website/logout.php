<?php
/**
 * გასვლის გვერდი
 * 
 * სესიის განადგურება და გადამისამართება
 */

require_once 'includes/functions.php';

// სესიის განადგურება
session_start();
session_unset();
session_destroy();

// შეტყობინება
// (არ გამოჩნდება რადგან session წაიშალა, მაგრამ კოდის სისუფთავისთვის)
show_message('წარმატებით გახვედით სისტემიდან', 'success');

// გადამისამართება მთავარ გვერდზე
redirect('index.php');
?>