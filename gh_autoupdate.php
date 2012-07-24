#!/usr/local/bin/php -q
<?php

	require('gh_autoload.php');

	# обновление конкретного пакета
	if (!isset($argv)) {
		$user = $argv[0];
		$repo = $argv[1];
		if (isset($argv[2]))
			$branch = $argv[2];
		else
			$branch = 'master';

		gh_autoload($user, $repo, $branch, $sfile = null, $type = 'update');

		exit;
	}


	# обновление всех пакетов из папки register



		