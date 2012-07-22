<?php

	
	require('gh_download.php');

	# папка где буду лежать библиотека
	define('LIBPATH', 'libs/');

	
	# подгружаемые библиотеки c github
	#gh_download('akdelf', 'hottags');
	gh_download('akdelf', 'fAK');



	echo charset('UTF-8');