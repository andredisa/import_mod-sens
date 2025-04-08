<?php
function cleanString($string) {
    // Rimuovere i caratteri di controllo (es. \x00 - \x1F)
    return preg_replace('/[\x00-\x1F\x7F]/', '', $string);
}



?>