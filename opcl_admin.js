jQuery(document).ready(function(jQuery){
	/*opcl_initTextarea();
	opcl_initDisplayPrice();
	opcl_initDisplayDates();
	opcl_initDisplayDatetimes();
	opcl_initCheckInputNumber();*/
	opcl_selectTab();
});


function opcl_show_tab(idTab,me,forceRefresh=true){
	/*if (forceRefresh){
		if (opcl_checkChanges('')){
			document.location.href = me.href;
			document.location.reload(true);
		}
		return;
	}*/
	opcl_tabs_section = document.getElementById("opcl_tabs_" + idTab.split("-")[0]);
	opcl_tabs_section.querySelector(".opcl_tabs_active").classList.remove("opcl_tabs_active");
	opcl_tabs_section.querySelector(".opcl_tabs_content_active").classList.remove("opcl_tabs_content_active");
	me.classList.add("opcl_tabs_active");
	document.getElementById(idTab).classList.add("opcl_tabs_content_active");
	return false;
}

function opcl_selectTab(e){
	var url = document.location.href;
	url = url.split("#");
	if (url[1]){
		var anchor = url[1];
		var a = document.querySelector('a[href="#' + anchor + '"');
		var onclick = a.getAttribute("onclick");
		var param1 = onclick.split("'")[1];
		opcl_show_tab(param1,a,false);
	}
	if (document.location.href.indexOf("&tab=") > -1){
		var tabUrl = document.location.href.split("#");
		var anchor = "";
		var endUrl = document.location.href.length;
		if (tabUrl[1]){
			anchor = tabUrl[1];
			endUrl = document.location.href.indexOf("#") - 1;
		}
		var newUrl = document.location.href.substr(0, document.location.href.indexOf("&tab="));
		if (anchor != ""){
			newUrl += "#" + anchor;
		}else{
			newUrl += "#" + document.location.href.substr(document.location.href.indexOf("&tab=") + 5, endUrl);
		}
		history.pushState(null, '', newUrl);  
		
	}
}

function opcl_closeAdminNotice(elt){
	elt.closest("div[class*='opcl_adminNotice']").style.display = "none";
}

function opcl_initListenerTips(idTab){
	tips = document.getElementById("opcl_tabs_" + idTab).getElementsByClassName("dashicons-editor-help");
	for(var i = 0; i < tips.length; i++){
		tips[i].addEventListener("mouseover",function(){document.getElementById("opcl_tabs_tips_" + idTab).innerHTML = this.getAttribute("opcl_title");});
	}
	tabLabels = document.getElementById("opcl_tabs_" + idTab).getElementsByClassName("opcl_tabLabel");
	for(var i = 0; i < tabLabels.length; i++){
		tabLabels[i].addEventListener("mouseover",function(){document.getElementById("opcl_tabs_tips_" + idTab).innerHTML = this.getAttribute("opcl_title");});
	}
	
	tipOnFocus = document.getElementById("opcl_tabs_" + idTab).getElementsByClassName("opcl_tipOnFocus");
	for(var i = 0; i < tipOnFocus.length; i++){
		
		tipOnFocus[i].addEventListener("focus",function(){
			if (this.closest(".opcl_fieldsContent")){
				if (this.closest(".opcl_fieldsContent").querySelector(".dashicons-editor-help")){
					document.getElementById("opcl_tabs_tips_" + idTab).innerHTML = this.closest(".opcl_fieldsContent").querySelector(".dashicons-editor-help").getAttribute("opcl_title");
				}
			}
		});

	}
	opcl_initLiDisplay(idTab);
}

function opcl_initLiDisplay(idTab){
	uls = document.getElementById("opcl_tabs_" + idTab).getElementsByClassName("rmc-sortable-display");
	for(var u = 0; u < uls.length; u++){
		li = uls[u].getElementsByTagName("li");
		for(var i = 0; i < li.length; i++){
			li[i].addEventListener("mouseover",function(){document.getElementById("opcl_tabs_tips_" + idTab).innerHTML = this.getAttribute("opcl_title");});
			li[i].querySelector("span > i.dashicons").addEventListener("click",function(){this.classList.toggle("dashicons-visibility");this.classList.toggle("dashicons-hidden");});
			li[i].querySelector("div > .opcl_liDisplayRequired").addEventListener("click",function(){
				if (this.innerHTML.indexOf("opcl_asterisk") != -1){
					if (this.innerHTML.indexOf("opcl_notrequired") == -1){
						this.innerHTML = '<span class="opcl_asterisk opcl_notrequired">*</span>';
					}else{
						this.innerHTML = '<span class="opcl_asterisk">*</span>';
					}
				}
			});
				
		}
	}
}

function opcl_addField(){
	if (document.getElementById("opcl_opcl_addFieldName").value != ""){
		document.getElementById("opcl_formAddField").submit();	
	}
}

function opcl_deleteComment(id){
	document.getElementById("opcl_id_commentaire").value = id;
	document.getElementById("opcl_formDeleteComment").submit();	
}

function opcl_displayComment(elt, id){
	var nouveau = elt.closest("tr").querySelector("td b").innerHTML;
	var text = elt.innerHTML.replace(/\\/g, '');
	if ((nouveau != '') || (text.substring(text.length -3) == "...")){
		var ajaxurl = WPJS_OCP.adminAjaxUrl;	
		var wpnonce = document.getElementById("opcl_afficher_commentaire").value;
		var data = {
			'action': 'opcl_js_displayComment',
			'idComment': id,
			'_wpnonce': wpnonce
		};
		jQuery.post(ajaxurl, data, function(response) {
			var reponse = JSON.parse(response);
			elt.closest("tr").querySelector("td b").innerHTML = "";
			elt.innerHTML = reponse.replace(/\n/g,'<br>').replace(/\\/g, '');
		});
	}
	return false;
}

function opcl_updateParameters(idTab){
	var f = document.createElement("form");
	f.setAttribute("method","post");
	f.id = "opcl_ajouterParametres";
	var data1 = document.getElementById(idTab).getElementsByTagName("input");
	var data2 = document.getElementById(idTab).getElementsByTagName("select");
	var data3 = document.getElementById(idTab).getElementsByTagName("textarea");
	for(var i=0; i < data1.length; i++){
		var clone = data1[i].cloneNode(true);
		if ((clone.type == "checkbox") && (clone.checked)){
			clone.value = "on";
		}
		clone.type = "hidden";
		f.appendChild(clone);
	}
	for(var i=0; i < data2.length; i++){
		//var clone = data2[i].cloneNode(true);
		var clone = document.createElement("input");
		clone.type = "hidden";
		clone.value = data2[i].value;
		clone.name = data2[i].name;
		f.appendChild(clone);
	}
	for(var i=0; i < data3.length; i++){
		var clone = data3[i].cloneNode(true);
		clone.value = data2[i].innerHTML;
		clone.type = "hidden";
		f.appendChild(clone);
	}
	document.getElementById(idTab).appendChild(f);
	document.getElementById("opcl_ajouterParametres").submit();
}

function opcl_toLocaleDateTimeString(elt, displayTime=true){
	da = elt.innerHTML;
	if (da != ""){
		d = new Date();
		d.setUTCFullYear(da.substr(0,4));
		d.setUTCMonth(Number(da.substr(5,2)) - 1);
		d.setUTCDate(da.substr(8,2));
		decalage = d.getTimezoneOffset()/60;
		d.setUTCHours(Number(da.substr(11,2)) + decalage);
		d.setUTCMinutes(da.substr(14,2));
		d.setUTCSeconds(0);
		display = d.toLocaleDateString()
		if (displayTime){
			display += " - " + d.toLocaleTimeString(navigator.language, {hour: '2-digit',minute:'2-digit'});
		}
		elt.innerHTML =  display;
	}
}

function opcl_selectIcon(elt){
	document.getElementById("opcl_icon").classList = "dashicons " + elt.value;
}

function opcl_changeColor(elt, idColor){
	document.getElementById("opcl_customcolor" + idColor).style.backgroundColor = elt.value;
}