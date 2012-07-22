<?php

	
	# папка где буду лежать библиотека
	define('LIBPATH', 'libs/');

	
	# подгружаемые библиотеки c github
	gh_download('hottags', 'akdelf');
	//gh_download('fAK', 'akdelf');

	echo charset('UTF-8');