CREATE TABLE IF NOT EXISTS `_PREFIX_dpdcarrier_pickup` (
  `id_cart` int(10) NOT NULL,
  `id_carrier` int(10) NOT NULL,
  `id_location` varchar(16) NOT NULL,
  PRIMARY KEY (`id_cart`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;