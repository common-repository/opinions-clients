<?php
defined( 'ABSPATH' ) or die();

add_action( 'wp_ajax_opcl_js_sendOpinion', 'opcl_js_sendOpinion' );
add_action( 'wp_ajax_opcl_js_displayComment', 'opcl_js_displayComment' );

function opcl_js_sendOpinion(){
	global $wpdb; //test
	$action = str_replace('opcl_js_','',$_POST['action']);
	if ($action != 'sendOpinion'){
		wp_die();
	}
	wp_verify_nonce($action,'opcl_'.$action);
	$opcl_options = opcl_getOptions();
	$idpost = intval($_POST['opcl_footer_button_idpost']);
	//echo $_POST['opcl_footer_textarea'];
	$text = sanitize_textarea_field($_POST['opcl_footer_textarea']);
	//echo $text;
	if ((strlen($text) == 0) || ($idpost <= 0)){
		echo JSON_encode(__('An error occured, please try again later!', 'opinions-clients'),JSON_FORCE_OBJECT|JSON_UNESCAPED_UNICODE);
		wp_die();
	}
	if ($opcl_options['pause_commentaires'] > 0){
		$resultats = $wpdb->get_results("SELECT date_commentaire FROM {$wpdb->prefix}ocp_commentaires ORDER BY date_commentaire DESC limit 1");
		if (isset($resultats[0])){
			$date = strtotime(date('Y-m-d H:i:s'));
			$delai = ($date - strtotime($resultats[0]->date_commentaire)) / 60;
			if ($delai < $opcl_options['pause_commentaires']){
				$text = '';
			}
		}
	}
	if ($opcl_options['longueur_max_commentaires'] > 0){
		$text = substr($text, 0, $opcl_options['longueur_max_commentaires']);
	}
	if (strlen($text) > 0){
		$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ocp_commentaires(commentaire,id_post,date_commentaire,statut) VALUES (%s,%d,%s,%s)",$text,$idpost,date("Y-m-d H:i:s"),'new'));
		echo JSON_encode(__('Message sent! Thank you!', 'opinions-clients'),JSON_FORCE_OBJECT|JSON_UNESCAPED_UNICODE);
	}else{
		echo JSON_encode(__('An error occured, please try again later!', 'opinions-clients'),JSON_FORCE_OBJECT|JSON_UNESCAPED_UNICODE);
	}
	wp_die();
}

function opcl_js_displayComment(){
	global $wpdb;
	$action = str_replace('opcl_js_','',$_POST['action']);
	if ($action != 'displayComment'){
		wp_die();
	}
	wp_verify_nonce($action,'opcl_'.$action);
	$idComment = intval($_POST['idComment']);
	if ($idComment > 0){
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ocp_commentaires SET statut = '' WHERE id = %d",$idComment));
		$resultats = $wpdb->get_results($wpdb->prepare("SELECT commentaire FROM {$wpdb->prefix}ocp_commentaires WHERE id = %d",$idComment));
		if (isset($resultats[0])){
			echo JSON_encode($resultats[0]->commentaire);
		}
	}
	wp_die();
}

$opcl_iconBouton = array(
	'dashicons-admin-appearance',
	'dashicons-admin-collapse',
	'dashicons-admin-comments',
	'dashicons-admin-customizer',
	'dashicons-admin-generic',
	'dashicons-admin-home',
	'dashicons-admin-links',
	'dashicons-admin-media',
	'dashicons-admin-multisite',
	'dashicons-admin-network',
	'dashicons-admin-page',
	'dashicons-admin-plugins',
	'dashicons-admin-post',
	'dashicons-admin-settings',
	'dashicons-admin-site-alt',
	'dashicons-admin-site-alt2',
	'dashicons-admin-site-alt3',
	'dashicons-admin-site',
	'dashicons-admin-tools',
	'dashicons-admin-users',
	'dashicons-album',
	'dashicons-align-center',
	'dashicons-align-left',
	'dashicons-align-none',
	'dashicons-align-right',
	'dashicons-analytics',
	'dashicons-archive',
	'dashicons-arrow-down-alt',
	'dashicons-arrow-down-alt2',
	'dashicons-arrow-down',
	'dashicons-arrow-left-alt',
	'dashicons-arrow-left-alt2',
	'dashicons-arrow-left',
	'dashicons-arrow-right-alt',
	'dashicons-arrow-right-alt2',
	'dashicons-arrow-right',
	'dashicons-arrow-up-alt',
	'dashicons-arrow-up-alt2',
	'dashicons-arrow-up',
	'dashicons-art',
	'dashicons-awards',
	'dashicons-backup',
	'dashicons-book-alt',
	'dashicons-book',
	'dashicons-buddicons-activity',
	'dashicons-buddicons-bbpress-logo',
	'dashicons-buddicons-buddypress-logo',
	'dashicons-buddicons-community',
	'dashicons-buddicons-forums',
	'dashicons-buddicons-friends',
	'dashicons-buddicons-groups',
	'dashicons-buddicons-pm',
	'dashicons-buddicons-replies',
	'dashicons-buddicons-topics',
	'dashicons-buddicons-tracking',
	'dashicons-building',
	'dashicons-businessman',
	'dashicons-calendar-alt',
	'dashicons-calendar',
	'dashicons-camera',
	'dashicons-carrot',
	'dashicons-cart',
	'dashicons-category',
	'dashicons-chart-area',
	'dashicons-chart-bar',
	'dashicons-chart-line',
	'dashicons-chart-pie',
	'dashicons-clipboard',
	'dashicons-clock',
	'dashicons-cloud',
	'dashicons-controls-back',
	'dashicons-controls-forward',
	'dashicons-controls-pause',
	'dashicons-controls-play',
	'dashicons-controls-repeat',
	'dashicons-controls-skipback',
	'dashicons-controls-skipforward',
	'dashicons-controls-volumeoff',
	'dashicons-controls-volumeon',
	'dashicons-dashboard',
	'dashicons-desktop',
	'dashicons-dismiss',
	'dashicons-download',
	'dashicons-edit',
	'dashicons-editor-aligncenter',
	'dashicons-editor-alignleft',
	'dashicons-editor-alignright',
	'dashicons-editor-bold',
	'dashicons-editor-break',
	'dashicons-editor-code',
	'dashicons-editor-contract',
	'dashicons-editor-customchar',
	'dashicons-editor-expand',
	'dashicons-editor-help',
	'dashicons-editor-indent',
	'dashicons-editor-insertmore',
	'dashicons-editor-italic',
	'dashicons-editor-justify',
	'dashicons-editor-kitchensink',
	'dashicons-editor-ltr',
	'dashicons-editor-ol',
	'dashicons-editor-outdent',
	'dashicons-editor-paragraph',
	'dashicons-editor-paste-text',
	'dashicons-editor-paste-word',
	'dashicons-editor-quote',
	'dashicons-editor-removeformatting',
	'dashicons-editor-rtl',
	'dashicons-editor-spellcheck',
	'dashicons-editor-strikethrough',
	'dashicons-editor-table',
	'dashicons-editor-textcolor',
	'dashicons-editor-ul',
	'dashicons-editor-underline',
	'dashicons-editor-unlink',
	'dashicons-editor-video',
	'dashicons-email-alt',
	'dashicons-email-alt2',
	'dashicons-email',
	'dashicons-excerpt-view',
	'dashicons-external',
	'dashicons-facebook-alt',
	'dashicons-facebook',
	'dashicons-feedback',
	'dashicons-filter',
	'dashicons-flag',
	'dashicons-format-aside',
	'dashicons-format-audio',
	'dashicons-format-chat',
	'dashicons-format-gallery',
	'dashicons-format-image',
	'dashicons-format-quote',
	'dashicons-format-status',
	'dashicons-format-video',
	'dashicons-forms',
	'dashicons-googleplus',
	'dashicons-grid-view',
	'dashicons-groups',
	'dashicons-hammer',
	'dashicons-heart',
	'dashicons-hidden',
	'dashicons-id-alt',
	'dashicons-id',
	'dashicons-image-crop',
	'dashicons-image-filter',
	'dashicons-image-flip-horizontal',
	'dashicons-image-flip-vertical',
	'dashicons-image-rotate-left',
	'dashicons-image-rotate-right',
	'dashicons-image-rotate',
	'dashicons-images-alt',
	'dashicons-images-alt2',
	'dashicons-index-card',
	'dashicons-info',
	'dashicons-laptop',
	'dashicons-layout',
	'dashicons-leftright',
	'dashicons-lightbulb',
	'dashicons-list-view',
	'dashicons-location-alt',
	'dashicons-location',
	'dashicons-lock',
	'dashicons-marker',
	'dashicons-media-archive',
	'dashicons-media-audio',
	'dashicons-media-code',
	'dashicons-media-default',
	'dashicons-media-document',
	'dashicons-media-interactive',
	'dashicons-media-spreadsheet',
	'dashicons-media-text',
	'dashicons-media-video',
	'dashicons-megaphone',
	'dashicons-menu-alt',
	'dashicons-menu',
	'dashicons-microphone',
	'dashicons-migrate',
	'dashicons-minus',
	'dashicons-money',
	'dashicons-move',
	'dashicons-nametag',
	'dashicons-networking',
	'dashicons-no-alt',
	'dashicons-no',
	'dashicons-palmtree',
	'dashicons-paperclip',
	'dashicons-performance',
	'dashicons-phone',
	'dashicons-playlist-audio',
	'dashicons-playlist-video',
	'dashicons-plus-alt',
	'dashicons-plus-light',
	'dashicons-plus',
	'dashicons-portfolio',
	'dashicons-post-status',
	'dashicons-pressthis',
	'dashicons-products',
	'dashicons-randomize',
	'dashicons-redo',
	'dashicons-rest-api',
	'dashicons-rss',
	'dashicons-schedule',
	'dashicons-screenoptions',
	'dashicons-search',
	'dashicons-share-alt',
	'dashicons-share-alt2',
	'dashicons-share',
	'dashicons-shield-alt',
	'dashicons-shield',
	'dashicons-slides',
	'dashicons-smartphone',
	'dashicons-smiley',
	'dashicons-sort',
	'dashicons-sos',
	'dashicons-star-empty',
	'dashicons-star-filled',
	'dashicons-star-half',
	'dashicons-sticky',
	'dashicons-store',
	'dashicons-tablet',
	'dashicons-tag',
	'dashicons-tagcloud',
	'dashicons-testimonial',
	'dashicons-text',
	'dashicons-thumbs-down',
	'dashicons-thumbs-up',
	'dashicons-tickets-alt',
	'dashicons-tickets',
	'dashicons-tide',
	'dashicons-translation',
	'dashicons-trash',
	'dashicons-twitter',
	'dashicons-undo',
	'dashicons-universal-access-alt',
	'dashicons-universal-access',
	'dashicons-unlock',
	'dashicons-update',
	'dashicons-upload',
	'dashicons-vault',
	'dashicons-video-alt',
	'dashicons-video-alt2',
	'dashicons-video-alt3',
	'dashicons-visibility',
	'dashicons-warning',
	'dashicons-welcome-add-page',
	'dashicons-welcome-comments',
	'dashicons-welcome-learn-more',
	'dashicons-welcome-view-site',
	'dashicons-welcome-widgets-menus',
	'dashicons-welcome-write-blog',
	'dashicons-wordpress-alt',
	'dashicons-wordpress',
	'dashicons-yes-alt',
	'dashicons-yes'
);