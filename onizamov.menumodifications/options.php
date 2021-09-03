<?php

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'onizamov');

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

global $USER;
global $APPLICATION;

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

Loader::includeModule("onizamov.menumodifications");


$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages($context->getServer()->getDocumentRoot() . "/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

$tabArray = [];
$filter["ACTIVE"] = "Y";
$rsGroups = CGroup::GetList(($by = "c_sort"), ($order = "desc"), $filter);
while ($arGroups = $rsGroups->Fetch()) {
    $tabArray[] = [
        "DIV" => $arGroups['ID'],
        "TAB" => $arGroups['NAME'],
        "TITLE" => $arGroups['NAME'],
    ];
}

$tabControl = new CAdminTabControl("tabControl", $tabArray);

if (!empty($save) && $request->isPost() && check_bitrix_sessid()) {
    $error = false;

    $data = $request->getPostList();

    $resultModifiedGroup = [];

    foreach ($data as $propName => $propValue) {
        if (is_array($data[$propName])) {
            $array = [];
            foreach ($propValue as $key => $prop) {
                $name = !empty($prop['name']) ? $prop['name'] : '';
                $href = !empty($prop['href']) ? $prop['href'] : '';

                if (!empty($name) && !empty($href)) {
                    $array[] = [
                        'TEXT' => $name,
                        'LINK' => $href,
                        'ID'   => $key,
                    ];
                }
            }

            if (!empty($array)) {
                $resultModifiedGroup[] = str_replace("menu_group_", "", $propName);
            }
            $array = json_encode($array);
            Option::set(ADMIN_MODULE_NAME, $propName, $array);
        }
    }
    Option::set(ADMIN_MODULE_NAME, 'group_modified', json_encode($resultModifiedGroup));

    if ($error) {
        CAdminMessage::showMessage('Ошибка сохранения настроек');
    } else {
        CAdminMessage::showMessage(
            [
                "MESSAGE" => 'Настройки успешно сохранены',
                "TYPE"    => "OK",
            ]
        );
    }
}

$tabControl->begin();
$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/js/main/jquery/jquery-1.7.min.js"></script>');
?>
    <style>
        table th,
        table .row td {
            text-align: center !important;
            white-space: nowrap;
        }

        .row input[type=text] {
            width: 80%;
        }

        .hidden {
            display: none
        }
    </style>


    <form method="post"
          action="<?= sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>">
        <?php
        echo bitrix_sessid_post();

        foreach ($tabArray as $tab) { ?>

            <?
            $tabEntity = $tab["DIV"];
            $tabEntityProperties = "menu_group_" . $tabEntity;
            $tabControl->beginNextTab();
            $savedProperties = json_decode(Option::get(ADMIN_MODULE_NAME, $tabEntityProperties, '[]'), true);
            $i = 0;
            ?>
            <tr>
                <th width="40%">
                    Название меню
                </th>
                <th width="40%">
                    Ссылка меню
                </th>
            </tr>
        <?php
        foreach ($savedProperties

        as $menuItem) : ?>
            <tr class="row">
                <td>
                    <input type="text" name="<?= $tabEntityProperties ?>[<?= $i ?>][name]"
                           value="<?= $menuItem['TEXT'] ?>"/>
                </td>
                <td>
                    <input type="text" name="<?= $tabEntityProperties ?>[<?= $i ?>][href]"
                           value="<?= $menuItem['LINK'] ?>"/>
                </td>
            </tr>
        <?
        $i++;
        endforeach ?>
            <tr id="<?= $tabEntity ?>Row" class="row hidden">
                <td>
                    <input type="text" name="<?= $tabEntityProperties ?>[][name]"/>
                </td>
                <td>
                    <input type="text" name="<?= $tabEntityProperties ?>[][href]"/>
                </td>
            </tr>
            <tr class="heading">
                <td colspan="3">
                    <button type="button" class="adm-btn-save" id="add<?= $tabEntity ?>Row">Добавить поле</button>
                </td>
            </tr>


            <script type="text/javascript">
                $(document).ready(function () {
                    $('#add<?= $tabEntity ?>Row').click(function () {
                        let row = $('#<?= $tabEntity ?>Row').clone().removeClass('hidden');
                        $('input[name$="[name]"]', row).attr("name", "<?= $tabEntityProperties ?>[" + ($('#<?= $tabEntity ?>_edit_table .row').length + 1) + "][name]");
                        $('input[name$="[href]"]', row).attr("name", "<?= $tabEntityProperties ?>[" + ($('#<?= $tabEntity ?>_edit_table .row').length + 1) + "][href]");
                        $('#<?= $tabEntity ?>_edit_table .row').last().after(row)
                    })
                });
            </script>

            <?
        } ?>

        <?php
        $tabControl->Buttons(); ?>
        <input type="submit" name="save" value="<?= Loc::getMessage("MAIN_SAVE") ?>"
               title="<?= Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>" class="adm-btn-save"/>
        <?php
        $tabControl->End(); ?>
    </form>

    <h2 id="onizamov-menumodifications">onizamov.menumodifications</h2>
    <p>Модуль реализует возможность установки пунктов левого меню каждой группе пользователей.</p>
    <h2 id="-">Логика работы</h2>
    <p>Модуль устанавливает для пользователя в его настройки left_menu_standard_items_s1 (таблица b_user_option) пункты
        меню, которые необходимо вывести. Установка срабатывает после события обновления и добавления пользователя в
        систему.</p>
    <h2 id="-1-bitrix-admin-settings-php-lang-ru-mid-onizamov-menumodifications-mid_menu-1-">Настройка модуля</h2>
    <p>1) Для настройки левого меню для групп пользователей переходим в настройки модуля &quot;<a
                href="/bitrix/admin/settings.php?lang=ru&amp;mid=onizamov.menumodifications&amp;mid_menu=1">Модуль
            настройки левого меню пользователей</a>&quot;</p>
    <p>2) Для выбранной группы пользователей нажимаем &quot;Добавить поле&quot; и в пункте &quot;Название меню&quot;
        указываем название ссылки, а в пункте &quot;Cсылка меню&quot;</p>
    <p><img src="/local/modules/onizamov.menumodifications/assets/1.png" alt="1.png"></p>
    <p>3) Переходим к пользователю и добавляем его в данную группу.</p>
    <p><strong>NB!</strong> Если пользователь состоит в группе администраторы, то данная настройка на него не действует!
    </p>
    <p><img src="/local/modules/onizamov.menumodifications/assets/2.png" alt="2.png"></p>
<?php
$tabControl->end();
?>