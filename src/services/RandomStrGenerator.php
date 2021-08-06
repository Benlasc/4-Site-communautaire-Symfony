<?php

namespace App\services;

class RandomStrGenerator
{
    public function generator($len_of_gen_str = 30)
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $var_size = strlen($chars);
        $res = "";
        for ($x = 0; $x < $len_of_gen_str; $x++) {
            $res .= $chars[rand(0, $var_size - 1)];
        }
        return $res;
    }
}
