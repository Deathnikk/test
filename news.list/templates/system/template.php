<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<h1><?=$arResult["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"];?></h1>
<?if(!empty($arResult["ITEMS"])):?>

	<?$startIndex = 0;?>
	<div class="tiles-list blog-list">
		<?foreach($arResult["ITEMS"] as $arNextElement):?>
			<?
				//for edit buttons
				$this->AddEditAction($arNextElement["ID"], $arNextElement["EDIT_LINK"], CIBlock::GetArrayByID($arNextElement["IBLOCK_ID"], "ELEMENT_EDIT"));
				$this->AddDeleteAction($arNextElement["ID"], $arNextElement["DELETE_LINK"], CIBlock::GetArrayByID($arNextElement["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage("CT_BNL_ELEMENT_DELETE_CONFIRM")));
				
				//get picture resize
				if(!empty($arNextElement["DETAIL_PICTURE"])){
					$arNextElement["RESIZE_PICTURE"] = CFile::ResizeImageGet($arNextElement["DETAIL_PICTURE"], array("width" => 600, "height" => 600), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 85);
				}

				// Получаем свойство SVYAZ
				$property = CIBlockElement::GetProperty($arNextElement["IBLOCK_ID"], $arNextElement["ID"], array("sort" => "asc"), array("CODE" => "SVYAZ"))->Fetch();
				$nestedElements = [];
				if ($property && !empty($property["DESCRIPTION"])) {
					$nestedElements = json_decode($property["DESCRIPTION"], true);
				}

				// Функция для рекурсивного отображения вложенных элементов
				function displayNestedElements($elements) {
					$html = "<ul>";
					foreach ($elements as $element) {
						$elementId = is_array($element) ? $element['id'] : $element;
						$rsElement = CIBlockElement::GetByID($elementId);
						if ($arElement = $rsElement->GetNext()) {
							$html .= "<li>[" . $arElement["ID"] . "] " . $arElement["NAME"];
							if (is_array($element) && isset($element['children'])) {
								$html .= displayNestedElements($element['children']);
							}
							$html .= "</li>";
						}
					}
					$html .= "</ul>";
					return $html;
				}
			?>
			<div class="tile-wrap" id="<?=$this->GetEditAreaId($arNextElement["ID"]);?>">
				<div class="tile<?if(!empty($startIndex)):?><?if(empty($arNextElement["RESIZE_PICTURE"])):?> no-image<?else:?> center-image<?endif;?><?endif;?>">
					<?if(!empty($arNextElement["RESIZE_PICTURE"])):?>
						<a href="<?=$arNextElement["DETAIL_PAGE_URL"]?>" class="image-wrap">
							<span class="image" style="background-image: url('<?=$arNextElement["RESIZE_PICTURE"]["src"]?>');"></span>
						</a>
					<?endif;?>
					<div class="tile-text">
						<?if(!empty($arNextElement["NAME"])):?>
							<a href="<?=$arNextElement["DETAIL_PAGE_URL"]?>" class="h3 ff-medium"><?=$arNextElement["NAME"]?></a>
						<?endif;?>
						<?if(!empty($arNextElement["PREVIEW_TEXT"])):?>
							<div class="tile-descr"><?=$arNextElement["PREVIEW_TEXT"]?></div>
						<?endif;?>
						<?if(!empty($arNextElement["DETAIL_PAGE_URL"])):?>
							<a href="<?=$arNextElement["DETAIL_PAGE_URL"]?>" class="btn-simple btn-border btn-micro">Рассчитать</a>
						<?endif;?>
						<?if(!empty($nestedElements)):?>
							<div class="nested-elements">
								<h4>Вложенные элементы:</h4>
								<?=displayNestedElements($nestedElements);?>
							</div>
						<?endif;?>
					</div>
				</div>
			</div>
			<?$startIndex++;?>
		<?endforeach;?>
		<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
			<br /><?=$arResult["NAV_STRING"]?>
		<?endif;?>
	</div>

<?endif;?>
<div class="margin-top-15"><?=$arResult["SECTION"]["PATH"][0]["DESCRIPTION"];?></div>
