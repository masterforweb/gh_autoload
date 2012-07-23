<?php

	
	require('gh_autoload.php');

	# папка где буду лежать библиотека
	define('LIBPATH', 'libs/');

	
	# подгружаемые библиотеки c github
	#gh_download('akdelf', 'hottags');
	gh_autoload('akdelf', 'fAK');



	echo charset('UTF-8');