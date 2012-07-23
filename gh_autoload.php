#!/usr/local/bin/php -q

<?php


if (isset($argv[0]))
    define('GH_DOWN_MODE', $argv[0]);
else
    define('GH_DOWN_MODE', 'init');




function gh_download($user, $repo, $sfile = null, $branch = 'master', $type = 'init') {

    $repo_dir = LIBPATH.$repo; //директория где будет установлен пакет
    
    if ($sfile == null)
        $sfile = $repo.'.php';    
      
    $sfile = $repo_dir.'/'.$sfile;

    if ($type !== 'update' and file_exists($sfile)) {
        require($sfile); // автозагрузка библиотеки
        return True;
    }

    $zipfile = 'http://github.com/'.$user.'/'.$repo.'/zipball/'.$branch; //получаем файл для занрузки
    $repo_dir = LIBPATH.$repo; //директория где будет установлен пакет
    $work_dir = $repo_dir.'_work'; // директория для распаковки
    $newfile = LIBPATH.$repo.'.zip';

    if ($type == 'init' and !file_exists($newfile)) { //защита от повторного скачивания

        if (!copy($zipfile, $newfile)) {
            gh_down_log('Не удалось скопировать '.$zipfile);
            return;
        }
        else 
           gh_down_log('Успешно скопирован '.$zipfile);    
   
        $zip = new ZipArchive;
        $res = $zip->open($newfile);
        if ($res === TRUE) {
            $zip->extractTo($work_dir);
            $zip->close();
            gh_down_log('Успешно разархивирован '.$newfile);
        }
        else {
            gh_down_log('Неудалось разархивировать '.$newfile);
            return False;
        }    

        $files = scandir($work_dir);
        foreach ($files as $file) {
            if (strpos($file, $user.'-'.$repo) !== False) { // ищем папку с последним коммитом
                rename($work_dir.'/'.$file.'/', $repo_dir);
                gh_down_removedir($work_dir);
                break;
            }    
        }        
    }

    unlink($newfile);

    require($sfile);
    
    gh_down_reg($user.'_'.$repo, $zipfile);

    return;
}




#очищаем директорию с файлаит
function gh_down_removedir($dir) {
    
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


# пишем в лог файл
function gh_down_log($warning) {
    
    $log = 'gh_down.log';
    
    if (file_exists($log))
        $fp = fopen($log,'a+');
    else {
        $fp = fopen($log,'w');
        chmod($log, 0660);
    }

    fwrite($fp, '['.date('d.m.y\ H:i:s').'] '.$warning.chr(13));
    fclose($fp);

    return;

}

#пишем регистрационный файл
function gh_down_reg($filereg, $filesource){

     $fp = fopen('register/'.$filereg, 'w');
     fwrite($fp, $filesource);
     fclose($fp);

     return;

}