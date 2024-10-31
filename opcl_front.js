
function opcl_toggle_footer_button(){
	var fb = document.getElementById('opcl_footer_button');
	var fbc = document.getElementById('opcl_footer_button_content');
	var fbi = document.getElementById('opcl_footer_button_icon');
	fb.classList.toggle("opcl_footer_menu_opened");
	if (fb.classList.contains("opcl_footer_menu_opened")){
		fbc.style.display = "block";
		fbi.style.display = "none";
	}else{
		fbc.style.display = "none";
		fbi.style.display = "inline";
	}
}

function opcl_footer_button_send(){
	var ajaxurl = WPJS_OCP.adminAjaxUrl;	
	var wpnonce = document.getElementById("opcl_sendOpinion").value;
	var opcl_footer_textarea = document.getElementById("opcl_footer_textarea").value;
	var opcl_footer_button_idpost = document.getElementById("opcl_footer_button_idpost").value;
	var data = {
		'action': 'opcl_js_sendOpinion',
		'opcl_footer_textarea': opcl_footer_textarea,
		'opcl_footer_button_idpost': opcl_footer_button_idpost,
		'_wpnonce': wpnonce
	};
	jQuery.post(ajaxurl, data, function(response) {
		var reponse = JSON.parse(response);
		alert(response);
		document.getElementById("opcl_footer_textarea").value = '';
		opcl_toggle_footer_button();
	});
}