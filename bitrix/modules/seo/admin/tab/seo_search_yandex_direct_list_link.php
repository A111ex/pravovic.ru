<?php
/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 * @global array $iblockElementInfo
 * @global array $campaignList
 * @global array $arBanners
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Text\Converter;

Loc::loadMessages(dirname(__FILE__).'/../seo_adv.php');

if(count($campaignList) > 0)
{
	if(count($arBanners) > 0)
	{
?>
<style>
.adv-links-list
{
	margin-top: 20px;
}

.adv-campaign-list
{
	padding-left: 25px;
}


span.yandex-delete {
	display: inline-block;
	height: 20px;
	width: 20px;
	cursor: pointer;
	background: url("/bitrix/panel/main/images/bx-admin-sprite-small-1.png") no-repeat scroll 5px -2446px rgba(0, 0, 0, 0);
}

.adv-banner-link,
.adv-campaign-link
{
	display: inline-block;
	height: 20px;
	vertical-align: top;
	margin-top: 2px;
}
</style>
<div id="adv_links_list" class="adv-links-list">
	<b><?=Loc::getMessage('SEO_YANDEX_DIRECT_BANNER_LINKS')?></b>
<?
		foreach($arBanners as $campaignId => $campaignBanners)
		{
			if(isset($campaignList[$campaignId]))
			{
?>
	<div class="adv-campaign-item">
		<a href="/bitrix/admin/seo_search_yandex_direct_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$campaignId?>&back_url=<?=Converter::getHtmlConverter()->encode(urlencode($APPLICATION->GetCurPageParam('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab=seo_adv_seo_adv', array('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab'))))?>" class="adv-campaign-link"><?=Converter::getHtmlConverter()->encode($campaignList[$campaignId]['NAME']);?></a>
		<div class="adv-campaign-list">
<?
				foreach($campaignBanners as $banner)
				{
?>
			<div class="adv-banner-item">
				<a href="/bitrix/admin/seo_search_yandex_direct_banner_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$banner['BANNER_ID']?>&element=<?=$iblockElementInfo['ID']?>&back_url=<?=Converter::getHtmlConverter()->encode(urlencode($APPLICATION->GetCurPageParam('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab=seo_adv_seo_adv', array('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab'))))?>" class="adv-banner-link"><?=Loc::getMessage('SEO_YANDEX_DIRECT_BANNER_LINK_TPL', array(
							"#XML_ID#" => $banner['BANNER_XML_ID'],
							'#NAME#' => $banner['BANNER_NAME'],
						))?></a>&nbsp;<span class="yandex-delete" onclick="deleteLink('<?=$banner['BANNER_ID']?>', this)"></span>
			</div>
<?
				}
?>
	</div></div>
<?
			}
		}
?>
</div>
<?

	}
?>
	<script>
		function deleteLink(bannerId, el)
		{
			if(!el._loading)
			{
				el._loading = true;
				el.style.background = 'url("/bitrix/panel/main/images/waiter-small-white.gif") no-repeat scroll center center';

				BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php?action=link_delete&banner='+bannerId+'&link=<?=$iblockElementInfo["ID"]?>&link_type=<?=\Bitrix\Seo\Adv\LinkTable::TYPE_IBLOCK_ELEMENT?>&get_list_html=1&sessid='+BX.bitrix_sessid(), function(res)
				{
					if(res.result)
					{
						BX('adv_banner_list').innerHTML = res.list_html;
					}
				});
			}
		}
	</script>
<?
}