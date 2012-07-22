#!/usr/local/bin/php -q

<?php

include('autoload.php');


if (isset($argv[0]))
    define('MODE', $argv[0]);
else
    define('MODE', 'init');





function gh_download($user, $repo, $sfile = null, $branch = 'master') {

    $repo_dir = LIBPATH.$repo; //директория где будет установлен пакет
    
    if ($sfile == null)
        $sfile = $repo.'.php';    
      
    $sfile = $repo_dir.'/'.$sfile;

    if (MODE !== 'update' and file_exists($sfile)) {
        require($sfile); // автозагрузка библиотеки
        return True;
    }

    $file = 'http://github.com/'.$user.'/'.$repo.'/zipball/'.$branch; //получаем файл для занрузки
    $repo_dir = LIBPATH.$repo; //директория где будет установлен пакет
    $work_dir = $repo_dir.'_work'; // директория для распаковки
    $newfile = LIBPATH.$repo.'.zip';

    if (MODE == 'init' and is_dir($work_dir)) //файл уже скачен
        return;


    if (!copy($file, $newfile)) {
        echo "не удалось скопировать $file...\n";
        return;
    }else {
        echo $file." импортирован... \n";
    }    
   
    $zip = new ZipArchive;
    $res = $zip->open($newfile);
    if ($res === TRUE) {
        $zip->extractTo($work_dir);
        $zip->close();
        echo $newfile." успешно разархивирован... \n"; 
        $files = scandir($work_dir);
        foreach ($files as $file) {
            if (strpos($file, $user.'-'.$repo) !== False) { // ищем папку с последним коммитом
                rename($work_dir.'/'.$file.'/', $repo_dir);
                removedir($work_dir);
                break;
            }    
        }        
    }

    unlink($newfile);

    require($sfile);
    

    return;
}




function removedir($dir) {
    
    if ($files = glob($dir.'/*')) {
       
       foreach($files as $file) {
         
         if (is_dir($file))
            removedir($file);
         elseif(is_file($file))   
             unlink($file);

       }

    }
    rmdir($dir);
}