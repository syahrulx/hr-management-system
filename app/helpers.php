<?php
if (!function_exists('isAdmin')) {
    function isAdmin(){
        return (auth()->user()->userRole ?? null) === 'admin';
    }
}
if (!function_exists('authenticateIfNotAdmin')) {
    function authenticateIfNotAdmin($userID, $targetID){
        if (((auth()->user()->userRole ?? null) !== 'admin') && ($userID != $targetID)){
            return abort(401, 'You are not authorized to perform this action.');
        }
    }
}

/**
 * Arabic Specific function. Remove it if you don't need it.
 */
if (!function_exists('NormalizeArabic')) {
    function NormalizeArabic($string) {
        $string = str_replace("أ", "ا", $string);
        $string = str_replace("آ", "ا", $string);
        $string = str_replace("إ", "ا", $string);
        $string = str_replace("ة", "ه", $string);
        $string = str_replace("ي", "ى", $string);
        return str_replace("ؤ", "و", $string);
    }
}
