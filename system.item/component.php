<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Config;
use Bitrix\Main\Entity;
use Bitrix\Currency;
use Bitrix\Catalog;
use Bitrix\Iblock;

global $USER;
global $APPLICATION;

if (!isset($arParams["CACHE_TIME"])){
    $arParams["CACHE_TIME"] = 1285912;
}

$arParams["DISPLAY_FORMAT_PROPERTIES"] = !empty($arParams["DISPLAY_FORMAT_PROPERTIES"]) ? $arParams["DISPLAY_FORMAT_PROPERTIES"] : "N";
$arParams["DISPLAY_MORE_PICTURES"] = !empty($arParams["DISPLAY_MORE_PICTURES"]) ? $arParams["DISPLAY_MORE_PICTURES"] : "N";
$arParams["DISPLAY_LAST_SECTION"] = !empty($arParams["DISPLAY_LAST_SECTION"]) ? $arParams["DISPLAY_LAST_SECTION"] : "N";
$arParams["DISPLAY_OFFERS_TABLE"] = !empty($arParams["DISPLAY_OFFERS_TABLE"]) ? $arParams["DISPLAY_OFFERS_TABLE"] : "N";
$arParams["DISPLAY_FILES_VIDEO"] = !empty($arParams["DISPLAY_FILES_VIDEO"]) ? $arParams["DISPLAY_FILES_VIDEO"] : "N";
$arParams["SET_CANONICAL_URL"] = !empty($arParams["SET_CANONICAL_URL"]) ? $arParams["SET_CANONICAL_URL"] : "N";
$arParams["DISPLAY_RELATED"] = !empty($arParams["DISPLAY_RELATED"]) ? $arParams["DISPLAY_RELATED"] : "N";
$arParams["DISPLAY_SIMILAR"] = !empty($arParams["DISPLAY_SIMILAR"]) ? $arParams["DISPLAY_SIMILAR"] : "N";
$arParams["DISPLAY_BRAND"] = !empty($arParams["DISPLAY_BRAND"]) ? $arParams["DISPLAY_BRAND"] : "N";

foreach ($arParams as $inx => $paramValue){
    if(is_array($paramValue)){
        $paramValue = $paramValue[0];
    }
    if($paramValue == "undefined"){
        unset($arParams[$inx]);
    }
}

if($arParams["CONVERT_CURRENCY"] != "Y"){
    if(isset($arParams["CURRENCY_ID"])){
        unset($arParams["CURRENCY_ID"]);
    }
}

$arParams["PRODUCT_PRICE_CODE"] = empty($arParams["PRODUCT_PRICE_CODE"]) ? array() : $arParams["PRODUCT_PRICE_CODE"];
$arParams["AVAILABLE_OFFERS"] = empty($arParams["AVAILABLE_OFFERS"]) ? array() : $arParams["AVAILABLE_OFFERS"];
$arParams["PICTURE_HEIGHT"] = empty($arParams["PICTURE_HEIGHT"]) ? "200" : $arParams["PICTURE_HEIGHT"];
$arParams["PICTURE_WIDTH"] = empty($arParams["PICTURE_WIDTH"]) ? "220" : $arParams["PICTURE_WIDTH"];
$arParams["IMAGE_QUALITY"] = empty($arParams["IMAGE_QUALITY"]) ? "80" : $arParams["IMAGE_QUALITY"];
$arParams["IBLOCK_ID"] = empty($arParams["IBLOCK_ID"]) ? false : $arParams["IBLOCK_ID"];

if(empty($arParams["PRODUCT_ID"])){
    ShowError("product id not set!");
    return 0;
}

if(empty($arParams["IBLOCK_ID"])){
    ShowError("iblock id not set!");
    return 0;
}

$cacheID = array(
    "NAME" => "ELEMENT_FULL_LIST",
    "PRODUCT_PRICE_CODE" => implode(",", $arParams["PRODUCT_PRICE_CODE"]),
    "PICTURE_HEIGHT" => floatval($arParams["PICTURE_HEIGHT"]),
    "HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
    "PICTURE_WIDTH" => floatval($arParams["PICTURE_WIDTH"]),
    "AVAILABLE_OFFERS" => $arParams["AVAILABLE_OFFERS"],
    "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
    "PRODUCT_ID" => floatval($arParams["PRODUCT_ID"]),
    "CURRENCY_ID" => $arParams["CURRENCY_ID"],
    "USER_GROUPS" => $USER->GetGroups(),
    "SITE_ID" => SITE_ID
);

$cacheDir = "/";

$obExtraCache = new CPHPCache();
if($arParams["CACHE_TYPE"] != "N" && $obExtraCache->InitCache($arParams["CACHE_TIME"], serialize($cacheID), $cacheDir)){
    $arResult = $obExtraCache->GetVars();
    $arResult["FROM_CACHE"] = "Y";
}
elseif($obExtraCache->StartDataCache()){
    if(
        !Bitrix\Main\Loader::includeModule("dw.deluxe")
        || !Bitrix\Main\Loader::includeModule("iblock")
        || !Bitrix\Main\Loader::includeModule('highloadblock')
        || !Bitrix\Main\Loader::includeModule("catalog")
        || !Bitrix\Main\Loader::includeModule("sale")
        || !Bitrix\Main\Loader::includeModule("currency")
    ){
        $obExtraCache->AbortDataCache();
        ShowError("modules not installed!");
        return 0;
    }

    $opCurrency = ($arParams["CONVERT_CURRENCY"] == "Y" && !empty($arParams["CURRENCY_ID"])) ? $arParams["CURRENCY_ID"] : NULL;

    $arElement = array();
    $arResult = array();

    $arElement["FROM_CACHE"] = "N";

    $opIblockId = empty($skuParentProduct) ? $arParams["IBLOCK_ID"] : $arElement["PARENT_PRODUCT_IBLOCK_ID"];
    $opProductId = empty($skuParentProduct) ? $arParams["PRODUCT_ID"] : $arElement["PARENT_PRODUCT_ID"];

    $arSelect = Array(
        "ID", "NAME", "CODE", "TIMESTAMP_X", "PREVIEW_TEXT", "DETAIL_TEXT",
        "DATE_CREATE", "IBLOCK_ID", "IBLOCK_TYPE", "DATE_MODIFY",
        "DATE_ACTIVE_TO", "DETAIL_PICTURE", "DATE_ACTIVE_FROM",
        "CATALOG_QUANTITY", "DETAIL_PAGE_URL", "IBLOCK_SECTION_ID",
        "CATALOG_MEASURE", "CATALOG_AVAILABLE", "CATALOG_SUBSCRIBE",
        "CATALOG_QUANTITY_TRACE", "CATALOG_CAN_BUY_ZERO", "CANONICAL_PAGE_URL"
    );

    $arFilter = Array(
        "IBLOCK_ID" => $opIblockId,
        "ID" => $opProductId,
        "ACTIVE_DATE" => "Y",
        "ACTIVE" => "Y"
    );

    if(!empty($arParams["SHOW_DEACTIVATED"]) && $arParams["SHOW_DEACTIVATED"] == "Y"){
        $arFilter["ACTIVE_DATE"] = "";
        $arFilter["ACTIVE"] = "";
    }

    $rsBaseProduct = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    if($oBaseProduct = $rsBaseProduct->GetNextElement()){
        $arElement["PARENT_PRODUCT"] = $oBaseProduct->GetFields();
        $arElement["PARENT_PRODUCT"]["PROPERTIES"] = $oBaseProduct->GetProperties(array("sort" => "asc", "name" => "asc"), array("EMPTY" => "N"));

        $seoValues = new Bitrix\Iblock\InheritedProperty\ElementValues($arElement["PARENT_PRODUCT"]["IBLOCK_ID"], $arElement["PARENT_PRODUCT"]["ID"]);
        $arElement["PARENT_PRODUCT"]["IPROPERTY_VALUES"] = $seoValues->getValues();

        $arElement["IBLOCK_ID"] = $arElement["PARENT_PRODUCT"]["IBLOCK_ID"];
        $arElement["IBLOCK_SECTION_ID"] = $arElement["PARENT_PRODUCT"]["IBLOCK_SECTION_ID"];
        $arElement["NAME"] = $arElement["PARENT_PRODUCT"]["NAME"];
        $arElement["DETAIL_PAGE_URL"] = $arElement["PARENT_PRODUCT"]["DETAIL_PAGE_URL"];
        $arElement["PREVIEW_TEXT"] = $arElement["PARENT_PRODUCT"]["PREVIEW_TEXT"];
        $arElement["DETAIL_TEXT"] = $arElement["PARENT_PRODUCT"]["DETAIL_TEXT"];
        $arElement["~DETAIL_TEXT"] = $arElement["PARENT_PRODUCT"]["~DETAIL_TEXT"];

        if(!empty($arElement["PARENT_PRODUCT"]["DETAIL_PICTURE"])){
            $arElement["PICTURE"] = CFile::ResizeImageGet($arElement["PARENT_PRODUCT"]["DETAIL_PICTURE"], 
                array("width" => $arParams["PICTURE_WIDTH"], "height" => $arParams["PICTURE_HEIGHT"]), 
                BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, $arParams["IMAGE_QUALITY"]);
        }

        if(!empty($arElement["PARENT_PRODUCT"]["CANONICAL_PAGE_URL"])){
            $arElement["CANONICAL_PAGE_URL"] = $arElement["PARENT_PRODUCT"]["CANONICAL_PAGE_URL"];
        }

        $arButtons = CIBlock::GetPanelButtons(
            $arElement["PARENT_PRODUCT"]["IBLOCK_ID"],
            $arElement["PARENT_PRODUCT"]["ID"],
            $arElement["PARENT_PRODUCT"]["IBLOCK_SECTION_ID"],
            array("SECTION_BUTTONS" => true, "SESSID" => true, "CATALOG" => true)
        );

        $arElement["PARENT_PRODUCT"]["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
        $arElement["PARENT_PRODUCT"]["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];
    }

    $arFilter = Array(
        "IBLOCK_ID" => $opIblockId,
        "ID" => $arParams["PRODUCT_ID"],
        "ACTIVE_DATE" => "Y",
        "ACTIVE" => "Y"
    );

    if(!empty($arParams["SHOW_DEACTIVATED"]) && $arParams["SHOW_DEACTIVATED"] == "Y"){
        $arFilter["ACTIVE_DATE"] = "";
        $arFilter["ACTIVE"] = "";
    }

    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    if($ob = $res->GetNextElement()){
        $arElement = array_merge($arElement, $ob->GetFields());
        $arElement["PROPERTIES"] = $ob->GetProperties(array("sort" => "asc", "name" => "asc"), array("EMPTY" => "N"));
        $arElement["DISPLAY_PROPERTIES"] = array();

        if(!empty($arElement["DETAIL_PICTURE"])){
            $arElement["PICTURE"] = CFile::ResizeImageGet($arElement["DETAIL_PICTURE"], 
                array("width" => $arParams["PICTURE_WIDTH"], "height" => $arParams["PICTURE_HEIGHT"]), 
                BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, $arParams["IMAGE_QUALITY"]);
        }else{
            if(empty($arElement["PICTURE"])){
                $arElement["PICTURE"]["src"] = SITE_TEMPLATE_PATH."/images/empty.png";
            }
        }

        if(isset($arElement["PROPERTIES"]["WIDTH_HL"]["VALUE"]) 
           && isset($arElement["PROPERTIES"]["LENGHT_HL"]["VALUE"]) 
           && isset($arElement["PROPERTIES"]["PACKPCS_1"]["VALUE"])) {
            $arElement["SYSTEM_RASHOD_M2"] = round(($arElement["PROPERTIES"]["WIDTH_HL"]["VALUE"]
                                                   * $arElement["PROPERTIES"]["LENGHT_HL"]["VALUE"]
                                                   * $arElement["PROPERTIES"]["PACKPCS_1"]["VALUE"])/1000000, 2);
        } elseif(isset($arElement["PROPERTIES"]["PLOSHAD_UPAKOVKA"]["VALUE"])) {
            $arElement["SYSTEM_RASHOD_M2"] = str_replace(" м2", "", $arElement["PROPERTIES"]["PLOSHAD_UPAKOVKA"]["VALUE"]);
        }

        $arElement["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW"] = array();
        $arElement["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW_FILTER"] = array();

        if(!empty($arParams["PRODUCT_PRICE_CODE"])){
            $arPricesInfo = DwPrices::getPriceInfo($arParams["PRODUCT_PRICE_CODE"], $arElement["IBLOCK_ID"]);
            if(!empty($arPricesInfo)){
                $arElement["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW"] = $arPricesInfo["ALLOW"];
                $arElement["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW_FILTER"] = $arPricesInfo["ALLOW_FILTER"];
            }
        }

        $arElement["PRICE"] = DwPrices::getPricesByProductId(
            $arElement["ID"],
            $arElement["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW"],
            $arElement["EXTRA_SETTINGS"]["PRODUCT_PRICE_ALLOW_FILTER"],
            $arParams["PRODUCT_PRICE_CODE"],
            $arElement["IBLOCK_ID"],
            $opCurrency
        );

        $arElement["EXTRA_SETTINGS"]["COUNT_PRICES"] = $arElement["PRICE"]["COUNT_PRICES"];
        $arElement["EXTRA_SETTINGS"]["CURRENCY"] = empty($opCurrency) ? $arElement["PRICE"]["RESULT_PRICE"]["CURRENCY"] : $opCurrency;

        $rsMeasure = CCatalogMeasure::getList(
            array(),
            array("ID" => $arElement["CATALOG_MEASURE"]),
            false,
            false,
            false
        );

        while($arNextMeasure = $rsMeasure->Fetch()){
            $arElement["EXTRA_SETTINGS"]["MEASURES"][$arNextMeasure["ID"]] = $arNextMeasure;
        }

        $arElement["EXTRA_SETTINGS"]["BASKET_STEP"] = 1;

        $rsMeasureRatio = CCatalogMeasureRatio::getList(
            array(),
            array("PRODUCT_ID" => floatval($arElement["ID"])),
            false,
            false,
            array()
        );

        if($arProductMeasureRatio = $rsMeasureRatio->Fetch()){
            if(!empty($arProductMeasureRatio["RATIO"])){
                $arElement["EXTRA_SETTINGS"]["BASKET_STEP"] = $arProductMeasureRatio["RATIO"];
            }
        }

        if(floatval($arElement["SYSTEM_RASHOD_M2"]) <= 0) {
            $arElement["SYSTEM_RASHOD_M2"] = 0;
        } else {
            if(floatval($arElement["SYSTEM_RASHOD_M2"]) >= 10) {
                $arElement["SYSTEM_COUNT"] = 1;
                $arElement["SYSTEM_SUMM"] = $arElement["PRICE"]["DISCOUNT_PRICE"];
            } elseif(floatval($arElement["SYSTEM_RASHOD_M2"]) < 10) {
                $arElement["SYSTEM_COUNT"] = ceil(10/floatval($arElement["SYSTEM_RASHOD_M2"]));
                $arElement["SYSTEM_SUMM"] = ceil($arElement["PRICE"]["DISCOUNT_PRICE"]*$arElement["SYSTEM_COUNT"]);
            }
            $GLOBALS["SYSTEM_CHECKOUT"] += $arElement["SYSTEM_SUMM"];
        }

        $seoValues = new Bitrix\Iblock\InheritedProperty\ElementValues($arElement["IBLOCK_ID"], $arElement["ID"]);
        $arElement["IPROPERTY_VALUES"] = $seoValues->getValues();

        $arButtons = CIBlock::GetPanelButtons(
            $arElement["IBLOCK_ID"],
            $arElement["ID"],
            $arElement["IBLOCK_SECTION_ID"],
            array("SECTION_BUTTONS" => true, "SESSID" => true, "CATALOG" => true)
        );

        $arElement["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
        $arElement["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

        global $CACHE_MANAGER;
        $CACHE_MANAGER->StartTagCache($cacheDir);
        $CACHE_MANAGER->RegisterTag("iblock_id_".$arElement["IBLOCK_ID"]);
        $CACHE_MANAGER->EndTagCache();
    }
    else{
        if($arParams["DETAIL_ELEMENT"] == "Y"){
            Iblock\Component\Tools::process404(
                trim($arParams["MESSAGE_404"]) ?: GetMessage("CATALOG_ITEM_NOT_FOUND"),
                true,
                $arParams["SET_STATUS_404"] === "Y",
                $arParams["SHOW_404"] === "Y",
                $arParams["FILE_404"]
            );
        }
        $obExtraCache->AbortDataCache();
    }

    if(!empty($arElement)){
        $obExtraCache->EndDataCache($arElement);
        unset($obExtraCache);
        $arResult = $arElement;
        unset($arElement);
    }
}

if(!Bitrix\Main\Loader::includeModule("dw.deluxe")){
    ShowError("modules not installed!");
    return 0;
}

$extraParams = array(
    "DISPLAY_FORMAT_PROPERTIES" => $arParams["DISPLAY_FORMAT_PROPERTIES"],
    "DISPLAY_MORE_PICTURES" => $arParams["DISPLAY_MORE_PICTURES"],
    "DISPLAY_OFFERS_TABLE" => $arParams["DISPLAY_OFFERS_TABLE"],
    "DISPLAY_FILES_VIDEO" => $arParams["DISPLAY_FILES_VIDEO"],
    "DISPLAY_RELATED" => $arParams["DISPLAY_RELATED"],
    "DISPLAY_SIMILAR" => $arParams["DISPLAY_SIMILAR"],
    "DISPLAY_BRAND" => $arParams["DISPLAY_BRAND"]
);

$extraContent = DwItemInfo::get_extra_content($arParams["CACHE_TIME"], $arParams["CACHE_TYPE"], $cacheID, $cacheDir, $extraParams, $arParams, $arResult, $opCurrency);

if(!empty($extraContent)){
    $arResult = $extraContent;
}

if(!empty($arResult)){
    $this->IncludeComponentTemplate();
}
?>