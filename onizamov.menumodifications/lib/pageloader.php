<?php

namespace Onizamov\Menumodifications;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Page\Asset;

class Pageloader
{

    function onPageLoad()
    {
        global $USER;

        if ($USER->IsAdmin()) {
            return;
        }

        /** Проверяем что пользователь входит в группу изменных меню и он не админ*/
        $resultModifiedGroup = json_decode(Option::get('onizamov', 'group_modified', '[]'), true);
        $arGroups = $USER->GetUserGroupArray();
        if (!empty(array_intersect($resultModifiedGroup, $arGroups))) {
            Asset::getInstance()->addString('<link rel="stylesheet" type="text/css" href="/local/modules/onizamov.menumodifications/assets/style.css">');
        }
    }


}