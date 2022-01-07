<?php
class Helios_Allowfile_Model_Core_File_Validator_Image extends Mage_Core_Model_File_Validator_Image
{	
	/**
     * Validation callback for checking is file is image
     *
     * @param  string $filePath Path to temporary uploaded file
     * @return null
     * @throws Mage_Core_Exception
     */
	public function validate($filePath)
    {	
        list($imageWidth, $imageHeight, $fileType) = getimagesize($filePath);
        if ($fileType) {
            if ($this->isImageType($fileType)) {
                //replace tmp image with re-sampled copy to exclude images with malicious data
                $image = imagecreatefromstring(file_get_contents($filePath));
                if ($image !== false) {
                    $img = imagecreatetruecolor($imageWidth, $imageHeight);
                    imagealphablending($img, false);
                    imagecopyresampled($img, $image, 0, 0, 0, 0, $imageWidth, $imageHeight, $imageWidth, $imageHeight);
                    imagesavealpha($img, true);

                    switch ($fileType) {
                        case IMAGETYPE_GIF:
                            $transparencyIndex = imagecolortransparent($image);
                            if ($transparencyIndex >= 0) {
                                imagecolortransparent($img, $transparencyIndex);
                                for ($y = 0; $y < $imageHeight; ++$y) {
                                    for ($x = 0; $x < $imageWidth; ++$x) {
                                        if (((imagecolorat($img, $x, $y) >> 24) & 0x7F)) {
                                            imagesetpixel($img, $x, $y, $transparencyIndex);
                                        }
                                    }
                                }
                            }
                            if (!imageistruecolor($image)) {
                                imagetruecolortopalette($img, false, imagecolorstotal($image));
                            }
                            imagegif($img, $filePath);
                            break;
                        case IMAGETYPE_JPEG:
                            imagejpeg($img, $filePath, 100);
                            break;
                        case IMAGETYPE_PNG:
                            imagepng($img, $filePath);
                            break;
                        default:
                            break;
                    }

                    imagedestroy($img);
                    imagedestroy($image);
                    return null;
                } else {
                    throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid image.'));
                }
            }
        } else {
			if (mime_content_type($filePath)=='application/pdf') {
				$fileType = IMAGETYPE_PNG;          
				return null;
			}
		}		
        throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid MIME type.'));
    }
}
		