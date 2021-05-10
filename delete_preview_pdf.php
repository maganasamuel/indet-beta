<?php
    $myFile = "files/preview.pdf";
    unlink($myFile) or die("Couldn't delete file");
    echo "<script>window.close();</script>";
?>