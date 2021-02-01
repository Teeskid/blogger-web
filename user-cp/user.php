<?php
/**
 * Project: Blog Management System With Sevida-Like UI
 * Developed By: Ahmad Tukur Jikamshi
 *
 * @facebook: amaedyteeskid
 * @twitter: amaedyteeskid
 * @instagram: amaedyteeskid
 * @whatsapp: +2348145737179
 */
require( __DIR__ . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

/**
 * load the tab index from get-array
 * @var string
 */
$tab = $_GET['tab'] ?? 'all';
$tabs_data = [
	'all' => ['title' => 'All', 'query' => 'REGEXP \'.*\''],
	'admin' => ['title' => 'Admin', 'query' => 'REGEXP \'^5$\''],
	'author' => ['title' => 'Author', 'query' => 'REGEXP \'^4$\''],
];
$current_tab = $tabs_data[$tab] ?? false;
if( !$current_tab ) {
	_redirect( BASEURI . '/404.php' );
}
$query = sprintf(<<<'EOS'
SELECT
	COUNT(a.id) AS all ,
	COUNT(b.id) AS admin,
	COUNT(c.id) AS author
FROM
	%s a,
	%s b,
	%s c
WHERE
	a.role %s AND
	b.role %s AND
	c.role %s
EOS
, users, users, users, $tabs_data['all']['query'], $tabs_data['admin']['query'], $tabs_data['author']['query']);

$query = $_db->query($query)->fetch(PDO::FETCH_ASSOC);
foreach( $query as $y => &$z )  {
	$tabs_data[$y]['count'] = parseInt($z);
}
unset($y, $z, $query);
$current_tab = $tabs_data[$tab];

$paging = new Paging( 20, $current_tab['count'] );

$query = sprintf('SELECT id,userName,role FROM %s WHERE role %s ORDER BY id ASC LIMIT %s', users, $current_tab['query'], $paging->getLimit());
$query = $_db->query($query);

initHtmlPage( 'Users', sprintf('/user-cp/user-cp.php?tab=%s&page=%s', $tab, $paging->pageNow) );
$HTML->head = <<<EOS
<link rel="stylesheet" media="all" href="css/admin.css" />
EOS;
include_once( __DIR__ . '/header.php' );
if(isset($_GET['admin-added'])){
	alert();
}
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb my-3">
		<li class="breadcrumb-item"><a href="index.php" class="breadcrumb">Home</a></li>
		<li class="breadcrumb-item active" aria-current="page">Users</li>
	</div>
</nav>
<div class="card">
	<div class="card-tabs">
		<ul class="tabs tabs-fixed-width">
<?php
foreach($tabs_data as $tab_index => $tab_data){
	if($current_tab === $tab_data) {
?>
	<li class="tab"><a href="#" class="active"><?=$tab_data['title']?> <span class="badge">(<?=$tab_data['count']?>)</span></a></li>
<?php
	} else {
?>
	<li class="tab"><a target="_self" href="?tab=<?=$tab_index?>"><?=$tab_data['title']?> <span class="badge">(<?=$tab_data['count']?>)</span></a></li>
<?php
	}
}
?>
		</ul>
	</div>
	<div class="card-body">
		<span class="card-title">Users</span>
		<?php
if( $query->rowcount() ) {
?>
		<ul class="selectable collection">
		<?php
		$icon_edit = icon('edit');
		$icon_trash = icon('trash');
		$icon_role = icon('tickets-alt');
		while($user = $query->fetch()) {
		?>
			<li class="collection-item avatar" data-id="<?=$user->id?>">
				<input type="checkbox" /><br/>
				<span class="circle blue di di-admin-users"></span>	
				<a href="#" data-link="view"><h6 class="title"><?=$user->userName?></h6></a>
				<p class="action">
					<a href="#" data-link="edit"><?=$icon_edit?></a>
					<?php
if( $user->id !== $_user ) {
?>
					<a href="#" data-link="role" class="green-text"><?=$icon_role?></a>
					<a href="#" data-link="unlink" class="red-text"><?=$icon_trash?></a>
					<?php
}
?>
				</p>
				<?=($user->role === ADMIN_LEVEL_GLOBAL ? '' : '')?>
			</li>
		<?php
}
?>
		</ul>
		<?php } else {
?>
		<div class="card-panel center green lighten-4">No data available</div>
		<?php
}
?>
		<?=$paging->html($HTML->url)?>
	</div>
</div>
<div class="fixed-action-btn">
	<a href="user-create.php" class="btn-floating btn-large pulse"><?=icon('plus')?></a>
</div>
<?php
$HTML->readyjs = <<<'EOS'
document.addEventListener("DOMContentLoaded", function(){
	M.Tabs.init(document.querySelector(".tabs"));
	M.FloatingActionButton.init(document.querySelector(".fixed-action-btn"));
	M.Modal.init(document.querySelector(".modal"), {'endingTop':'4%'});
	T.SimpleAnchor.init(".collection", "user-%.php?id=%");
	T.Selectable.init(".collection", {
		"trash": function(data) {
			T.Ajax.init({
				type: "post",
				url: "user-delete.php",
				data: {"id": data, "async": true, "submit": true},
				success: function(r){
					var parent = document.querySelector(".collection"), elem;
					data.forEach(function(id){
						elem = parent.querySelector("[data-id='" + id + "']");
						parent.removeChild(elem);
					});
				}
			});
		}
	});
	T.FabPulse.init(".pulse");
});
EOS;
include_once( __DIR__ . '/footer.php' );
