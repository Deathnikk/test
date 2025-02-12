<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$this->setFrameMode(true);
?>

<?php if(!empty($arResult["ITEMS"])): ?>
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
        <?php foreach ($arResult["ITEMS"] as $index => $arElement): ?>
            <?php
            $svyazIds = array();
            if (!empty($arElement["PROPERTIES"]["SVYAZ"]["VALUE"])) {
                $svyazIds = (array)$arElement["PROPERTIES"]["SVYAZ"]["VALUE"];
                $svyazIds = array_filter($svyazIds); // Удаляем пустые значения
            }
            ?>
            <?php $APPLICATION->IncludeComponent(
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
                    "SVYAZ_IDS" => $svyazIds
                ),
                false,
                array("HIDE_ICONS" => "Y")
            ); ?>
            
            <?php if(!empty($arElement["PROPERTIES"]["PROPERTY_REFERENCE"]["VALUE"])): ?>
                <div class="reference-items">
                    <h3>Ссылки на товары:</h3>
                    <?php foreach($arElement["PROPERTIES"]["PROPERTY_REFERENCE"]["VALUE"] as $referenceItemId): ?>
                        <?php $APPLICATION->IncludeComponent(
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
                        ); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($svyazIds)): ?>
                <div class="svyaz-items">
                    <h3>Связанные товары:</h3>
                    <?php foreach($svyazIds as $svyazItemId): ?>
                        <?php $APPLICATION->IncludeComponent(
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
                        ); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<div class="system_div_input">
    <div class="h1 bold">Итого (при площади <span id="system_input_value">10</span> м2): <span id="system_checkout"><?=$GLOBALS["SYSTEM_CHECKOUT"];?></span> руб.</div>
    <a id="send_system_chekout_open" href="#" class="btn-simple btn-black btn-medium margin-top-15 margin-right-15 print_hidden"><i class="fas fa-paper-plane icon margin-right-5"></i> Отправить расчёт специалисту</a>
    <a href="javascript:window.print()" class="btn-simple btn-black-border btn-medium margin-top-15 margin-right-15 print_hidden"><i class="fas fa-print icon margin-right-5"></i> Распечатать</a>
</div>
<?php endif; ?>
