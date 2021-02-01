<?php
/**
 * Blog Configuration Editor
 * @package Sevida
 * @subpackage Administration
 */
 /**
  * @var bool
  */
define( 'SE_HTML', true );

/** Load the blog bootstrap file and utilities */
require( __DIR__ . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

/** Fetch active blog configuration */
$config = $_db->query( 'SELECT * FROM Config' );
$config = $config->fetchAll( PDO::FETCH_KEY_PAIR );
$config = array_map( 'escHtml', $config );
$config = new Config( $config );
$config->searchable = checked( $config->searchable === 'true' );

initHtmlPage( 'Blog Configuration', USERURI . '/config.php' );
include_once( __DIR__ . '/header.php' );
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb my-3">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Blog Configuration</li>
    </ol>
</nav>
<div class="col-sm-7 col-md-6 col-lg-5 col-xl-4 mb-3 mx-auto">
    <div class="card bg-white text-dark">
        <h2 class="card-header">Blog Configuration</h2>
        <div class="card-header">
            <nav id="tabs" class="card-header-tabs nav nav-pills nav-justified" role="tablist">
                <a href="#global" class="nav-link active" data-bs-toggle="pill" role="tab" aria-controls="global">General</a>
                <a href="#setPost" class="nav-link" data-bs-toggle="pill" role="tab" aria-controls="setPost">Post</a>
                <a href="#doReset" class="nav-link" data-bs-toggle="pill" role="tab" aria-controls="doReset">Reset</a>
            </nav>
        </div>
        <div class="card-body">
            <form id="configForm" method="post" action="#" autocomplete="off">
                <input type="hidden" id="action" name="action" value="modify" />
                <div class="alert alert-danger d-none text-center"></div>
                <div class="tab-content">
                    <div id="global" class="tab-pane fade show active" role="tabpanel">
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
                    </div>
                    <div id="setPost" class="tab-pane" role="tabpanel">
                        <div class="h4">Post Permalink</div>
                        <?php
                        $mDate = date( 'Y|m' );
                        $mDate = explode( '|', $mDate );
                        foreach( Rewrite::POST_SYNTAX as $index => $entry ) {
                            $entry = str_replace( '%year%', $mDate[0], $entry );
                            $entry = str_replace( '%month%', $mDate[1], $entry );
                            $entry = str_replace( '%name%', 'sample-post', $entry );
                            $entry = escHtml( ROOTURL . BASEURI . $entry );
                            $domId = 'permalink_' . $index;
                            $check = checked( $index === $_cfg->permalink );
                        ?>
                        <div class="mb-3 form-check">
                            <input class="form-check-input" id="<?=$domId?>" type="radio" name="permalink" value="<?=$index?>"<?=$check?> />
                            <label class="form-check-label" for="<?=$domId?>"><?=$entry?></label>
                        </div>
                        <?php
                        }
                        ?>
                        <div class="h4">Post Date</div>
                        <?php
                        $DATE_SYNTAX = [ 'Y, F d', 'd-m-Y', 'F d, Y' ];
                        foreach(  $DATE_SYNTAX as $index => $entry ) {
                            $domId = 'blogDate_' . $index;
                            $check = checked( $entry === $_cfg->blogDate );
                            $index = date( $entry );
                        ?>
                        <div class="mb-3 form-check">
                            <input class="form-check-input" id="<?=$domId?>" type="radio" name="blogDate" value="<?=$entry?>"<?=$check?> />
                            <label class="form-check-label" for="<?=$domId?>"><?=$index?></label>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                    <div id="doReset" class="tab-pane" role="tabpanel">
                        <div class="alert alert-danger"><strong class="me-1">NOTE:</strong>Resetting your blog clears all the data added since the first run. So it's recommended you run a <a href="backup.php" class="alert-link">backup</a> before resetting the blog</div>
                        <div class="alert alert-info">To continue reset, pease confirm your password</div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Confirm Password</label>
                            <input class="form-control" type="password" id="password" name="password" />
                        </div>
                        <div class="mb-3 form-check">
                            <input class="form-check-input" id="noFiles" type="checkbox" name="noFiles" value="true" />
                            <label class="form-check-label" for="noFiles">Leave user uploaded files</label>
                        </div>
                    </div>
                </div>
                <button id="submit" name="submit" type="submit" class="btn btn-primary float-end"><?=icon('check me-1')?> Submit</button>
            </form>
        </div>
    </div>
</div>
<?php
addPageJsFile( 'js/async-form.js' );
function onPageJsCode() {
?>
document.addEventListener("DOMContentLoaded", function() {
	var asyncForm = AsyncForm(document.getElementById("configForm"), {
		url: "../api/config.php", success: function(response) {
            if(response.type)
                if(window.localStorage)
                    delete localStorage.authToken;
            window.location = "";
            return true;
        }
    });
    document.querySelectorAll('nav#tabs a[data-bs-toggle="pill"]').forEach(function(tabElem) {
        tabElem.addEventListener("show.bs.tab", function(event){
            var element = document.getElementById("action");
            switch(event.target.hash) {
                case "#doReset":
                    element.value = "reset";
                    break;
                default:
                    element.value = "modify";
            }
        });
    });
});
<?php
}
include_once( __DIR__ . '/footer.php' );
