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
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/UIUtil.php' );

if(isset($_POST['import']))
{
	// INSTALL PENDING BACKUP
	$post = file_get_contents($_FILES['article']['tmp_name']);
	$post = json_decode($post);
	$post->permalink = prepare_makePermalink($post->permalink?$post->permalink:$post->title);
	$post->excerpt = makeExcerpt($post->excerpt?$post->excerpt:$post->content);
	$post->posted = formatDate(time());
	$post->state = (int) $post->state;
	$exist = $db->quote($post->permalink);
	try
	{
		$exist = $db->query( 'SELECT COUNT(*) FROM Post WHERE permalink=$exist' )->fetchColumn();
		if($exist) throw (new Exception('Post already exist !'));
	}
	catch(Exception $e)
	{
		die($e->getMessage());
	}
	try
	{
		$stmt = $db->prepare( 'INSERT INTO Post SET title=?,permalink=?,categoryID=?,excerpt=?,content=?,state=?,posted=?,dateModified=?' );
		$bool = $stmt->execute([$post->title,$post->permalink,$post->categoryID,$post->excerpt,$post->content,$post->state,$post->posted,$post->posted]);
		header('Location:index.php');
		exit;
	}
	catch(Exception $e)
	{
	}
}
$page = new page();
$page->title = 'Admin Panel';
$page->style = <<<CSS
select.browser-default {
	max-width:400px;
	border:1px solid #BFBFBF;
	margin:20px auto;
}
CSS;
$page->foo = <<<FOO
<ul class="nav-mobile right">
	<li><a href="index.php">CLOSE</a></li>
</ul>
FOO;
$options = $db->query( 'SELECT title,category FROM categories' )->fetchAll();
$options = array_merge(([(object) ['category'=>0,'title'=>'Uncategorized']]), $options);
foreach($options AS &$option)
{
	$option = '<option value="'.$option->category.'">'.$option->title.'</option>';
}
$options = implode($options);
include_once( ABSPATH . USER_UTIL . '/HeadHtml.php' );
?>
<div class="card">
	<div class="card-content">
		<span class="card-title">Import Article</span>
		<div class="divider"></div>
		<form action="<?=$_SERVER['REQUEST_URI'];?>" method="post" enctype="multipart/form-data">
			<div class="input-field">
				<select name="categoryID" id="categoryID">
					<option value="" selected disabled>SELECT CATEGORY</option>
					<?=$options;?>
				</select>
				<label for="categoryID">ARTICLE CATEGORY</label>
			</div>
			<div class="input-field">
				<input type="file" class="browser-default" name="article" />
			</div>
			<div class="input-field text-center">
				<a href="index.php" class="btn-large grey white-text">CANCEL</a>
				<button type="submit" class="btn-large teal white-text" name="import">IMPORT</button>
			</div>
		</form>
	</div>
</div>
<?php
$_page->readyjs = <<<JS
$(document).ready(function(){
	$('select').formSelect();
});
JS;
include( 'html-footer.php' );
