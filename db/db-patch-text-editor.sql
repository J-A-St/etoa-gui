CREATE TABLE `texts` (
 `text_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 `text_content` text COLLATE utf8_unicode_ci NOT NULL,
 `text_updated` int(10) unsigned NOT NULL,
 PRIMARY KEY (`text_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
