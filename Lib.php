<?php
 
namespace Edev\System\Helpers\Lib; 


function calc($val1, $operator, $val2)
{
    return calcOp($val1, $operator, $val2);
}


function calcOp($val1, $operator, $val2){
    switch($operator){
        case '==':
            return $val1 == $val2;
        case '!=':
            return $val1 != $val2;
        case '<':
            return $val1 < $val2;
        case '<=':
            return $val1 <= $val2;
        case '>':
            return $val1 > $val2;
        case '>=':
            return $val1 >= $val2;
    }
}
