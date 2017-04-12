<?php 
define('IMAGEPATH', 'sources/');
define('FINAL_WIDTH', 10000);
define('FINAL_HEIGHT', 800);

foreach(glob(IMAGEPATH.'*') as $filename) { $images[] =  basename($filename); }

$filesCount = count($images) * 2 - 1; // *2 because of the alpha blending, -1 because of final column
$lineWidth = round(FINAL_WIDTH / $filesCount); // auto width
$canvas = imagecreatetruecolor(FINAL_WIDTH,FINAL_HEIGHT); // Create canvas

$x = 0; // Loop files 

foreach ($images as $image) {

    $size = GetImageSize(IMAGEPATH.$image);
    $originalWidth = $size[0];
    $originalHeight = $size[1];
    $originalType = $size[2];
    $opacity = 0.5;

    if ($originalType==1) $image_orig=imagecreatefromgif(IMAGEPATH.$image);
    if ($originalType==2) $image_orig=imagecreatefromjpeg(IMAGEPATH.$image);
    if ($originalType==3) $image_orig=imagecreatefrompng(IMAGEPATH.$image);

    // For each pic, get average color by line : set to 1px width, then expand to $lineWidth
    $pixel = imagecreatetruecolor(1,$originalHeight);
    imagecopyresampled($pixel, $image_orig, 0, 0, 0, 0, 1, $originalHeight, $originalWidth, $originalHeight); // Resize original pic to 1px
    $column = imagecreatetruecolor($lineWidth,$size[1]);    
    imagecopyresampled($column, $pixel, 0, 0, 0, 0, $lineWidth, $originalHeight, 1, $originalHeight); // Zoom pixel to final column width
    imagecopyresampled ($canvas , $column, $x , 0 , 0 , 0 , $lineWidth , FINAL_HEIGHT , $lineWidth , $originalHeight ); // Paste to canvas

    // Draw the after and before transition pic
    $x_after = $x + $lineWidth;    
    $x_before = $x - $lineWidth;    

    imagecopyresampled ( $canvas , $column, $x_after , 0 , 0 , 0 , $lineWidth , FINAL_HEIGHT , $lineWidth , $originalHeight ); // Paste 100% alpha to after
    
    // Paste half transparent pic before for smoother transitions
    imagealphablending($column, false);
    imagesavealpha($column, true);
    imagefilter($column, IMG_FILTER_COLORIZE, 0, 0, 0, 127*$opacity);
    imagecopyresampled ( $canvas , $column, $x_before , 0 , 0 , 0 , $lineWidth , FINAL_HEIGHT , $lineWidth , $originalHeight ); // Paste 50% alpha over before

    $x = $x + ($lineWidth*2); // Jump 1 step
}

imagepng($canvas, "timelapscape.png"); // Pop final picture
echo '<img src="timelapscape.png" alt="w00t"><br />'.$filesCount.' files -> '.$lineWidth.' px for each column<br />Done !';
?>
