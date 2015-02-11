<?php


function getErrorClass($fieldError,&$hasError){
    if($hasError === true){
        return "";
    }else{
        if($fieldError != ""){
            $hasError = true;
            return "error_field_to_focused";
        }
    }
}

