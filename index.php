<?php

require_once "controller.php";

$main = file_get_contents( "resources/app/html/main.html" );
$main = str_replace( "{STYLES}", file_get_contents( "resources/app/html/styles.html" ), $main );
$main = str_replace( "{HEADER}", file_get_contents( "resources/app/html/header.html" ), $main );
$main = str_replace( "{FOOTER}", file_get_contents( "resources/app/html/footer.html" ), $main );
$main = str_replace( "{SCRIPTS}", file_get_contents( "resources/app/html/scripts.html" ), $main );

$main = str_replace( "{CONTENT}", file_get_contents( "view/EventosView.php" ), $main );

echo $main;
