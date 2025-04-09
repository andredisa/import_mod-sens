<?php
function cleanString($string) {
    // Rimuove caratteri invisibili e spazi Unicode strani
    $string = preg_replace('/[\x00-\x1F\x7F\xA0\x{200B}-\x{200D}\x{FEFF}]/u', '', $string); // invisibili e non-breaking
    $string = preg_replace('/\s+/', ' ', $string); // normalizza spazi
    return trim($string);
}
?>