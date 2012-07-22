<?php

function charset($code){

	echo '<meta http-equiv="Content-Type" content="text/html; charset='.$code.'" />';
}

function js($code) {
	echo '<script type="text/javascript" src="'.$code.'"></script>';		
}

function css($code) {
	echo '<style>'.$code.'</style>';
}

function icon($file) {
	echo '<link rel="icon" type="image/png" href="'.$file.'" />';
}

function feed($file) {

	echo '<link title="argumenti_ru" type="application/rss+xml" rel="alternate" href="'.$file.'"/>';
}

function title($var){

}	

function discript($var) {

}

function keywords($keys) {

}

function a($link, $title) {
	echo '<a href="'.$link.'">'.$title.'</a>';
}

function li_a($title, $link) {
	echo '<li><a href="'.$link.'">'.$title.'</a></li>';
}