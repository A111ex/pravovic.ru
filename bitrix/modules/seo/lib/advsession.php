<?php
namespace Bitrix\Seo;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\BasketTable;
use Bitrix\Seo\Adv\LinkTable;
use Bitrix\Seo\Adv\YandexBannerTable;
use Bitrix\Seo\Engine\YandexDirect;

Loc::loadMessages(__FILE__);

/**
 * Class AdvSession
 *
 * Events handler for managing users came from yandex.direct ad
 *
 * @package Bitrix\Seo
 **/
class AdvSession
{
	const URL_PARAM_CAMPAIGN = 'bxydcampaign';
	const URL_PARAM_CAMPAIGN_VALUE = '{campaign_id}';
	const URL_PARAM_BANNER = 'bxydbanner';
	const URL_PARAM_BANNER_VALUE = '{banner_id}';

	public static function checkSession()
	{
		$request = Main\Context::getCurrent()->getRequest();
		if(
			isset($request[static::URL_PARAM_CAMPAIGN])
			&& isset($request[static::URL_PARAM_BANNER])
		)
		{
			$dbRes = YandexBannerTable::getList(array(
				'filter' => array(
					'=XML_ID' => $request[static::URL_PARAM_BANNER],
					'=ENGINE.CODE' => YandexDirect::ENGINE_ID,
				),
				'select' => array(
					'ID', 'CAMPAIGN_ID'
				)
			));

			$banner = $dbRes->fetch();

			if($banner)
			{
				$_SESSION['SEO_ADV'] = array(
					"ENGINE" => YandexDirect::ENGINE_ID,
					"CAMPAIGN_ID" => $banner['CAMPAIGN_ID'],
					"BANNER_ID" => $banner['ID'],
				);
			}
		}
	}
}