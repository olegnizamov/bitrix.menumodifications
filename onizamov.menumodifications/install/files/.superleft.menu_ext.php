<?php

use Bitrix\Main\Config\Option;
global $USER;
$resultModifiedGroup = json_decode(Option::get('onizamov', 'group_modified', '[]'), true);
$arGroups = $USER->GetUserGroupArray();
if (!empty(array_intersect($resultModifiedGroup, $arGroups))) {
    $aMenuLinks = [

    ];
}else{
    include_once('.top.menu_ext.php');
}


