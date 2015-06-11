<?php
/**
 * Bitrix vars
 * @global array $iblockElementInfo
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 * @global array $iblockElementInfo
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $APPLICATION;

use Bitrix\Main;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Engine;
use Bitrix\Seo\Adv;

Loc::loadMessages(dirname(__FILE__).'/../seo_adv.php');

$engine = new Engine\YandexDirect();
$bNeedAuth = !$engine->getAuthSettings();

try
{
	$currentUser = $engine->getCurrentUser();
}
catch(Exception $e)
{
	$currentUser = null;
	$bNeedAuth = true;
}

if($bNeedAuth)
{
	if(!defined('BX_PUBLIC_MODE') || !BX_PUBLIC_MODE)
	{
		$message = new CAdminMessage(array(
			"TYPE" => "OK",
			"DETAILS" => Loc::getMessage("SEO_ERROR_NO_YANDEX_AUTH", array(
				"#LANGUAGE_ID#" => LANGUAGE_ID,
			)),
			"HTML" => true
		));
		echo $message->Show();
	}
	else
	{
		echo BeginNote().Loc::getMessage("SEO_ERROR_NO_YANDEX_AUTH", array(
				"#LANGUAGE_ID#" => LANGUAGE_ID,
			)).EndNote();
	}

	return;
}

$request = Main\Context::getCurrent()->getRequest();

$dbRes = Adv\YandexCampaignTable::getList(array(
	"order" => array("NAME" => "asc"),
	"filter" => array(
		'=ACTIVE' => Adv\YandexCampaignTable::ACTIVE,
		'=ENGINE_ID' => $engine->getId(),
	),
	'select' => array(
		"ID", "NAME", "XML_ID", "OWNER_ID", "SETTINGS"
	)
));
$campaignList = array();

while($campaign = $dbRes->fetch())
{
	if($campaign['OWNER_ID'] == $currentUser['id'])
	{
		$campaignList[$campaign['ID']] = $campaign;
	}
}

if(count($campaignList) <= 0)
{
	if(!defined('BX_PUBLIC_MODE') || !BX_PUBLIC_MODE)
	{
		$message = new CAdminMessage(array(
			"TYPE" => "OK",
			"DETAILS" => Loc::getMessage("SEO_ERROR_NO_CAMPAIGNS", array(
				"#LANGUAGE_ID#" => LANGUAGE_ID,
			)),
			"HTML" => true
		));
		echo $message->Show();
	}
	else
	{
		echo BeginNote().Loc::getMessage("SEO_ERROR_NO_CAMPAIGNS", array(
				"#LANGUAGE_ID#" => LANGUAGE_ID,
			)).EndNote();
	}

?>
<a href="/bitrix/admin/seo_search_yandex_direct_edit.php?lang=<?=LANGUAGE_ID?>&back_url=<?=urlencode($APPLICATION->GetCurPageParam('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab=seo_adv_seo_adv', array('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab')))?>"><?=Loc::getMessage("SEO_CREATE_NEW_CAMPAIGN")?></a>
<?
}
else
{
	$dbRes = Adv\LinkTable::getList(array(
		"filter" => array(
			'LINK_TYPE' => Adv\LinkTable::TYPE_IBLOCK_ELEMENT,
			'LINK_ID' => $iblockElementInfo['ID'],
			"BANNER.ENGINE_ID" => $engine->getId(),
		),
		"select" => array(
			"BANNER_ID", "BANNER_NAME" => "BANNER.NAME", "BANNER_XML_ID" => "BANNER.XML_ID",
			"BANNER_CAMPAIGN_ID" => "BANNER.CAMPAIGN_ID",
		)
	));

	$arBanners = array();
	while($banner = $dbRes->fetch())
	{
		if(!isset($arBanners[$banner['BANNER_CAMPAIGN_ID']]))
		{
			$arBanners[$banner['BANNER_CAMPAIGN_ID']] = array();
		}

		$arBanners[$banner['BANNER_CAMPAIGN_ID']][] = $banner;
	}

	?>
	<table class="internal" width="100%">
		<thead>
		<tr class="heading">
			<td colspan="2"><?=Loc::getMessage('SEO_YANDEX_LINK_TITLE')?></td>
		</tr>
		</thead>
		<tbody id="adv_banner_selector">
		<tr>
			<td width="40%"><?=Loc::getMessage("SEO_CAMPAIGN_CHOOSE")?>:</td>
			<td width="60%">
				<select id="seo_adv_campaign" style="width:400px" onchange="updateNewBannerLink()">
					<option value="0"><?=Loc::getMessage("SEO_CAMPAIGN_CHOOSE_OPTION")?></option>
					<?
					foreach($campaignList as $campaign)
					{
						$canAdd = in_array(
							$campaign["SETTINGS"]['Strategy']['StrategyName'],
							Adv\YandexCampaignTable::$supportedStrategy
						);
						?>
						<option value="<?=$campaign["ID"]?>"
							data-add="<?=$canAdd ? 1 : 0?>"><?=Converter::getHtmlConverter()->encode($campaign["NAME"])?></option>
					<?
					}
					?>
				</select>&nbsp;&nbsp;<a
					href="/bitrix/admin/seo_search_yandex_direct_edit.php?lang=<?=LANGUAGE_ID?>&back_url=<?=urlencode($APPLICATION->GetCurPageParam('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab=seo_adv_seo_adv', array('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab')))?>"><?=Loc::getMessage("SEO_CREATE_NEW_CAMPAIGN")?></a>
			</td>
		</tr>
		<tr>
			<td><?=Loc::getMessage("SEO_BANNER_CHOOSE")?>:</td>
			<td>
				<select id="seo_adv_banner" style="width:400px" disabled="disabled"
					onchange="BX('seo_adv_link_btn').disabled=this.value<=0">
					<option value="0"><?=Loc::getMessage("SEO_CAMPAIGN_CHOOSE_OPTION")?></option>
				</select>&nbsp;&nbsp;<a id="adv_banner_link"
					href="/bitrix/admin/seo_search_yandex_direct_banner_edit.php?lang=<?=LANGUAGE_ID?>&element=<?=$iblockElementInfo['ID']?>&back_url=<?=urlencode($APPLICATION->GetCurPageParam('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab=seo_adv_seo_adv', array('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab')));?>" style="display: none;"><?=Loc::getMessage('SEO_CREATE_NEW_BANNER')?></a>
				<small id="adv_banner_strategy_warning"
					style="display: none;"><?=Loc::getMessage('SEO_YANDEX_STRATEGY_WARNING')?></small>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><input type="button" class="adm-btn-save" value="<?=Loc::getMessage("SEO_BANNER_LINK_CREATE")?>"
					id="seo_adv_link_btn" disabled="disabled" onclick="createLink()"></td>
		</tr>
		</tbody>
	</table>
<script>
	var bannerList = {};

	function updateNewBannerLink()
	{
		var campaignSelect = BX('seo_adv_campaign');
		var bannerSelect = BX('seo_adv_banner');
		var bannerWarning = BX('adv_banner_strategy_warning');
		var submitBtn = BX('seo_adv_link_btn');

		var campaign = campaignSelect.value;
		var link = BX('adv_banner_link');

		submitBtn.disabled = true;
		bannerWarning.style.display = 'none';

		if (campaign > 0 && typeof bannerList[campaign] == 'undefined')
		{
			campaignSelect.disabled = true;
			bannerSelect.disabled = true;

			BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php?action=banners_get&campaign=' + campaign + '&sessid=' + BX.bitrix_sessid(), function (result)
			{
				bannerList[campaign] = result;
				updateNewBannerLink();
			});
		}
		else
		{
			campaignSelect.disabled = false;

			bannerSelect.disabled = true;
			link.style.display = 'none';

			bannerSelect[0].text = '<?=Loc::getMessage('SEO_CAMPAIGN_CHOOSE_OPTION')?>';

			if (campaign > 0)
			{
				link.style.display = 'inline';
				bannerSelect.disabled = false;

				var option = campaignSelect.options[campaignSelect.selectedIndex];
				if (option.getAttribute('data-add') == '0')
				{
					link.style.display = 'none';
					bannerWarning.style.display = 'inline';
				}

				if (!link._href)
				{
					link._href = link.href;
				}

				link.href = link._href + '&campaign=' + campaign;

				while (bannerSelect.length > 1)
				{
					bannerSelect.remove(1);
				}

				if (!bannerList[campaign].error)
				{
					bannerSelect[0].text = '<?=Loc::getMessage('SEO_BANNER_CHOOSE_OPTION')?>';

					for (var i = 0; i < bannerList[campaign].length; i++)
					{
						bannerSelect.add(BX.create('OPTION', {
							props: {
								text: bannerList[campaign][i].NAME,
								value: bannerList[campaign][i].ID
							}
						}));
					}
				}
			}
			else
			{
				bannerSelect.selectedIndex = 0;
			}
		}
	}

	function createLink()
	{
		var campaignSelect = BX('seo_adv_campaign');
		var bannerSelect = BX('seo_adv_banner');
		var submitBtn = BX('seo_adv_link_btn');

		if (bannerSelect.value > 0)
		{
			campaignSelect.disabled = true;
			bannerSelect.disabled = true;

			BX.adminPanel.showWait(submitBtn);

			BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php', {
				action: 'link_create',
				banner: bannerSelect.value,
				link: <?=$iblockElementInfo['ID']?>,
				link_type: '<?=Adv\LinkTable::TYPE_IBLOCK_ELEMENT?>',
				get_list_html: 1,
				sessid: BX.bitrix_sessid()
			}, function (res)
			{
				campaignSelect.disabled = false;
				bannerSelect.disabled = false;

				if(res.result)
				{
					//BX('adv_banner_selector').style.display = 'none';
					BX('adv_banner_list').innerHTML = res.list_html;
				}
			})
		}
	}
</script>

<div id="adv_banner_list">
<?
	require(dirname(__FILE__)."/seo_search_yandex_direct_list_link.php");
?>
</div>
<?
}

