<?php
/**
 * Blog Configuration Editor
 * 
 * @package Sevida
 * @subpackage Administration
 */
 /**
  * @var bool
  */
define( 'SE_HTML', true );

/** Load the blog bootstrap file and utilities */
require( dirname(__DIR__) . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$config = $db->query( 'SELECT * FROM config' );
$config = $config->fetchAll( PDO::FETCH_KEY_PAIR );
$config = array_map( 'escHtml', $config );
$config = new Config( $config );
$config->searchable = checked( $config->searchable === 'true' );

$_page = new Page( 'Installation', USERPATH . '/config.php' );
include( 'html-header.php' );
?>
<nav aria-role="navigation">
    <ul class="breadcrumb my-3">
        <li class="breadcrumb-item"><a href="<?=USERPATH.'/'?>">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Settings</li>
    </ul>
</nav>
<div class="col-sm-6 offset-sm-3 col-xl-4 offset-xl-4 mb-3">
	<div class="card bg-light text-dark">
		<h3 class="card-header">Edit Configuration</h3>
		<div class="card-body">
			<form id="config" class="needs-validation" novalidate>
				<input type="hidden" name="action" value="modify" />
                <div class="alert alert-danger d-none text-center"></div>
				<h4>Blog Information</h4>
				<div class="mb-3">
					<label class="form-label" for="blogName">Blog Name</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('pen')?></div>
						<input class="form-control" type="text" id="blogName" name="blogName" value="<?=$config->blogName?>" required minlength="5" maxlength="15" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="blogEmail">Blog Contact Email</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('at')?></div>
						<input class="form-control" type="email" id="blogEmail" name="blogEmail" value="<?=$config->blogEmail?>" required minlength="5" maxlength="25" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="blogDesc">Blog Description</label>
					<textarea class="form-control" id="blogDesc" name="blogDesc" rows="3" maxlength="120"><?=$config->blogDesc?></textarea>
					<div class="form-text">Describe your blog in 120 charachters</div>
				</div>
				<div class="mb-3 form-check">
					<input id="searchable" class="form-check-input" type="checkbox" name="searchable" value="true"<?=$config->searchable?> />
					<label for="searchable" class="form-check-label">Allow search engines to crawl this site</label>
				</div>
                <h4>Post & Permalinks</h4>
                
				<button name="submit" type="submit" class="btn btn-primary float-end">Submit</button>
			</form>
		</div>
	</div>
</div>
<?php
include( 'html-footer.php' );
