<?php

// is .js loader
if(substr($shortcode, (strlen($shortcode)-3)) == '.js'){
	$truecode = substr($shortcode, 0, (strlen($shortcode)-3));
	$elements = get_option('BR_ELEMENTS');
	if(!empty($elements)){
		foreach ($elements as $element => $options){
			if(!empty($options['shortcode'])){
				if($options['shortcode'] == $truecode){
					$elementConfig = get_option($element);
					$baseConf = array();
					foreach($elementConfig['_variable'] as $varkey=>$var){
						$defaultvalue = $elementConfig['_variableDefault'][$varkey];
						foreach($elementConfig['_assetLabel'] as $assetkey=>$asset){
							$defaultvalue = str_replace('{{'.$asset.'}}', $elementConfig['_assetURL'][$assetkey], $defaultvalue);
						}
						$baseConf[$var] = $defaultvalue;
					}
				}
			}
		}
	}
	if(empty($elementConfig)){
		echo "alert('invalid app code');";
		exit;
	}
	//dump($baseConf);
/*
?>
//CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE 
		var <?php echo $truecode; ?>_config = {
<?php
if(!empty($baseConf)){
	$vars = array();
	foreach($baseConf as $var=>$val){
	$vars[] = "			\"".$var."\"     		: \"".urldecode($val)."\"";
	}
	echo implode(",\r\n", $vars)."\r\n";
};
?>
		};

		# DON'T EDIT BELOW THIS LINE
*/		
?>
//<script>
			var url = '<?php echo get_site_url().'/brickroad-embed-element/'.$truecode; ?>';
			var iem = document.getElementById('<?php echo $truecode; ?>_embed');
			var qs = [];
			for(var att in iem.attributes) {
				if(iem.attributes[att].nodeName){
					if(iem.attributes[att].nodeName.substr(0,5) == 'data-'){
						//console.log(iem.attributes[att].nodeName.substr(5));
						qs.push(encodeURIComponent(iem.attributes[att].nodeName.substr(5)) + "=" + encodeURIComponent(iem.attributes[att].nodeValue));
					}
				}
			}
			if(qs.length > 0){
				url += '?'+qs.join('&');
			}

			/*if ('undefined' !== typeof <?php echo $truecode; ?>_config) {
				var qs = "";
					for(var key in <?php echo $truecode; ?>_config) {
					var value = <?php echo $truecode; ?>_config[key];
					qs += encodeURIComponent(key) + "=" + encodeURIComponent(value) + "&";
				}
				url += '?'+qs;
			}*/


			var embed_element = document.createElement("iframe");
			embed_element.setAttribute("id","embed_<?php echo $truecode; ?>");
			embed_element.setAttribute("allowTransparency","true");
			embed_element.setAttribute("frameBorder","0");
			<?php if(!empty($elementConfig['_embed-width'])){ ?>
			embed_element.setAttribute("width","<?php echo $elementConfig['_embed-width']; ?>");
			<?php }else{ ?>
			//url += '&__autowidth__=true';
			embed_element.setAttribute("width","100%");
			<?php } ?>
			<?php if(!empty($elementConfig['_embed-height'])){ ?>
				embed_element.setAttribute("height","<?php echo $elementConfig['_embed-height']; ?>");
			<?php }else{ ?>
				if(qs.length > 0){
					url += '&__autoheight__=true';
				}else{
					url += '?__autoheight__=true';
				}
			<?php } ?>
			embed_element.setAttribute("src",url);
			
			iem.parentNode.insertBefore(embed_element, iem);

<?php
exit;
}



global $footerOutput, $headerscripts, $javascript, $phpincludes, $contentPrefilters, $wp_scripts, $wp_styles;

// register code
$varData = array();
if(!empty($_GET)){
	$varData = array_merge_recursive(stripslashes_deep($_GET));
}
if(!empty($_POST)){
	//$varData = array_merge_recursive(stripslashes_deep($_POST));
}

$varData['_slug'] = $shortcode;
brickroad_register_element('render', $varData);



/// PATH STUFF
$cssReset = "html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block}body{line-height:1}ol,ul{list-style:none}blockquote,q{quotes:none}blockquote:before,blockquote:after,q:before,q:after{content:'';content:none}table{border-collapse:collapse;border-spacing:0}";


/*
$elements = get_option('BR_ELEMENTS');
if(!empty($elements)){
	foreach ($elements as $element => $options){
		if(!empty($options['shortcode'])){        
			foreach($used[2] as $currentKey=>$currentShortcode){
				if($options['shortcode'] == strtolower($used[2][$currentKey])){
					if(!empty($used[3][0])){
						$atts[$currentKey] = shortcode_parse_atts($used[3][$currentKey]);
					}else{
						$atts[$currentKey] = array();
					}
					$IDs[$currentKey] = $element;
					$shortcodes[$currentKey] = $options['shortcode'];
					$cfg = get_option($element);
				}
			}
		}
	}
}
if(!empty($IDs)){
	foreach($IDs as $currentKey=>$ID){        
		$instance[$currentKey] = brickroad_getDefaultAtts($ID, $atts[$currentKey]);
		brickroad_processHeaders($ID, $instance[$currentKey]);
	}
	if(isset($cfg['_defaultContent'])){
		$content = $cfg['_defaultContent'];
	}else{
		$content = "";
	}
	$outPutCode = brickroad_doShortcode($instance[0]['atts'], $content, $shortcodes[0]);
	
}
*/


//wp_dequeue_script
//dump($wp_styles);
//dump($wp_scripts);
	
//dump($wp_scripts);

// end pre process
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" style="overflow:hidden;">
	<head>
		<meta name="referrer" content="origin">
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
		<style type="text/css"><?php echo $cssReset; ?></style>
		<?php wp_footer(); ?>
	</head>
	<body>
		<?php
			echo brickroad_render_element('render');
		?>
	<?php if(!empty($_GET['__autoheight__']) || !empty($_GET['__autowidth__'])){ ?>
	<script type="text/javascript">
	<?php if(!empty($_GET['__autoheight__'])){ ?>

	setInterval(function(){
		////console.log(window);
		if (document.readyState === "complete" && null !== typeof window.frameElement){
			if(window.frameElement.height !== window.document.documentElement.offsetHeight){
				window.frameElement.height = window.document.documentElement.offsetHeight;
			}
		}
		//
	}, 100);
	<?php } ?>
	<?php if(!empty($_GET['__autowidth__'])){ ?>
		//setInterval(function(){
			////console.log(window.document.documentElement.offsetWidth);
			//window.frameElement.width = window.document.documentElement.offsetWidth;
		//},10);
	<?php } ?>

	</script>
	<?php } ?>
	</body>
</html>
















