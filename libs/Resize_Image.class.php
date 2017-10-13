<?php
/* 
------------------------------------------------------------------------------------
Credits: Bit Repository

Source URL: http://www.bitrepository.com/web-programming/php/resizing-an-image.html
------------------------------------------------------------------------------------
*/
class Resize_Image {

		var $image_to_resize;
		var $new_width;
		var $new_height;
		var $new_image_name;
		var $save_folder;
		var $watermark;
		var $watermark_top_margin=0;
		var $watermark_left_margin=0;
		var $crop;
		var $grayscale;
		
		function resize() {
			if(!file_exists($this->image_to_resize)) exit("File ".$this->image_to_resize." does not exist.");	// does the file exist
			
			// kenCode
			$original_width = $this->new_width;
			$original_height = $this->new_height; // used for cropping
			//kenCode

			$info = GetImageSize($this->image_to_resize);		// get image information

			if(empty($info)) exit("The file ".$this->image_to_resize." doesn't seem to be an image.");		// if we can't get the dimensions quit

			// get image info
			$width = $info[0];
			$height = $info[1];
			$mime = $info['mime'];

			// only new width or new height
			// is used in the computation. if both
			// are set, use width
			if (isset($this->new_width)) {
				if (isset($this->new_height)&&($this->new_width > $this->new_height)) {
					$factor = (float)$this->new_height / (float)$height;
					$this->new_width = $factor * $width;
				} else {
					$factor = (float)$this->new_width / (float)$width;
					$this->new_height = $factor * $height;
				}
			} elseif (isset($this->new_height)) {
				$factor = (float)$this->new_height / (float)$height;
				$this->new_width = $factor * $width;
			} else {
				exit('please set new_width or new_height');
			}

			// image type
			$type = substr(strrchr($mime, '/'), 1);

			switch ($type)  {
				case 'jpeg':
					$image_create_func = 'ImageCreateFromJPEG';
					break;
				case 'png':
					$image_create_func = 'ImageCreateFromPNG';
					break;
				case 'bmp':
					$image_create_func = 'ImageCreateFromBMP';
					break;
				case 'gif':
					$image_create_func = 'ImageCreateFromGIF';
					break;
				case 'vnd':
				case 'wap':
				case 'wbmp':
					$image_create_func = 'ImageCreateFromWBMP';
					break;
				case 'xbm':
					$image_create_func = 'ImageCreateFromXBM';
					break;
				default: 
					$image_create_func = 'ImageCreateFromJPEG';
			}

			// New Image
			$new_image = $image_create_func($this->image_to_resize);
			
			//kenCode
			if($this->crop) {
				//getting the image dimensions 
				    $ratio_orig = $width/$height;
				   
				    if ($original_width/$original_height > $ratio_orig) {
				       $nheight = $original_width/$ratio_orig;
				       $nwidth = $original_width;
				    } else {
				       $nwidth = $original_height*$ratio_orig;
				       $nheight = $original_height;
				    }
				   
				    $x_mid = $nwidth/2;  //horizontal middle
				    $y_mid = $nheight/2; //vertical middle
				   
				    $process = imagecreatetruecolor(round($nwidth), round($nheight));
				   
				    imagecopyresampled($process, $new_image, 0, 0, 0, 0, $nwidth, $nheight, $width, $height);

				    $image_c = imagecreatetruecolor($original_width, $original_height);
				    imagecopyresampled($image_c, $process, 0, 0, ($x_mid-($original_width/2)), ($y_mid-($original_height/2)), $original_width, $original_height, $original_width, $original_height);
				
			} else {
					$image_c = imagecreatetruecolor($this->new_width, $this->new_height);
					imagecopyresampled($image_c, $new_image, 0, 0, 0, 0, $this->new_width, $this->new_height, $width, $height);
			}
			// kenCode
			
			
			if($this->save_folder) {
				$new_name = $this->new_image_name ? $this->new_image_name.'.jpg' : $this->new_thumb_name(basename($this->image_to_resize)).'.jpg';
				$save_path = $this->save_folder.$new_name;
			} else {
				/* Show the image without saving it to a folder */
			   header("Content-Type: ".$mime);
			   ImageJPEG($image_c);
			   $save_path = '';
			}
			// kenCode
			if($this->watermark) {
				$mark = imagecreatefrompng($this->watermark);
				list($mwidth, $mheight) = getimagesize($this->watermark);
				imagecopy($image_c,$mark, $this->watermark_left_margin, $this->watermark_top_margin ,0, 0, $mwidth, $mheight);
			}
			// kenCode
				$process = ImageJPEG($image_c, $save_path,100);
				return array('result' => $process, 'new_file_path' => $save_path);
			}

			function new_thumb_name($filename)	{
				$fparts = explode('.',$filename);
				$ext = $fparts[count($fparts)-1];
				return $fparts[0];
			}
}
?>