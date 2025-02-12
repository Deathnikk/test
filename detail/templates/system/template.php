<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>

<!-- Отладочная информация -->
<div style="margin: 20px 0; padding: 10px; border: 1px solid #ccc; background-color: #f9f9f9;">
    <h3>Структура $arResult:</h3>
    <pre><?print_r($arResult)?></pre>
</div>

<?if(!empty($arResult["ITEMS"])):?>
<div class="system_div_input print_hidden">
    <div class="system_icon">
        <i class="fas fa-calculator icon margin-right-7 margin-left-2"></i>
    </div>
    <div class="h2">
        Введите свою площадь. Смета пересчитывается автоматически.
    </div>
    <div class="system_input"><input type="text" id="system_input" value="10" /> м<sup>2</sup></div>
</div>
<div class="detail-text-wrap system-list-items-wrap">
    <div class="system-list-items">
        <?foreach ($arResult["ITEMS"] as $index => $arElement):?>
            <!-- Отладочная информация для каждого элемента -->
            <div style="margin: 10px 0; padding: 5px; border: 1px solid #ccc; background-color: #f0f0f0;">
                <h4>Элемент ID: <?=$arElement["ID"]?></h4>
                <pre>
                Ключи массива $arElement: <?print_r(array_keys($arElement))?>
                
                <?if(isset($arElement["PROPERTIES"])):?>
                    Ключи массива PROPERTIES: <?print_r(array_keys($arElement["PROPERTIES"]))?>
                <?else:?>
                    PROPERTIES отсутствует в элементе
                <?endif;?>
                </pre>
            </div>

            <?
            // Получаем массив ID из свойства SVYAZ
            $svyazIds = array();
            if (!empty($arElement["PROPERTIES"]["SVYAZ"]["VALUE"])) {
                $svyazIds = (array)$arElement["PROPERTIES"]["SVYAZ"]["VALUE"];
                // Удаляем нулевые значения
                $svyazIds = array_filter($svyazIds);
            }
            ?>
            <?$APPLICATION->IncludeComponent(
                "unlimtech:system.item", 
                "system", 
                array(
                    "CACHE_TIME" => $arParams["CACHE_TIME"],
                    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                    "HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
                    "HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                    "PRODUCT_ID" => $arElement["ID"],
                    "PICTURE_HEIGHT" => "120",
                    "PICTURE_WIDTH" => "120",
                    "PRODUCT_PRICE_CODE" => $arParams["PRICE_CODE"],
                    "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
                    "CURRENCY_ID" => $arParams["CURRENCY_ID"],
                    "SVYAZ_IDS" => $svyazIds // Передаем массив ID связанных элементов
                ),
                false,
                array("HIDE_ICONS" => "Y")
            );?>
            
            <?if(!empty($arElement["PROPERTIES"]["PROPERTY_REFERENCE"]["VALUE"])):?>
                <div class="reference-items">
                    <h3>Ссылки на товары:</h3>
                    <?foreach($arElement["PROPERTIES"]["PROPERTY_REFERENCE"]["VALUE"] as $referenceItemId):?>
                        <?$APPLICATION->IncludeComponent(
                            "unlimtech:system.item", 
                            "system", 
                            array(
                                "CACHE_TIME" => $arParams["CACHE_TIME"],
                                "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                                "HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
                                "HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
                                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                                "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                "PRODUCT_ID" => $referenceItemId,
                                "PICTURE_HEIGHT" => "120",
                                "PICTURE_WIDTH" => "120",
                                "PRODUCT_PRICE_CODE" => $arParams["PRICE_CODE"],
                                "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
                                "CURRENCY_ID" => $arParams["CURRENCY_ID"]
                            ),
                            false,
                            array("HIDE_ICONS" => "Y")
                        );?>
                    <?endforeach;?>
                </div>
            <?endif;?>
            
            <?if(!empty($arElement["PROPERTIES"]["SVYAZ"]["VALUE"])):?>
                <div class="svyaz-items">
                    <h3>Связанные товары:</h3>
                    <?
                    $svyazItems = array();
                    if(!is_array($arElement["PROPERTIES"]["SVYAZ"]["VALUE"])){
                        $svyazItems[] = $arElement["PROPERTIES"]["SVYAZ"]["VALUE"];
                    } else {
                        $svyazItems = $arElement["PROPERTIES"]["SVYAZ"]["VALUE"];
                    }
                    ?>
                    <?foreach($svyazItems as $svyazItemId):?>
                        <?$APPLICATION->IncludeComponent(
                            "unlimtech:system.item", 
                            "system", 
                            array(
                                "CACHE_TIME" => $arParams["CACHE_TIME"],
                                "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                                "HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
                                "HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
                                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                                "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                "PRODUCT_ID" => $svyazItemId,
                                "PICTURE_HEIGHT" => "120",
                                "PICTURE_WIDTH" => "120",
                                "PRODUCT_PRICE_CODE" => $arParams["PRICE_CODE"],
                                "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
                                "CURRENCY_ID" => $arParams["CURRENCY_ID"]
                            ),
                            false,
                            array("HIDE_ICONS" => "Y")
                        );?>
                    <?endforeach;?>
                </div>
            <?endif;?>
        <?endforeach;?>
    </div>
</div>
<div class="system_div_input">
    <div class="h1 bold">Итого (при площади <span id="system_input_value">10</span> м2): <span id="system_checkout"><?=$GLOBALS["SYSTEM_CHECKOUT"];?></span> руб.</div>
    <a id="send_system_chekout_open" href="#" class="btn-simple btn-black btn-medium margin-top-15 margin-right-15 print_hidden"><i class="fas fa-paper-plane icon margin-right-5"></i> Отправить расчёт специалисту</a>
    <a href="javascript:window.print()" class="btn-simple btn-black-border btn-medium margin-top-15 margin-right-15 print_hidden"><i class="fas fa-print icon margin-right-5"></i> Распечатать</a>
</div>
<?endif;?>
