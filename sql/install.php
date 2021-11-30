<?php
/**
* 2007-2019 PrestaShop.
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$sql = array();
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ban_ipaddress` (
    `id_ban_ipaddress` int(11) NOT NULL AUTO_INCREMENT,
    `ip_address` varchar(25) NOT NULL COMMENT "ipaddress, ip address range",
    `blocking_type` varchar(25) DEFAULT "ipblock" COMMENT "rangeblock, ipblock",
    `ip_type` varchar(25) DEFAULT "ipv4" COMMENT "ipv4,ipv6",
    `date_add` datetime DEFAULT CURRENT_TIMESTAMP,
    `status` tinyint(1) NOT NULL DEFAULT "1" COMMENT "1,0",
    PRIMARY KEY (`id_ban_ipaddress`),
    KEY `ip_address` (`ip_address`),
    KEY `blocking_type` (`blocking_type`),
    KEY `ip_type` (`ip_type`)
  ) ENGINE='._MYSQL_ENGINE_.'  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
