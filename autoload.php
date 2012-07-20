<?php

	
	# папка где буду лежать библиотека
	define('LIBPATH', $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATION.'libs');

	
	# подгружаемые библиотеки c github
	gh_download('hottags', 'akdelf');
	gh_download('fAK', 'akdelf');