<?php
$arr = array(0,1,2,3);
$sum = 0;
//obviously running into array index out of bounds
for($i = 0; $i < 10; $i++){
    $sum += $arr[$i];
}