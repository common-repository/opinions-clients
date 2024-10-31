<?php
/*
* Plugin Name: Opinions Clients
* Plugin URI: https://wordpress.org/plugins/opinions-clients/
* Description: Que pensent vos clients de votre site internet? Demandez leur de façon anonyme et simple avec ce plugin! / What do your customers think of your website? Ask them anonymously and simply with this plugin!
* Version: 1.0.0
* Author: CreaLion.NET
* Author URI: https://crealion.net
* Text Domain: opinions-clients
* Domain Path: /languages
*/

include 'opcl-wpajax.php';

function opcl_install(){
	if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
    global $wpdb;
    $wpdb->query($wpdb->prepare("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ocp_commentaires (id INT AUTO_INCREMENT PRIMARY KEY, commentaire text NOT NULL, id_post INT, date_commentaire datetime, statut VARCHAR(%d));", "3"));
    $wpdb->query($wpdb->prepare("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ocp_options (id INT AUTO_INCREMENT PRIMARY KEY, cle VARCHAR(%d) NOT NULL, valeur TEXT);", "255"));
	$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ocp_options (cle, valeur) VALUES (%s, %s)", 'afficher_form_articles_pages_products', '7'));
	$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ocp_options (cle, valeur) VALUES (%s, %s)", 'deleteTableOnUninstall', ''));
	
	$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ocp_options (cle, valeur) VALUES (%s, %s)", 'licencekey', ''));
	$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ocp_options (cle, valeur) VALUES (%s, %s)", 'texte_form', __('Give your anonymous opinion about this page','opinions-clients')));
	$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ocp_options (cle, valeur) VALUES (%s, %s)", 'texte_button', __('Send','opinions-clients')));
	$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ocp_options (cle, valeur) VALUES (%s, %s)", 'longueur_max_commentaires', 0));
	$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ocp_options (cle, valeur) VALUES (%s, %s)", 'pause_commentaires', 0));
	
	$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ocp_options (cle, valeur) VALUES (%s, %s)", 'position_vertical_bouton', 'bottom'));
	$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ocp_options (cle, valeur) VALUES (%s, %s)", 'position_horizontal_bouton', 'right'));
}

function opcl_deactivate(){
    global $wpdb;
}

function opcl_uninstallPlugin(){
	global $wpdb;
	$ocp_options = opcl_getOptions();
	if ($ocp_options['deleteTableOnUninstall'] == '1'){
		$wpdb->query("DROP TABLE {$wpdb->prefix}ocp_commentaires");
		$wpdb->query("DROP TABLE {$wpdb->prefix}ocp_options");
	}
}

function opcl_add_admin_menu(){
	global $wpdb;
	$notif = '';
	$resultats = $wpdb->get_results("SELECT count(id) AS total FROM {$wpdb->prefix}ocp_commentaires WHERE statut = 'new'");
	if ($resultats[0]->total > 0){
		$notif = ' <span class="awaiting-mod">'.$resultats[0]->total.'</span>';
	}
    $hook = add_menu_page('Opinions Clients', 'Opinions Clients' . $notif, 'manage_options', 'opinions-clients', 'opcl_menu_html');
	add_action('load-'.$hook, 'opcl_process_action');
}

function opcl_process_action(){
	if (!current_user_can('administrator')){return;}
	global $ocp_options, $wpdb;
	
    if (isset($_POST['opcl_supprimer_commentaire'])) {
		check_admin_referer('supprimer_commentaire','opcl_supprimer_commentaire');
		$id_commentaire = intval($_POST['opcl_id_commentaire']);
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ocp_commentaires WHERE id = %d", $id_commentaire));
    }
    
	if (isset($_POST['opcl_enregistrer_options'])) {
		check_admin_referer('enregistrer_options','opcl_enregistrer_options');
        $afficher_form_articles_pages_products = intval($_POST['afficher_form_articles_pages_products']);
        $deleteTableOnUninstall = intval($_POST['deleteTableOnUninstall']);
        $texte_form = sanitize_text_field($_POST['texte_form']);
        $texte_button = sanitize_text_field($_POST['texte_button']);
        $longueur_max_commentaires = intval($_POST['longueur_max_commentaires']);
        $pause_commentaires = intval($_POST['pause_commentaires']);
        $position_vertical_bouton = sanitize_text_field($_POST['position_vertical_bouton']);
        $position_horizontal_bouton = sanitize_text_field($_POST['position_horizontal_bouton']);
        
        if (($afficher_form_articles_pages_products < 0) ||
        	($deleteTableOnUninstall < 0) ||
        	($longueur_max_commentaires < 0) ||
        	($pause_commentaires < 0)
        ){
        	opcl_addAdminNotice(__("Wrong data, please check your entries","opinions-clients"),1,'error');
        	return false;
    	}
        	
        if ((!in_array($position_vertical_bouton,['top','bottom'])) ||
    		(!in_array($position_horizontal_bouton,['left','right']))
    	){
    		opcl_addAdminNotice(__("Wrong data, please check your entries","opinions-clients"),1,'error');
        	return false;
    	}
        
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ocp_options SET valeur = %s WHERE cle = 'afficher_form_articles_pages_products'", $afficher_form_articles_pages_products));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ocp_options SET valeur = %s WHERE cle = 'deleteTableOnUninstall'", $deleteTableOnUninstall));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ocp_options SET valeur = %s WHERE cle = 'texte_form'", $texte_form));
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ocp_options SET valeur = %s WHERE cle = 'texte_button'", $texte_button));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ocp_options SET valeur = %s WHERE cle = 'longueur_max_commentaires'", $longueur_max_commentaires));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ocp_options SET valeur = %s WHERE cle = 'pause_commentaires'", $pause_commentaires));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ocp_options SET valeur = %s WHERE cle = 'position_vertical_bouton'", $position_vertical_bouton));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ocp_options SET valeur = %s WHERE cle = 'position_horizontal_bouton'", $position_horizontal_bouton));
        
        $ocp_options = [];
    }
}

$ocp_options = array();
function opcl_getOptions(){
	global $ocp_options, $wpdb;
	if (sizeof($ocp_options) == 0){
		$resultats = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ocp_options ORDER BY %s", "cle")) ;
		foreach ($resultats as $cv) {
			$ocp_options[$cv->cle] = $cv->valeur;
		}
	}
	return $ocp_options;
}

function opcl_extract($text){
	$longueur_max_commentaires = 80;
	//$text = htmlspecialchars_decode($text, ENT_QUOTES);
	if (strlen($text) > $longueur_max_commentaires){
		return nl2br(substr($text, 0, $longueur_max_commentaires)) . "...";
	}
	return nl2br($text);
}

function opcl_menu_html(){
	global $wpdb, $opcl_iconBouton;
	
	opcl_load_admin_js();
	opcl_load_admin_css();
	
	$resultats = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ocp_commentaires ORDER BY date_commentaire DESC");
	$listActualComments = [];
	foreach($resultats as $comment){
		$listActualComments[] = [
			(($comment->statut == 'new') ? '<b style="color:#00bf00"> NEW </b>' : '<b></b>'),
			opcl_ui_fields([
				'type'			=> 'datetime',
				'value'			=> substr($comment->date_commentaire, 0, 19)
			]),
			opcl_ui_fields([
				'type'			=> 'text',
				'value'			=> get_the_title(intval($comment->id_post))
			]),
			str_replace('--O--','<br>',
			opcl_ui_fields([
				'type'			=> 'link',
				'value'			=> '#',
				'linktext'		=>  opcl_extract(sanitize_textarea_field($comment->commentaire)),
				'onclick'		=> 'opcl_displayComment(this, \''.intval($comment->id).'\')'
			])),
			opcl_ui_fields([
				'label'			=> __('Delete', 'opinions-clients'),
				'type'			=> 'button',
				'onclick'		=> 'opcl_deleteComment(\''.intval($comment->id).'\')',
				'class'			=> 'button'
			])
		];
	}
	$displayTableActualComments = [
		'<form method="post" action="" id="opcl_formDeleteComment">',
		opcl_get_wp_nonce_field('supprimer_commentaire','opcl_supprimer_commentaire'),
		opcl_get_wp_nonce_field('afficher_commentaire','opcl_afficher_commentaire'),
		opcl_ui_fields([
			'name' 			=> 'opcl_id_commentaire',
			'id' 			=> 'opcl_id_commentaire',
			'type' 			=> 'hidden',
			'value' 		=> '-1'
		]),
		opcl_ui_table([
			'columnsName'		=> ['',__('Date','opinions-clients'),__('Product/Post/Page', 'opinions-clients'),__('Comment', 'opinions-clients'),''],
			'displayFooter'		=> false,
			'tbodyId'			=> 'opcl_listcomments',
			'data'				=> $listActualComments
		]),
		'</form>'
	];
	
	$ocp_options = opcl_getOptions();
	
	$afficherForm = [
						__('Nowhere','opinions-clients'),
						__('Only on posts','opinions-clients'),
						__('Only on pages','opinions-clients'),
						__('Only on products','opinions-clients'),
						__('Both on post and pages','opinions-clients'),
						__('Both on post and products','opinions-clients'),
						__('Both on pages and products','opinions-clients'),
						__('Everywhere', 'opinions-clients')
					];
	$valueAfficherForm = '';
	foreach($afficherForm as $s => $t){
		if ($ocp_options['afficher_form_articles_pages_products'] == $s){$selected = 'selected';}else{$selected = '';}
		$valueAfficherForm .= '<option value="'.$s.'" '.$selected.'>'.$t.'</option>';
	}
	
	$verticalPosition = [	'bottom' 	=> __('Bottom', 'opinions-clients'),
							'top'		=> __('Top', 'opinions-clients')
						];
	$valueVerticalPosition = '';
	foreach($verticalPosition as $s => $t){
		if ($ocp_options['position_vertical_bouton'] == $s){$selected = 'selected';}else{$selected = '';}
		$valueVerticalPosition .= '<option value="'.$s.'" '.$selected.'>'.$t.'</option>';
	}
	$horizontalPosition = [	'left' 		=> __('Left', 'opinions-clients'),
							'right'		=> __('Right', 'opinions-clients')
						];
	$valueHorizontalPosition = '';
	foreach($horizontalPosition as $s => $t){
		if ($ocp_options['position_horizontal_bouton'] == $s){$selected = 'selected';}else{$selected = '';}
		$valueHorizontalPosition .= '<option value="'.$s.'" '.$selected.'>'.$t.'</option>';
	}
	$deleteonuninstall = [	__('No', 'opinions-clients'),
							__('Yes', 'opinions-clients')
						];
	$deleteTableOnUninstall = '';
	foreach($deleteonuninstall as $s => $t){
		if ($ocp_options['deleteTableOnUninstall'] == $s){$selected = 'selected';}else{$selected = '';}
		$deleteTableOnUninstall .= '<option value="'.$s.'" '.$selected.'>'.$t.'</option>';
	}
	
	$displayParameters1 = [
		opcl_get_wp_nonce_field('enregistrer_options','opcl_enregistrer_options'),
		opcl_ui_fields([
			'name' 			=> 'texte_form',
			'label' 		=> __('Text introducing the opinion form','opinions-clients'),
			'tip'			=> __('Text introducing the opinion form','opinions-clients'),
			'edit' 			=> true,
			'type' 			=> 'text',
			'class'			=> 'opcl_nochange',
			'value'			=> $ocp_options['texte_form']
		]),
		opcl_ui_fields([
			'name' 			=> 'texte_button',
			'label' 		=> __('Button text of the opinion form','opinions-clients'),
			'tip'			=> __('Button text of the opinion form','opinions-clients'),
			'edit' 			=> true,
			'type' 			=> 'text',
			'class'			=> 'opcl_nochange',
			'value'			=> $ocp_options['texte_button']
		]),
		opcl_ui_fields([
			'name' 			=> 'pause_commentaires',
			'label' 		=> __('Pause between each opinion','opinions-clients'),
			'tip'			=> __('To prevent spam, you can force pause between each opinion (In minutes, 0 = no pause)','opinions-clients'),
			'edit' 			=> true,
			'type' 			=> 'number',
			'min'			=> 0,
			'class'			=> 'opcl_nochange',
			'value'			=> $ocp_options['pause_commentaires']
		]),
		opcl_ui_fields([
			'name' 			=> 'longueur_max_commentaires',
			'label' 		=> __('Max characters allowed','opinions-clients'),
			'tip'			=> __('Max characters allowed for opinion (0 = unlimited)','opinions-clients'),
			'edit' 			=> true,
			'type' 			=> 'number',
			'min'			=> 0,
			'class'			=> 'opcl_nochange',
			'value'			=> $ocp_options['longueur_max_commentaires']
		]),
		
		opcl_ui_fields([
			'name' 			=> 'afficher_form_articles_pages_products',
			'label' 		=> __('Where to display the form','opinions-clients'),
			'tip'			=> __('Where to display the form','opinions-clients'),
			'edit' 			=> true,
			'type' 			=> 'select',
			'class'			=> 'opcl_nochange',
			'value'			=> $valueAfficherForm
		]),
		opcl_ui_fields([
			'name' 			=> 'position_vertical_bouton',
			'label' 		=> __('Vertical position of the button','opinions-clients'),
			'tip'			=> __('Vertical position of the button','opinions-clients'),
			'edit' 			=> true,
			'type' 			=> 'select',
			'class'			=> 'opcl_nochange',
			'value'			=> $valueVerticalPosition
		]),
		opcl_ui_fields([
			'name' 			=> 'position_horizontal_bouton',
			'label' 		=> __('Horizontal position of the button','opinions-clients'),
			'tip'			=> __('Horizontal position of the button','opinions-clients'),
			'edit' 			=> true,
			'type' 			=> 'select',
			'class'			=> 'opcl_nochange',
			'value'			=> $valueHorizontalPosition
		]),
		opcl_ui_fields([
			'name' 			=> 'deleteTableOnUninstall',
			'label' 		=> __('Delete all data on uninstall','opinions-clients'),
			'tip'			=> __('Do not activate this setting if you want to install the premium version or if you want to keep your settings / opinions for later','opinions-clients'),
			'edit' 			=> true,
			'type' 			=> 'select',
			'class'			=> 'opcl_nochange',
			'value'			=> $deleteTableOnUninstall
		]),
		'<div><p class="submit"><input type="button" class="button" value="'.__('Update settings', 'opinions-clients').'" onclick="opcl_updateParameters(\'1-1\')"></p></div>'
	];
	$displayParameters2 = [];
	
	$help = [];
	$help[] = ['h1',__('How to use the plugin?','opinions-clients')];
	$help[] = ['h2',__('1. What does the plugin do?','opinions-clients')];
	$help[] = ['p',__('The plugin simply adds a button offering a form for your visitors to give their opinions on your website. Opinions are anonymous and allow you to know how your customers feel about your products, articles or pages in general. You can then make more informed decisions about what you offer, taking into account the opinions received.','opinions-clients')];
	$help[] = ['img','img/screen1.png'];
	$help[] = ['h2',__('2. Display opinions','opinions-clients')];
	$help[] = ['p',__('Log in to your WordPress dashboard and access to "Opinions clients" menu. In the first tab "Opinions", you can see all the opinions you have received. Click on the comment to see the full text.','opinions-clients')];
	$help[] = ['img','img/screen2.png'];
	$help[] = ['h2',__('3. Settings','opinions-clients')];
	$help[] = ['p',__('a. Customize text for the form','opinions-clients')];
	$help[] = ['p',__('b. Set how and where is the form displayed','opinions-clients')];
	$help[] = ['p',__('c. Change icon button position: You can position the icon on your website at the top or bottom, and right or left. For more precision, you can define the horizontal and vertical offset from the edge you have chosen','opinions-clients')];
	$help[] = ['p',__('d. Change icon and background color: Choose the icon you want and the background color for it. Select "No icon" for deactivate the button, visitors will no longer be able to give their opinion','opinions-clients')];
	$help[] = ['p',__('e. Change color for the form','opinions-clients')];
	$help[] = ['img','img/screen3.png'];
	$help[] = ['p',__('Some settings are only available on the premium version','opinions-clients')];
	
	
	$displayHelp = '<div class="opcl_adminHelp">';
	foreach($help as $h){
		if ($h[0] == 'img'){
			$displayHelp .= '<img src="'.plugins_url($h[1],__FILE__).'">';	
		}else{
			$displayHelp .= '<' . $h[0] . '>' . $h[1] . '</' . $h[0] . '>';
		}
	}
	$displayHelp .= '</div>';
	
	echo opcl_ui_tabs(
		'<img src="'.plugins_url('/img/logo/opcl_logo_32.png',__FILE__).'" style="display:inline-block;margin-right:8px">'.'Opinions Clients',
		[
			__('Opinions', 'opinions-clients'),
			__('Settings', 'opinions-clients'),
			__('Help', 'opinions-clients')
		],
		[
			'dashicons dashicons-list-view',
			'dashicons dashicons-admin-tools',
			'dashicons dashicons-editor-help'
		],
		[	
			[opcl_ui_verticallist([$displayTableActualComments])],
			[opcl_ui_verticallist([$displayParameters1,$displayParameters2])],
			[opcl_ui_verticallist([[$displayHelp]])],
		],
		'champs',
		-1,
		[
			__('Opinions', 'opinions-clients'),
			__('Settings', 'opinions-clients'),
			__('Help', 'opinions-clients')
			
		],
		['opinions','settings','help']
	);
    
}

$opcl_idTabs = 0;
function opcl_ui_tabs($tabs_title, $tabs_menu, $tabs_icons, $tabs_content, $table, $idInTable, $tips, $anchors){
	global $opcl_idTabs;
	global $SAFE_DATA;
	$opcl_idTabs++;
	$tab_active = '';
	if (isset($SAFE_DATA['tab'])){
		$tab_active = $SAFE_DATA['tab'];
	}
	
	$display = opcl_displayAdminNotice(-1) . '
	<div class="opcl_tabs_section" id="opcl_tabs_'.$opcl_idTabs.'"><div id="opcl_form">
		<div class="opcl_tabs_title">
			<div>' . $tabs_title . '</div>
			<div>
	';

	if ($idInTable != -1){
		$display .= '
				<a href="?page=opinions-clients&opcl_act=trash'.ucfirst($table).'&'.$table.'='.$idInTable.'">'.__('Move to trash','opinions-clients').'</a>' . 
				//get_submit_button(__('Update', 'opinions-clients'),'primary large','updateSpace',true,['id' => 'updateSpace']) .
				'<p class="submit"><input type="button" name="'.$action.'" id="'.$action.'" class="button button-primary button-large" value="'.__('Update', 'opinions-clients').'"></p>
		';
	}

	$display .= '

			</div>
		</div>
		<div class="opcl_tabs_container">
			<div class="opcl_menu_container">
				<ul>';
					foreach($tabs_menu as $index => $menu){
						$anchor = sanitize_title($anchors[$index]);
						if (($tab_active == '') && ($index == 0)){$tab_active = $anchor;}
						($tab_active == $anchor) ? $class = 'class="opcl_tabs_active opcl_tabLabel"' : $class = 'class="opcl_tabLabel"';
						$display .= '<li '.$class.' opcl_title="'.$tips[$index].'"><a href="#'.$anchor.'" onclick="opcl_show_tab(\''.$opcl_idTabs.'-'.$index.'\',this,false)"><span class="'.$tabs_icons[$index].'"></span><span class="opcl_tabText">'.$menu.'</span></a></li>';
					}
					$display .= '
				</ul>
			</div>
			<div class="opcl_content_container">';
				foreach($tabs_content as $index => $content){
					$anchor = sanitize_title($anchors[$index]);
					($tab_active == $anchor) ? $class = 'opcl_tabs_content_active' : $class = '';
					$display .= '<div class="opcl_tabs_content '.$class.'" id="'.$opcl_idTabs.'-'.$index.'">';
					//$class = 'opcl_width' . floor(100 / sizeof($content));
					for($i = 0; $i < sizeof($content); $i++){
						//$display .= '<div class="'.$class.'">' . $content[$i] . '</div>';
						$display .= $content[$i];
					}
					
					$display .= '</div>';
				}
				$display .= '
			</div>
		</div>
		<div class="opcl_tabs_tips" id="opcl_tabs_tips_'.$opcl_idTabs.'">'.__('Info bar. Displays information when you edit a field','opinions-clients').'</div>
	</div></div>
	<script>
		jQuery(document).ready(function(jQuery){
			opcl_initListenerTips('.$opcl_idTabs.');
		})
	</script>';
	return $display;
}

function opcl_ui_fields($param){
	$p = [
		'name' => '',
		'label' => '',
		'edit' => false,
		'type' => 'text',
		'value' => '',
		'placeholder' => '',
		'onchange' => '',
		'required' => '',
		'tip' => '',
		'id' => '',
		'min' => '',
		'max' => '',
		'step' => '',
		'brotherfield' => '',
		'class' => '',
		'classParent' => '',
		'title' => '',
		'opcl_title' => '',
		'data' => '',
		'datalist' => '',
		'labelTop' => false,
		'target' => '_self',
		'linktext' => '',
		'onclick' => ''
	];	
	$p = array_merge($p, $param);
	
	$classParent = 'opcl_fieldsContent';
	if ($p['classParent'] != ''){
		$classParent = $p['classParent'];
	}
	
	$classTopField = '';
	if (strpos($p['class'],'opcl_marginTopColumn') > -1){
		$p['class'] = str_replace('opcl_marginTopColumn','',$p['class']);
		$classTopField = 'opcl_marginTopColumn';
	}
	
	$p['class'] .= ' opcl_tipOnFocus';
	
	if ($p['opcl_title'] != ''){
		$p['class'] .= ' opcl_tabLabel';
		$p['title'] .= __('See the details below in the info bar','opinions-clients');
	}
	
	$p['id'] = (($p['id'] == '')&&($p['name'] != '')) ? 'opcl_' . $p['name'] : $p['id'];
	$p['tip'] = htmlspecialchars($p['tip'], ENT_QUOTES, 'UTF-8');
	if ($p['type'] == 'WPEditor'){
		$display = '<div class="opcl_labelWPeditor"><label>' . $p['label'] . '</label>';
		$display .= '<span class="dashicons dashicons-editor-help" opcl_title="'.$p['tip'].'"></span></div>';
		$display .= '<div id="opcl_parentWPEditor_'.$p['name'].'" class="opcl_width100 opcl_mt10"></div>';
		echo '<div class="opcl_hiddenWPEditor">';
		wp_editor(stripcslashes($p['value']),$p['name'],array( 'editor_height' => '310'));
		
		echo '</div>';
		$display .= '<script>
						jQuery(document).ready(function(jQuery){
							var content = "'.$p['value'].'";
							document.getElementById("opcl_parentWPEditor_'.$p['name'].'").innerHTML = "";
							document.getElementById("opcl_parentWPEditor_'.$p['name'].'").appendChild(document.getElementById("wp-'.$p['name'].'-wrap"));
						})
					</script>';
	}elseif ($p['type'] == "hidden"){
		$display = '<input type="'.$p['type'].'" id="'.$p['id'].'" name="'.$p['name'].'" value="'.$p['value'].'">';
	}else{
		if (($p['type'] == 'text') || ($p['type'] == 'textarea') || ($p['type'] == 'email')){
			$p['value'] = opcl_removeslashes($p['value']);
		}elseif ($p['type'] == 'number'){
			$p['value'] = (floatval($p['value']) + 0);
		}
		$class = '';
		if (($p['type'] == 'textarea') || ($p['type'] == 'ul')){
			$class = 'class="opcl_fieldLabelTop"';
		}
		($p['labelTop']) ? $opcl_field = '' . $classTopField : $opcl_field = 'opcl_field ' . $classTopField;
		$display = '<div class="'.$opcl_field.'">';
		if (($p['type'] != 'button') && ($p['type'] != 'reset')){
			if (($p['label'] != '') && (!$p['labelTop'])){
				$display .= '<label for="'.$p['id'].'" '.$class.'>' . $p['label'] . '</label>';	
			}
			$display .= '<div class="'.$classParent.'">';
		}
		
		if ($p['type'] == 'link'){
			$display .= '<a href="'.$p['value'].'" id="'.$p['id'].'" class="'.$p['class'].'" target="'.$p['target'].'" onclick="'.$p['onclick'].'"'." rmc-data='".$p['data']."'".'>'.opcl_removeslashes($p['linktext']).'</a>';	
		}elseif ($p['type'] == 'select'){
			if ($p['labelTop']){
				$p['value'] = '<option value="">'.$p['label'].'</option>' . $p['value'];
			}
			//if (strpos($p['value'],'selected>') === false){$p['class'] = str_replace('opcl_filteractivated','',$p['class']);}
			$display .= '<select id="'.$p['id'].'" name="'.$p['name'].'" onchange="'.$p['onchange'].'" '.$p['required'].' class="'.$p['class'].'" title="'.$p['title'].'">'.$p['value'].'</select>';
		}elseif ($p['type'] == 'ul'){
			$display .= '<ul id="'.$p['id'].'" class="'.$p['class'].'">'.$p['value'].'</ul>';
		}elseif ($p['type'] == 'textarea'){
			$display .= '<textarea id="'.$p['id'].'" name="'.$p['name'].'" '.$p['required'].' placeholder="'.$p['placeholder'].'" rows="'.$p['min'].'" class="'.$p['class'].'">'.$p['value'].'</textarea>';
			if ($p['tip'] == ''){$display .= '<span>&nbsp;</span>';}
		}elseif ($p['type'] == 'button'){
			$display .= '<button type="button" id="'.$p['id'].'" onclick="'.$p['onclick'].'" class="'.$p['class'].'">'.$p['label'].'</button>';
		}elseif ($p['type'] == 'reset'){
			$display .= '<button type="reset" id="'.$p['id'].'" class="'.$p['class'].'">'.$p['label'].'</button>';
		}elseif ($p['type'] == 'checkbox'){
			if ($p['value']){$checked = 'checked';}else{$checked = '';}
			$display .= '<input type="'.$p['type'].'" id="'.$p['id'].'" name="'.$p['name'].'" '.$checked.' '.$p['required'] .' class="'.$p['class'].'" title="'.$p['title'].'" opcl_title="'.$p['opcl_title'].'">';
			$display .= '<label for="'.$p['id'].'" '.$class.'>' . $p['label'] . '</label>';

		}elseif ($p['edit']){
			if ($p['labelTop']){$placeholder = 'placeholder="'.$p['label'].'"';}else{$placeholder = 'placeholder="'.$p['placeholder'].'"';}
			//if (($p['type'] == 'number')&&($p['value'] == 0)){$p['class'] = str_replace('opcl_filteractivated','',$p['class']);}
			if ($p['datalist'] != ''){$list = ' list="'.$p['datalist'].'" ';}else{$list = '';}
			$display .= '<input type="'.$p['type'].'" id="'.$p['id'].'" name="'.$p['name'].'" value="'.$p['value'].'" '.$p['required'] .' class="'.$p['class'].'" title="'.$p['title'].'" '.$placeholder.$list;
			if ($p['min'] !== ''){$display .= ' min="' . $p['min'] . '"';}
			if ($p['max'] !== ''){$display .= ' max="' . $p['max'] . '"';}
			if ($p['step'] !== ''){$display .= ' step="' . $p['step'] . '"';}
			if ($p['step'] == '1'){$display .= ' pattern="\d+"';}
			if ($p['onchange'] != ''){$display .= ' onchange="' . $p['onchange'] . '"';}
			$display .= '>';
		}else{
			if (($p['type'] == 'datetime') && ($p['id'] == '')){
				$p['id'] = 'opcl_' . rand(1,999999);
				$p['class'] .= ' opcl_datetime';
			}
			$display .= '<span id="'.$p['id'].'" class="opcl_noedit '.$p['class'].'">'.$p['value'].'</span>';
			if ($p['type'] == 'datetime'){
				$display .= '<script>jQuery(document).ready(function(jQuery){opcl_toLocaleDateTimeString(document.getElementById("'.$p['id'].'"));})</script>';
			}
		}
		
		$display .= $p['brotherfield'];
		
		if ($p['tip'] != ''){
			$class = 'dashicons dashicons-editor-help';
			/*if (($p['type'] == 'textarea') || ($p['type'] == 'ul')){
				$class .= ' opcl_fieldTipTop';
			}*/
			$display .= '<span class="'.$class.'" title="'.__('See the details below in the info bar','opinions-clients').'" opcl_title="';
			if (($p['label'] != '') && (strpos($p['label'],'<') === false)){
				$display .= '<b>'.$p['label'].'</b>: ';
			}
			$display .= $p['tip'].'"></span>';
		}
		if (($p['type'] != 'button') && ($p['type'] != 'reset')){
			$display .= '</div>';
		}
		$display .= '</div>';
	}
	if ($p['datalist'] != ''){
		$display .= '<datalist id="'.$p['datalist'].'"></datalist>';
	}
	return $display;
}

function opcl_ui_verticallist($tabs){
	$display = '';
	$class = 'opcl_width' . floor(100 / sizeof($tabs));
	foreach($tabs as $tab){
		$display .= '<div class="'.$class.'"><div class="opcl_ui_verticallist">';
		foreach($tab as $elt){
			if ((strpos($elt,'type="hidden"') > -1) || (strpos($elt,'<form') > -1) || (strpos($elt,'</form') > -1)){
				$display .= $elt;
			}else{
				$display .= '<div>'.$elt.'</div>';
			}
		}
		$display .= '</div></div>';
	}
	return $display;
}

function opcl_ui_table($param){
	$p = [
		'name' 			=> '',
		'caption'		=> '',
		'class' 		=> '',
		'id'			=> '',
		'tip'			=> '',
		'tbodyId'		=> '',
		'displayFooter'	=> true,
		'columnsName'	=> [],
		'data'			=> [[]],
		'attributes'	=> [],		
	];
	$p = array_merge($p, $param);
	
	$display = '';
	if ($p['name'] != ''){$display .= '<h2>'.ucfirst($p['name']).'</h2>';}
	$display .= '<table class="opcl_table opcl_widefat opcl_striped '.$p['class'].'" id="'.$p['id'].'">';
	if ($p['caption'] != ''){
		$display .= '<caption>'.$p['caption'];
		if ($p['tip'] != ''){
			$display .= '<span class="dashicons dashicons-editor-help" title="'.__('See the details below in the info bar','opinions-clients').'" opcl_title="'.$p['tip'].'"></span>';
		}
		$display .= '</caption>';
	}
	$display .= '<thead><tr>';
	foreach($p['columnsName'] as $index => $column){
		if ($column != '--row'){
			$attr = '';
			if (isset($p['attributes'][$index])){$attr = $p['attributes'][$index];}
			$display .= '<th '.$attr.'>'.$column.'</th>';
		}
	}
	$display .= '</tr></thead><tbody id="'.$p['tbodyId'].'">';
	foreach($p['data'] as $row){
		$emptydata = false;
		foreach($row as $index => $data){
			if (substr($data,0,5) == '--row'){
				$display .= '<tr class="opcl_'.substr($data, 2).'">';
				$emptydata = true;
			}else{
				if ($index == 0){$display .= '<tr>';}
				if (($data == '')&&(!$emptydata)){$data = '—';}
				$display .= '<td rmc-title="'.$p['columnsName'][$index].'">'.$data.'</td>';
			}
		}
		$display .= '</tr>';
	}
	$display .= '</tbody>';
	if ($p['displayFooter']){
		$display .= '<tfoot><tr>';
		foreach($p['columnsName'] as $column){
			$display .= '<th>'.$column.'</th>';
		}
		$display .= '</tr></tfoot>';
	}
	$display .= '</table>';
	return $display;
}

function opcl_get_wp_nonce_field($action,$nonceName='_wpnonce'){
	$nonce = wp_create_nonce($action);
	return '<input type="hidden" id="'.$nonceName.'" name="'.$nonceName.'" value="'.$nonce.'" />
			<input type="hidden" name="_wp_http_referer" value="'.$_SERVER['REQUEST_URI'].'" />';
}

function opcl_load_admin_js(){
	if (!current_user_can('administrator')){return;}
	wp_enqueue_script('opinions-clients-admin-js',	plugins_url('opcl_admin.js', __FILE__), array('jquery'),'1.0.0',true);

	wp_localize_script('opinions-clients-admin-js', 'WPJS_OCP', array(
		'adminAjaxUrl' 					=> admin_url('admin-ajax.php'),
		'pluginsUrl' 					=> plugins_url('',__FILE__),
		'opcl_TConfirmDeleteItem' 		=> __('Are you sure you want to delete this item?', 'opinions-clients'),
		'opcl_TPleaseFillLabel' 			=> __('Please fill in the label', 'opinions-clients'),
		'opcl_TPleaseFillMaxQty' 		=> __('Please fill in the maximum quantity', 'opinions-clients'),
		'opcl_TPleaseFillEndDate' 		=> __('Please fill in the end date', 'opinions-clients'),
		));
}

function opcl_load_admin_css(){
	if (!current_user_can('administrator')){return;}
	wp_enqueue_style('opinions-clients-admin-css', plugins_url('opcl_admin.css', __FILE__));
}

function opcl_load_front_js(){
	wp_enqueue_script('opinions-clients-front-js',	plugins_url('opcl_front.js', __FILE__), array('jquery'),'1.0.0',true);
	wp_localize_script('opinions-clients-front-js', 'WPJS_OCP', array(
		'adminAjaxUrl' 									=> admin_url('admin-ajax.php')
	));
}

function opcl_load_front_css(){
	wp_enqueue_style('opinions-clients-front-css', plugins_url('opcl_front.css', __FILE__));
}

function opcl_removeslashes($text){
	$text = stripslashes($text);
	//$text = htmlspecialchars($text);
	return $text;
}

$opcl_adminNotice = [];
function opcl_addAdminNotice($text,$level,$type=''){
	global $opcl_adminNotice;
	$opcl_adminNotice[] = array("text" => $text, "level" => $level, "type" => $type);
}

function opcl_displayAdminNotice($level=-1){
	global $opcl_adminNotice;
	$aff = '';
	foreach($opcl_adminNotice as $notice){
		$class = "opcl_adminNotice";
		$img = '<img src="'.plugins_url( 'img/check.png', __FILE__ ).'"> ';
		if ($notice["type"] == "error"){
			$class = "opcl_adminNoticeError";
			$img = '';
		}
		if ($notice["type"] == "warning"){
			$class = "opcl_adminNoticeWarning";
			$img = '';
		}
		if (($level == -1)||($level == $notice["level"])){
			$aff .= '<div class="'.$class.'"><div>' . $img . $notice["text"] . '</div><div><img class="opcl_btnClose" onclick="opcl_closeAdminNotice(this)" src="'.plugins_url( 'img/close.png', __FILE__ ).'"></div></div>';
			unset($notice);
		}
	}
	return $aff;
}

function opcl_add_footer_button(){
	opcl_load_front_css();
	opcl_load_front_js();
	
	$nonce = opcl_get_wp_nonce_field('sendOpinion','opcl_sendOpinion');
	$ocp_options = opcl_getOptions();
	
	$post_type = get_post_type(get_the_ID());
	switch ($ocp_options['afficher_form_articles_pages_products']) {
		case 0:	return;	break;
		case 1:	if ($post_type != 'post'){return;}; break;
		case 2:	if ($post_type != 'page'){return;}; break;
		case 3:	if ($post_type != 'product'){return;}; break;
		case 4:	if (($post_type != 'post') && ($post_type != 'page')){return;}; break;
		case 5:	if (($post_type != 'post') && ($post_type != 'product')){return;}; break;
		case 6:	if (($post_type != 'page') && ($post_type != 'product')){return;}; break;
		default: //everywhere
	}
	
	if ($ocp_options['pause_commentaires'] > 0){
		global $wpdb;
		$resultats = $wpdb->get_results("SELECT date_commentaire FROM {$wpdb->prefix}ocp_commentaires ORDER BY date_commentaire DESC limit 1");
		if (isset($resultats[0])){
			$date = strtotime(date('Y-m-d H:i:s'));
			$delai = ($date - strtotime($resultats[0]->date_commentaire)) / 60;
			if ($delai < $ocp_options['pause_commentaires']){
				return;
			}
		}
	}
	
	$verticalPosition = 'bottom';
	$horizontalPosition = 'right';
	$verticalOffset = intval($ocp_options['offset_vertical_bouton']);
	$horizontalOffset = intval($ocp_options['offset_horizontal_bouton']);
	
	if (in_array($ocp_options['position_vertical_bouton'],['bottom','top'])){
		$verticalPosition = $ocp_options['position_vertical_bouton'];
	}
	if (in_array($ocp_options['position_horizontal_bouton'],['left','right'])){
		$horizontalPosition = $ocp_options['position_horizontal_bouton'];
	}
	
	$maxlength = '';
	if ($ocp_options['longueur_max_commentaires'] > 0){
		$maxlength = 'maxlength='.$ocp_options['longueur_max_commentaires'];
	}
	
	echo '
	<div id="opcl_footer_button" style="position:fixed;'.$verticalPosition.': 50px;'.$horizontalPosition.':50px;;background-color:white;cursor:pointer;border:1px solid #ededed;border-radius:5px;box-shadow:0 4px 20px rgba(0,0,0,0.17);display:flex;justify-content:center;align-items:center;z-index:9999999999999">
		<i id="opcl_footer_button_icon" class="dashicons dashicons-lightbulb" onclick="opcl_toggle_footer_button()" style="width:40px;height:40px;padding:10px;color:#3e3e3e"></i>
		<div id="opcl_footer_button_content" style="display:none;cursor:default">
			<div id="opcl_footer_button_form" style="padding: 20px;color:#242424">'.$nonce.'
				<input type="hidden" id="opcl_footer_button_idpost" value="'.get_the_ID().'">
				<span>'.$ocp_options['texte_form'].'</span>
				<textarea '.$maxlength.' id="opcl_footer_textarea" style="background-color:white;color:#242424;width:100%" rows="4"></textarea>
				<div style="display:flex;justify-content:space-between">
					<span onclick="opcl_toggle_footer_button()" style="cursor:pointer;display:inline-block;padding:5px 10px;background-color:#f7f7f7;color:#242424;margin-top:10px;border-radius:5px;">'.__('Close', 'opinions-clients').'</span>
					<span onclick="opcl_footer_button_send()" style="cursor:pointer;display:inline-block;padding:5px 10px;background-color:#5ac1ff;color:white;margin-top:10px;border-radius:5px;">'.$ocp_options['texte_button'].'</span>
				</div>
				<div id="opcl_footer_response"></div>
			</div>
		</div>
	</div>';
}

function opcl_load_plugin_textdomain() {
    load_plugin_textdomain( 'opinions-clients', NULL, 'opinions-clients/languages' );
}
add_action( 'plugins_loaded', 'opcl_load_plugin_textdomain' );
add_action( 'wp_footer', 'opcl_add_footer_button' );
add_action( 'wp_enqueue_scripts', 'opcl_load_dashicons_front_end' );

function opcl_load_dashicons_front_end() {
	wp_enqueue_style( 'dashicons' );
}

if (is_admin() === true) {
	register_activation_hook(__FILE__, 'opcl_install');
	register_deactivation_hook(__FILE__, 'opcl_deactivate');
	register_uninstall_hook(__FILE__, 'opcl_uninstallPlugin');
	add_action('admin_menu', 'opcl_add_admin_menu');
}

?>