<? 


	/*
		$source - источник фотографии
		$w - ширина фотографии
		$h - высота фотографии
		$type - тип изменения

	*/


	function fakThumb($source, $w = '', $h = '', $type = 'crop') {
		
		
		$fsource = IMGPATH.$source; //полное имя файла

		$http = strpos(IMGPATH, 'http'); //внешняя ссылка или текущий каталог
	
		if ($http === false) {

			if (!file_exists($fsource)) // проверка существования файла
				return null;	
		}


		$info = pathinfo($source);
		$mkdir = IMGCACHE.str_replace(IMGPATH, '', $info['dirname']).DIRECTORY_SEPARATOR; //вычисляем папочку
		$newfile = $mkdir.$info['filename'].'_'.$w.'_'.$h.'_'.$type.'.'.$info['extension']; // новое имя файла
		
		$link = str_replace($_SERVER['DOCUMENT_ROOT'], 'http://'.$_SERVER['HTTP_HOST'], $newfile);	
		
		
		if ($http !== False and file_exists($newfile))
			return $link; 
		elseif (file_exists($newfile) and filectime($newfile) > filectime($fsource))
				return $link; 
			

		if (!is_dir($mkdir)) { // создаем директорию
			if (!mkdir($mkdir, 0775, True)) 
				return;
		}	
		

		if (class_exists('Imagick')) {
			if (fak_timagick($fsource, $newfile, $w, $h, $type))
				return $link;
		}	
		elseif (fak_tgd($fsource, $newfile, $w, $h, $type))
				return $link;
		
		return '';
	
	}

	

	
	// ресайз в gd
	function fak_tgd($fsource, $newfile, $w, $h, $type = 'crop', $q = 80) {

		$src = imagecreatefromjpeg($fsource); 
		$w_src = imagesx($src); 
		$h_src = imagesy($src);

		if ($w_src == $w && $h_src = $h) 
			copy($fsource, $newfile);
		else {	
			if ($w_src != $w) {
				if ($type == 'crop'){ 								 
					
					$dest = imagecreatetruecolor($w,$w); 
					if ($w_src > $h_src) 
						imagecopyresampled($dest, $src, 0, 0,
						round((max($w_src,$h_src)-min($w_src,$h_src))/2),
                          0, $w, $w, min($w_src,$h_src), min($w_src,$h_src)); 
					if ($w_src < $h_src) 
						imagecopyresampled($dest, $src, 0, 0, 0, 0, $w, $w,
					min($w_src,$h_src), min($w_src,$h_src)); 
					 
					if ($w_src == $h_src) 
						imagecopyresampled($dest, $src, 0, 0, 0, 0, $w, $w, $w_src, $w_src); 

				}
   				 
				else { 
										
					$ratio = $w_src / $w; 
					$w_dest = round($w_src / $ratio); 
					$h_dest = round($h_src / $ratio); 
					$dest = imagecreatetruecolor($w_dest, $h_dest); 
					imagecopyresampled($dest, $src, 0, 0, 0, 0, $w_dest, $h_dest, $w_src, $h_src); 

				} 
			}

			if (imagejpeg($dest, $newfile, $q))
				return True;

		}	

	}


	//ресайз в Imagick
	function fak_timagick ($fsource, $newfile, $w, $h, $type) {
		
		$im = new Imagick($fsource);

		/*try {
			if ($im->readImage($fsource) == False)
			return '';
		} 
		catch (Exception $e) {
			return '';
		}*/

		
		switch ($type){
			case 'fit':
				$im->thumbnailImage($w, $h, true);
				break;
			case 'fixed':	
				$im->thumbnailImage($w, $h);
				break;
			case 'cropped':	
			case 'crop':	
				$im->cropThumbnailImage($w, $h);
				break;
			case 'proportion':	
				$m_width = (float) $w;
				$m_height = (float) $h;
				$curr_width = $im->getImageWidth();
				$curr_height = $im->getImageHeight();
				if (($m_width < $curr_width ) or ($m_height < $curr_height)){
					$w_k = $curr_width/$m_width;
					$h_k = $curr_height/$m_height;
					if ($w_k > $h_k){
						$new_width = $m_width;
						$new_height = $curr_height/$w_k;
					}
					else {
						$new_width = $curr_width/$h_k;
						$new_height = $m_height;
					}
					$im->resizeImage($new_width, $new_height, imagick::FILTER_LANCZOS, 1); 
				}
				break;
			default:	
				$h = null;
				$im->thumbnailImage($w, $h);
		
		}

		if ($im->writeImage($newfile))
			return True;


		
	}