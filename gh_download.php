#!/usr/local/bin/php -q

<?php

include('autoload.php');


if (isset($argv[0]))
    define('MODE', $argv[0]);
else
    define('MODE', 'init');



function gh_download($repo, $user) {


    $file = 'http://github.com/'.$user.'/'.$repo.'/zipball/master';
    $work_dir = $repo.'_work';
    $newfile = LIBPATH.$repo.'.zip';

    if (MODE == 'init' and is_dir($work_dir)) //файл скачен
        return;
    
    if (!copy($file, $newfile)) {
        echo "не удалось скопировать $file...\n";
        return;
    }else {
        echo $file." импортирован... \n";
    }    

    $work_dir = $repo.'_work';
    $zip = new ZipArchive;
    $res = $zip->open($newfile);
    if ($res === TRUE) {
        $zip->extractTo($work_dir);
        $zip->close();
        echo $newfile." успешно разархивирован... \n"; 
        $files = scandir($work_dir);
        foreach ($files as $file) {
            if (strpos($file, $user.'-'.$repo) !== False) {
                rename($work_dir.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR, $repo);
                removedir($work_dir);
                break;
            }    
        }        
    }

    unlink($newfile);

    return;
}
 

function removedir($dir) {
    
    if ($files = glob($dir.'/*')) {
       
       print_r($files);
       foreach($files as $file) {
         
         if (is_dir($file))
            removedir($file);
         elseif(is_file($file))   
             unlink($file);

       }

    }
    rmdir($dir);
}