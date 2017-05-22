<?php

    // 連想配列
    $country = array('country_name' => 'japan', 'language' => 'japanese');
    // 二次元配列
    $region = array($country);
    // 三次元配列
    $earth = array($region);
    // 四次元配列
    $solar_system = array($earth);
    // 五次元配列
    $space = array($solar_system);

    echo $space[0][0][0][0]['country_name'];

?>