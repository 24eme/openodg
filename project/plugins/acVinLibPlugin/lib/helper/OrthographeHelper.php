<?php

function elision($motPrec,$chaine) 
{
    $c = in_array(strtolower($chaine[0]),array('a','e','i','o','u','h'));
    
    $mp = in_array(strtolower($motPrec[strlen($motPrec)-1]), array('a','e','i','o','u','h'));
    if($c && $mp){
        $motPrec[strlen($motPrec)-1] = "'";
        return $motPrec.$chaine;
    }
    return $motPrec.' '.$chaine;
}