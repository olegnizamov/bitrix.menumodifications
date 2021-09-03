<?php

namespace Onizamov\Menumodifications;

use Bitrix\Main\Config\Option;

class UserMenu
{
    const OPTION_NAME_SORT_ITEMS = 'left_menu_sorted_items_s1';
    const OPTION_CATEGORY = 'intranet';
    const ADMIN_GROUP = 1;
    const MODULE_ID = 'onizamov';
    const PARAMENT = "menu_group_";
    const OPTION_NAME_STANDARD_ITEMS = "left_menu_standard_items_s1";
    const OPTION_NAME_CUSTOM_PRESET_ITEMS = 'left_menu_custom_preset_items';

    public static function onAfterUserEventHandler(&$arFields)
    {
        if (empty($arFields['GROUP_ID']) && !is_array($arFields['GROUP_ID'])) {
            return;
        }

        $arrMenu = [];
        /** Перебираем все группы пользователя и получаем результатирующее меню*/
        foreach ($arFields['GROUP_ID'] as $group) {
            $groupId = $group['GROUP_ID'];
            /** Если у нас есть группа администратора */
            if ($groupId == self::ADMIN_GROUP) {
                return;
            }

            $savedProperties = json_decode(Option::get(self::MODULE_ID, self::PARAMENT . $groupId, '[]'), true);
            if (!empty($savedProperties)) {
                $arrMenu = $arrMenu + $savedProperties;
            }
        }

        if (!empty($arrMenu) && is_array($arrMenu)) {
            /** Удаляем меню по умолчанию */
            \CUserOptions::DeleteOption(self::OPTION_CATEGORY, self::OPTION_NAME_SORT_ITEMS);
            Option::delete(
                self::OPTION_CATEGORY,
                ["name" => self::OPTION_NAME_CUSTOM_PRESET_ITEMS]
            );

            \CUserOptions::SetOption(
                self::OPTION_CATEGORY,
                self::OPTION_NAME_STANDARD_ITEMS,
                $arrMenu,
                false,
                $arFields['ID']
            );
        }
    }

}