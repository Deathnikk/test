<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$this->setFrameMode(true);
?>
<?php if(!empty($arResult)): ?>
    <div class="list-item-wrap">
        <div class="list-item">
            <div class="tb">
                <div class="image tc">
                    <a href="<?php echo $arResult["DETAIL_PAGE_URL"]; ?>" class="image-container">
                        <img src="<?php echo $arResult["PICTURE"]["src"]; ?>" alt="<?php echo !empty($arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"]) ? $arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] : $arResult["NAME"]; ?>" title="<?php echo !empty($arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]) ? $arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] : $arResult["NAME"]; ?>">
                    </a>
                </div>
                <div class="text tc">
                    <div class="">
                        <a href="<?php echo $arResult["DETAIL_PAGE_URL"]; ?>" class="name theme-color-hover"><?php echo $arResult["NAME"]; ?></a>
                    </div>
                    <div class="clear prop-wrap">
                        <div class="price-wrap">
                            <?php if(!empty($arResult["PRICE"])): ?>
                            <div class="price">
                                Цена:
                                <b>
                                    <span id="system_item_price_<?php echo $arResult["ID"]; ?>" data-value="<?php echo $arResult["PRICE"]["RESULT_PRICE"]["DISCOUNT_PRICE"]; ?>">
                                        <?php echo CCurrencyLang::CurrencyFormat($arResult["PRICE"]["DISCOUNT_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true); ?>
                                    </span>
                                <?php if($arParams["HIDE_MEASURES"] != "Y" && !empty($arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"])): ?>
                                    <span class="measure"> / <?php echo $arResult["EXTRA_SETTINGS"]["MEASURES"][$arResult["CATALOG_MEASURE"]]["SYMBOL_RUS"]; ?></span>
                                <?php endif; ?>
                                </b>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="price-wrap">
                            Площадь в упаковке: <b><span class="system_item_rashod" id="system_item_rashod_<?php echo $arResult["ID"]; ?>" data-value="<?php echo $arResult["SYSTEM_RASHOD_M2"]; ?>"><?php echo $arResult["SYSTEM_RASHOD_M2"]; ?></span> м2</b>
                        </div>
                        <div class="price-wrap">
                            Кол-во: <b><span class="system_item_count" id="system_item_count_<?php echo $arResult["ID"]; ?>" data-value="<?php echo $arResult["SYSTEM_COUNT"]; ?>"><?php echo $arResult["SYSTEM_COUNT"]; ?></span></b>
                        </div>
                        <div class="price-wrap">
                            Стоимость: <b><span class="system_item_summ" id="system_item_summ_<?php echo $arResult["ID"]; ?>" data-value="<?php echo $arResult["SYSTEM_SUMM"]; ?>"><?php echo $arResult["SYSTEM_SUMM"]; ?></span> руб.</b>
                        </div>
                    </div>

                    <?php if(!empty($arResult["PROPERTIES"]["PROPERTY_REFERENCE"]["VALUE"]) || !empty($arResult["PROPERTIES"]["SVYAZ"]["VALUE"])): ?>
                        <div class="related-items" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;">
                            <?php
                            $relatedIds = array_merge(
                                (array)$arResult["PROPERTIES"]["PROPERTY_REFERENCE"]["VALUE"],
                                (array)$arResult["PROPERTIES"]["SVYAZ"]["VALUE"]
                            );
                            $relatedIds = array_unique(array_filter($relatedIds));
                            
                            if (!empty($relatedIds)):
                                $rsElements = CIBlockElement::GetList(
                                    array("SORT" => "ASC"),
                                    array("ID" => $relatedIds),
                                    false,
                                    false,
                                    array("ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL", "PREVIEW_PICTURE")
                                );
                            ?>
                                <h4>Связанные товары:</h4>
                                <?php while($arElement = $rsElements->GetNext()): ?>
                                    <?php
                                    $elementPrice = CPrice::GetBasePrice($arElement["ID"]);
                                    $picture = CFile::ResizeImageGet($arElement["PREVIEW_PICTURE"], array('width'=>100, 'height'=>100), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                    ?>
                                    <div class="related-item" style="margin-bottom: 10px; padding: 10px; border: 1px solid #ddd;">
                                        <?php if($picture): ?>
                                            <img src="<?php echo $picture['src']; ?>" alt="<?php echo $arElement["NAME"]; ?>" style="float: left; margin-right: 10px;">
                                        <?php endif; ?>
                                        <a href="<?php echo $arElement["DETAIL_PAGE_URL"]; ?>"><?php echo $arElement["NAME"]; ?></a>
                                        <?php if($elementPrice): ?>
                                            <div class="price">
                                                <?php echo CCurrencyLang::CurrencyFormat($elementPrice["PRICE"], $elementPrice["CURRENCY"], true); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div style="clear: both;"></div>
                                    </div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
