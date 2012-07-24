<?php

	require('gh_autoload.php');

	# папка где буду лежать библиотека
	define('LIBPATH', 'libs/');

	# подгружаемые библиотеки c github
	gh_autoload('akdelf', 'fAK', 'master', 'fAK.php'); # минималистический фреймворк fAK
	gh_autoload('akdelf', 'hottags', 'master', 'hottags.php'); # процедурный сборник html-хэлперов

	echo charset('UTF-8');