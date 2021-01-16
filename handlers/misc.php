<?php
$_basePath = escHtml(BASEPATH);
$_baseUrl = escHtml(BASE_URL) . $_basePath;
switch( $_VARS['file'] ) {
	case 'rss.xml':
		header( 'Content-Type: text/xml;charset=utf-8', true );
		break;
	case 'sitemap.xml':
		$urlset = [];

		$lastMod = date('Y-M-d h:i:s');

		$urlset[] = [ 'loc' => '/', 'priority' => 1, 'lastmod' => $lastMod ];
		$urlset[] = [ 'loc' => '/about-us', 'priority' => 1, 'lastmod' => $lastMod ];
		$urlset[] = [ 'loc' => '/contact-us', 'priority' => 1, 'lastmod' => $lastMod ];
		$urlset[] = [ 'loc' => '/privacy-policy', 'priority' => 1, 'lastmod' => $lastMod ];
		$urlset[] = [ 'loc' => '/sponsor-post', 'priority' => 1, 'lastmod' => $lastMod ];
		$urlset[] = [ 'loc' => '/menu', 'priority' => 1, 'lastmod' => $lastMod ];
		$urlset[] = [ 'loc' => '/search', 'priority' => 1, 'lastmod' => $lastMod ];
		$urlset[] = [ 'loc' => '/bb-code', 'priority' => 1, 'lastmod' => $lastMod ];

		$mQuery = $db->query( 'SELECT rowType, title, permalink FROM Term ORDER BY rowType ASC, id DESC' );
		$mQuery = $mQuery->fetchAll( PDO::FETCH_CLASS, 'Term' );
		foreach( $mQuery as $entry ) {
		  $urlset[] = [
			'loc' => Rewrite::termUri( $entry ),
			'priority' => '0.9',
			'lastmod' => $lastMod
		  ];
		}

		$mQuery = $db->prepare( 'SELECT id, permalink, posted FROM Post WHERE status=? ORDER BY posted DESC' );
		$mQuery->execute( [ 'public' ] );
		$mQuery = $mQuery->fetchAll();
		foreach( $mQuery as $entry ) {
			$entry->posted = parseDate( $entry->posted );
			$urlset[] = [
				'loc' => Rewrite::postUri( $entry ),
				'priority' => 0.8,
				'lastmod' => '', // $entry->posted->format('Y-M-d H:i:s')
			];
		}

		header( 'Content-Type: text/xml;charset=utf-8', true );
		echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0">' . PHP_EOL;
		foreach( $urlset as $url ) {
			$url['loc'] = escHtml( BASE_URL . BASEPATH . $url['loc'] . '/' );
			$url['lastmod'] = escHtml( $url['lastmod'] );
			echo "\t<url>\n";
			foreach( $url as $k => $v )
				echo "\t\t<$k>$v</$k>\n";
			echo "\t</url>\n";
		}
		echo "</urlset>";
	break;
}