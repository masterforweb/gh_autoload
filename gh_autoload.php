<?php


function gh_autoload($user, $repo, $branch = 'master', $sfile = null, $type = 'init') {

    $repo_dir = LIBPATH.$repo; //директория где будет установлен пакет

    if ($sfile !== null)
        $sfile = $repo_dir.'/'.$sfile;

    
    if ($type == 'init' and file_exists($sfile)) {
        if ($sfile !== null)
            require($sfile); // автозагрузка библиотеки
        return True;
    }

    $zipfile = 'http://github.com/'.$user.'/'.$repo.'/zipball/'.$branch; //получаем файл для занрузки
    $repo_dir = LIBPATH.$repo; //директория где будет установлен пакет
    $work_dir = $repo_dir.'_work'; // директория для распаковки
    $newfile = LIBPATH.$repo.'.zip';

    if ($type == 'init' and !file_exists($newfile)) { //защита от повторного скачивания

        if (!copy($zipfile, $newfile)) {
            gh_autoload_log('Не удалось скопировать ...'.$zipfile);
            return;
        }
        else 
           gh_autoload_log('Успешно скопирован ...'.$zipfile);
    }  


    if (!file_exists($newfile)) {
        gh_autoload_log('Не удалось закачать ...'.$zipfile);
        return False;
    }        
   
    $zip = new ZipArchive;
    $res = $zip->open($newfile);
    if ($res === TRUE) {
        $zip->extractTo($work_dir);
        $zip->close();
        gh_autoload_log('Успешно разархивирован ...'.$newfile);
    }
    else {
        gh_autoload_log('Неудалось разархивировать ...'.$newfile);
        return False;
    }    

    $files = scandir($work_dir);
    foreach ($files as $file) {
        if (strpos($file, $user.'-'.$repo) !== False) { // ищем папку с последним коммитом
            rename($work_dir.'/'.$file.'/', $repo_dir);
            gh_autoload_rm($work_dir);
            break;
        }    
            
    }

    unlink($newfile);

    if ($sfile !== null)
        require($sfile);
    
    gh_autoload_reg($user.'_'.$repo, $zipfile);

    return;
}




#очищаем директорию с файлаит
function gh_autoload_rm($dir) {
    
   
    if ($files = scandir($dir, 1)) {

           
        foreach($files as $file) {
         
          $file = trim($file);
        
          if($file === '.' || $file === '..') 
             continue; 

          $object = $dir.'/'.$file;

          if (is_dir($object))
            gh_autoload_rm($object);
          elseif(is_file($object))   
            unlink($object);
        }  

    }
    
    rmdir($dir);
}


# write log file
function gh_autoload_log($warning) {
    
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

#write registred files
function gh_autoload_reg($filereg, $filesource){

    $reg_fold = 'register';

    if (!is_dir($reg_fold)) 
        mkdir($reg_fold, 0775);

    $fp = fopen('register'.'/'.$filereg, 'w');
    fwrite($fp, $filesource);
    fclose($fp);

     return;

}