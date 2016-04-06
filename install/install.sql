CREATE TABLE IF NOT EXISTS `_PREFIX_dpdcarrier_pickup` (
  `id_cart` int(10) NOT NULL,
  `id_carrier` int(10) NOT NULL,
  `id_location` varchar(16) NOT NULL,
  `lat` FLOAT(12,10) NOT NULL,
  `lng` FLOAT(12,10) NOT NULL,
  `name` varchar(128) NOT NULL,
  `address` varchar(128) NOT NULL,
  `city` varchar(128) NOT NULL,
  `postcode` varchar(16) NOT NULL,
  `iso_code` varchar(2) NOT NULL,
  PRIMARY KEY (`id_cart`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `ps_dpdcarrier_label` (
  `id_dpdcarrier_label` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(10) NOT NULL,
  `parcel_number` varchar(14) NOT NULL,
  `date` datetime NOT NULL,
  `weight` float(10,4) NOT NULL,
  `length` float(10,4) NOT NULL,
  `height` float(10,4) NOT NULL,
  `depth` float(10,4) NOT NULL,
  `value` float(10,2) NOT NULL,
  `id_location` varchar(16) NOT NULL,
  `services` mediumtext NOT NULL,
  `shipped` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_dpdcarrier_label`),
  UNIQUE KEY `ORDER_PARCEL` (`id_order`,`parcel_number`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;