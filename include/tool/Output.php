<?php

namespace gp\tool{

	defined('is_running') or die('Not an entry point...');

	global $GP_ARRANGE, $gpOutConf;

	$GP_ARRANGE	= true;
	$gpOutConf	= [];


	//named menus should just be shortcuts to the numbers in custom menu
	//	custom menu format: $top_level,$bottom_level,$expand_level

	//custom menu: 0,0,0,0
	$gpOutConf['FullMenu'] = [
		'class'		=> '\\gp\\tool\\Output\\Menu',
		'method'	=> 'GetFullMenu',
		'link'		=> 'all_links',
	];

	//custom menu: 0,0,1,1
	$gpOutConf['ExpandMenu'] = [
		'class'		=> '\\gp\\tool\\Output\\Menu',
		'method'	=> 'GetExpandMenu',
		'link'		=> 'expanding_links',
	];

	//custom menu: 0,0,2,1
	$gpOutConf['ExpandLastMenu'] = [
		'class'		=> '\\gp\\tool\\Output\\Menu',
		'method'	=> 'GetExpandLastMenu',
		'link'		=> 'expanding_bottom_links',
	];

	//custom menu: 0,1,0,0
	$gpOutConf['Menu'] = [
		'class'		=> '\\gp\\tool\\Output\\Menu',
		'method'	=> 'GetMenu',
		'link'		=> 'top_level_links',
	];

	//custom menu: 1,0,0,0
	$gpOutConf['SubMenu'] = [
		'class'		=> '\\gp\\tool\\Output\\Menu',
		'method'	=> 'GetSubMenu',
		'link'		=> 'subgroup_links',
	];

	//custom menu: 0,2,0,0
	$gpOutConf['TopTwoMenu'] = [
		'class'		=> '\\gp\\tool\\Output\\Menu',
		'method'	=> 'GetTopTwoMenu',
		'link'		=> 'top_two_links',
	];

	//custom menu: does not translate, this pays no attention to grouping
	$gpOutConf['BottomTwoMenu'] = [
		'class'		=> '\\gp\\tool\\Output\\Menu',
		'method'	=> 'GetBottomTwoMenu',
		'link'		=> 'bottom_two_links',
	];

	//custom menu: 1,2,0,0
	$gpOutConf['MiddleSubMenu'] = [
		'class'		=> '\\gp\\tool\\Output\\Menu',
		'method'	=> 'GetSecondSubMenu',
		'link'		=> 'second_sub_links',
	];

	//custom menu: 2,3,0,0
	$gpOutConf['BottomSubMenu'] = [
		'class'		=> '\\gp\\tool\\Output\\Menu',
		'method'	=> 'GetThirdSubMenu',
		'link'		=> 'third_sub_links',
	];

	//custom menu
	$gpOutConf['CustomMenu'] = [
		'class'		=> '\\gp\\tool\\Output\\Menu',
		'method'	=> 'CustomMenu',
	];

	//breadcrumb nav
	$gpOutConf['Breadcrumbs'] = [
		'class'		=> '\\gp\\tool\\Output\\Menu',
		'method'	=> 'BreadcrumbNav',
		'link'		=> 'Breadcrumb Links',
	];
	//$gpOutConf['Breadcrumbs']['method']	= ['\\gp\\tool\\Output', 'BreadcrumbNav'];
	//$gpOutConf['Breadcrumbs']['link']		= 'Breadcrumb Links';


	$gpOutConf['Extra']['method']			= ['\\gp\\tool\\Output', 'GetExtra'];

	//$gpOutConf['Text']['method']			= ['\\gp\\tool\\Output','GetText']; //use Area() and GetArea() instead

	//$gpOutConf['Image']['method']			= ['\\gp\\tool\\Output','GetImage'];

	/* The following methods should be used with \gp\tool\Output'::Fetch() */
	$gpOutConf['Gadget']['method']			= ['\\gp\\tool\\Output', 'GetGadget'];


	class Output{

		public static $components			= '';
		public static $editlinks			= '';
		public static $template_included	= false;

		private static $out_started			= false;
		private static $gadget_cache		= [];

		public static $edit_area_id			= '';

		private static $catchable			= [];

		public static $lang_values			= [];
		public static $inline_vars			= [];
		public static $nested_edit			= false;

		private static $edit_index			= 0;

		private static $head_css			= '';
		private static $head_content		= '';
		private static $head_js				= '';


		/**
		 * Backwards compat for functions moved to \gp\tool\Output\Menu
		 *
		 */
		public static function __callStatic($name, $args){

			if( method_exists('\\gp\\tool\\Output\\Menu', $name) ){
				$menu = new \gp\tool\Output\Menu();
				return call_user_func_array([$menu, $name], $args);
			}

			throw new \Exception('Call to undefined method gp\\tool\\Output::' . $name);
		}


		/*
		 *
		 * Request Type Functions
		 * functions used in conjuction with $_REQUEST['gpreq']
		 *
		 */


		 public static function Prep(){
			global $page;
			if( !isset($page->rewrite_urls) ){
				return;
			}

			ini_set('arg_separator.output', '&amp;');
			foreach($page->rewrite_urls as $key => $value){
				output_add_rewrite_var($key, $value);
			}
		}


		/**
		 * Send only messages and the content buffer to the client
		 * @static
		 */
		public static function Flush(){
			global $page;
			self::StandardHeaders();
			echo GetMessages();
			echo $page->contentBuffer;
		}


		public static function Content(){
			global $page;
			self::StandardHeaders();
			echo GetMessages();
			$page->GetGpxContent();
		}


		public static function StandardHeaders(){
			header('Content-Type: text/html; charset=utf-8');
			Header('Vary: Accept,Accept-Encoding'); // for proxies
		}


		/**
		 * Send only the messages and content as a simple html document
		 * @static
		 */
		public static function BodyAsHTML(){
			global $page;

			self::$inline_vars['gp_bodyashtml']	= true;

			self::TemplateSettings();

			self::StandardHeaders();

			echo '<!DOCTYPE html>';
			echo '<html lang="' . $page->lang . '"><head><meta charset="UTF-8" />';
			self::getHead();
			echo '</head>';

			echo '<body class="gpbody">';
			echo GetMessages();

			$page->GetGpxContent();

			echo '</body>';
			echo '</html>';

			self::HeadContent();
		}


		public static function AdminHtml(){
			global $page;

			//\gp\tool\Output::$inline_vars['gp_bodyashtml']	= true;

			self::StandardHeaders();

			echo '<!DOCTYPE html>';
			echo '<html class="admin_body" lang="' . $page->lang . '"><head><meta charset="UTF-8" />';
			self::getHead();
			echo '</head>';

			echo '<body class="gpbody">';
			echo GetMessages();

			$page->GetGpxContent();

			echo '</body>';
			echo '</html>';

			self::HeadContent();
		}


		/**
		 * Get default values from customizer if it exists
		 * return empty array otherwise
		 *
		 * Layout installer will pass a customizer file path
		 *
		 * @static
		 * @since 5.2
		 * @param string $used_in
		 * @param string $customizer_file
		 * @return array
		 *
		 */
		public static function GetCustomizerDefaults($used_in='', $customizer_file=''){
			global $page;

			if( empty($customizer_file) ){
				$layout_dir			= $page->theme_dir . '/' . $page->theme_color;
				$customizer_file	= $layout_dir . '/customizer.php';
			}

			if( !file_exists($customizer_file) ){
				// msg('customizer file ' . htmlspecialchars($customizer_file) . ' does not exist'); // TODO remove
				return [];
			};

			$customizer = \gp\tool\Files::Get($customizer_file, 'customizer');
			// debug('$customizer = ' . pre($customizer));
			$defaults		= [];

			foreach($customizer as $section => $section_data){

				foreach($section_data['items'] as $item_name => $item_data){
					if( !empty($used_in) &&
						isset($item_data['control']['used_in']) &&
						is_array($item_data['control']['used_in']) &&
						!in_array($used_in, $item_data['control']['used_in'])
					){
						continue;
					}

					$defaults[$item_name]['value'] = $item_data['default_value'];
					if( !empty($item_data['default_units']) ){
						$defaults[$item_name]['units'] = $item_data['default_units'];
					}
				}
			}

			return $defaults;
		}


		/**
		 * Get javascript values from the layout
		 * if the layout has no stored js_vals, try to get customizer defaults
		 * otherwise return empty string
		 *
		 * @static
		 * @since 5.2
		 * @return string js expression like 'var layout_config = JSON;'
		 *
		 */
		public static function GetLayoutJsVars(){
			global $page, $gpLayouts;

			if( $page->gpLayout ){
				$layout_info = $gpLayouts[$page->gpLayout];
				if( isset($layout_info['js_vars']) ){
					return $layout_info['js_vars'];
				}
			}

			$js_vars = self::GetCustomizerDefaults('js');
			return "\n" . 'var layout_config = ' . json_encode($js_vars) . ';' . "\n";
		}


		/**
		 * Get configuration array from the layout
		 * if the layout has no stored config, try to get customizer defaults
		 * otherwise return empty array
		 *
		 * @static
		 * @since 5.2
		 * @return array layout configuration
		 *
		 */
		public static function GetLayoutConfig(){
			global $page, $gpLayouts;

			if( $page->gpLayout ){
				$layout_info = $gpLayouts[$page->gpLayout];
				if( isset($layout_info['config']) ){
					return $layout_info['config'];
				}
			}

			return self::GetCustomizerDefaults('php');
		}


		/**
		 * Send all content according to the current layout
		 * @static
		 *
		 */
		public static function Template(){
			global $page, $gpLayouts, $layout_config;
			global $GP_ARRANGE, $GP_STYLES, $get_all_gadgets_called;
			global $addon_current_id, $GP_MENU_LINKS, $GP_MENU_CLASS;
			global $GP_MENU_CLASSES, $GP_MENU_ELEMENTS;

			$get_all_gadgets_called		= false;
			self::$template_included	= true;

			if( isset($page->theme_addon_id) ){
				$addon_current_id = $page->theme_addon_id;
			}
			self::TemplateSettings();

			self::StandardHeaders();

			if( !empty($page->preview_layout_config) ){
				// only exists in Layout Editor preview mode
				$layout_config = $page->preview_layout_config;
			}elseif( empty($layout_config) ){
				$layout_config = self::GetLayoutConfig();
			}
			// debug('$layout_config = ' . pre($layout_config));

			$path = $page->theme_dir . '/template.php';

			$return = IncludeScript(
				$path,
				'require',
				[
					'page', 'layout_config',
					'GP_ARRANGE',
					'GP_MENU_LINKS', 'GP_MENU_CLASS',
					'GP_MENU_CLASSES', 'GP_MENU_ELEMENTS'
				]
			);

			//return will be false if there's a fatal error with the template.php file
			if( $return === false ){
				self::BodyAsHtml();
			}
			\gp\tool\Plugins::ClearDataFolder();

			self::HeadContent();
		}


		/**
		 * Get the settings for the current theme if settings.php exists
		 * @static
		 */
		public static function TemplateSettings(){
			global $page;

			$path = $page->theme_dir . '/settings.php';
			IncludeScript($path, 'require_if', ['page', 'GP_GETALLGADGETS']);
		}


		/**
		 * Add a Header to the response
		 * The header will be discarded if it's an ajax request or similar
		 *
		 * @param string $header
		 * @param bool $replace
		 * @param int $code
		 * @return bool
		 */
		public static function AddHeader($header, $replace=true, $code=null){
			if( !empty($_REQUEST['gpreq']) ){
				return false;
			}
			if( !is_null($code) ){
				\gp\tool::status_header($code, $header);
			}else{
				header($header, $replace);
			}
			return true;
		}


		/*
		 *
		 * Content Area Functions
		 *
		 */

		 public static function GetContainerID($name, $arg=false){
			static $indices;

			$name = str_replace(
				['+', '/', '='],
				['', '', ''],
				base64_encode($name)
			);
			if( !isset($indices[$name]) ){
				$indices[$name] = 0;
			}else{
				$indices[$name]++;
			}
			return $name . '_' . $indices[$name];
		}


		/**
		 * Fetch the output and return as a string
		 *
		 */
		public static function Fetch($default, $arg=''){
			ob_start();
			self::Get($default, $arg);
			return ob_get_clean();
		}


		public static function Get($default='', $arg=''){
			global $page, $gpLayouts, $gpOutConf;

			$outSet = false;
			$outKeys = false;

			$layout_info =& $gpLayouts[$page->gpLayout];

			//container id
			$container_id	= $default . ':' . substr($arg, 0, 10);
			$container_id	= self::GetContainerID($container_id);

			if( isset($layout_info) && isset($layout_info['handlers']) ){
				$handlers =& $layout_info['handlers'];
				if( isset($handlers[$container_id]) ){
					$outKeys	= $handlers[$container_id];
					$outSet		= true;
				}
			}

			//default values
			$outKeys =array();
			if( !$outSet && isset($gpOutConf[$default]) ){
				$outKeys[] = trim($default . ':' . $arg, ':');
			}

			self::ForEachOutput($outKeys, $container_id);
		}


		public static function ForEachOutput($outKeys, $container_id){

			if( !is_array($outKeys) || (count($outKeys) == 0) ){
				$info				= [];
				$info['gpOutCmd']	= '';
				self::CallOutput($info, $container_id);
				return;
			}

			foreach($outKeys as $gpOutCmd){
				$info = self::GetgpOutInfo($gpOutCmd);
				if( $info === false ){
					trigger_error('gpOutCmd <i>' . $gpOutCmd . '</i> not set');
					continue;
				}
				$info['gpOutCmd'] = $gpOutCmd;
				self::CallOutput($info, $container_id);
			}
		}


		/* static */
		public static function GetgpOutInfo($gpOutCmd){
			global $gpOutConf, $config;

			$key	= $gpOutCmd = trim($gpOutCmd, ':');
			$info	= false;
			$arg	= '';
			$pos	= mb_strpos($key, ':');

			if( $pos > 0 ){
				$arg = mb_substr($key, $pos + 1);
				$key = mb_substr($key, 0, $pos);
			}

			if( isset($gpOutConf[$key]) ){
				$info = $gpOutConf[$key];
			}elseif( isset($config['gadgets'][$key]) ){
				$info = $config['gadgets'][$key];
				$info['is_gadget'] = true;
			}else{
				return false;
			}

			$info['key']		= $key;
			$info['arg']		= $arg;
			$info['gpOutCmd']	= $gpOutCmd;

			return $info;
		}



		public static function GpOutLabel($info){
			global $langmessage;

			$info += ['arg' => '']; // suppress warning with older themes TODO: check the cause
			$label = $info['arg'];
			if( empty($label) ){
				$label = $info['gpOutCmd'];
			}

			if( isset($info['link']) && isset($langmessage[$info['link']]) ){
				$label = $langmessage[$info['link']];
			}

			return str_replace([' ', '_', ':'], ['&nbsp;', '&nbsp;', ':&nbsp;'], $label);
		}


		public static function CallOutput($info,$container_id){
			global $GP_ARRANGE, $page, $langmessage, $GP_MENU_LINKS;
			global $GP_MENU_CLASS, $GP_MENU_CLASSES, $gp_current_container;

			$gp_current_container	= $container_id;
			self::$out_started		= true;
			self::$edit_area_id		= '';

			if( isset($info['disabled']) ){
				return;
			}

			//gpOutCmd identifies the output function used, there can only be one
			if( !isset($info['gpOutCmd']) ){
				trigger_error('gpOutCmd not set for $info in CallOutput()');
				return;
			}

			//generate a class based on the area $info
			if( isset($info['html']) ){
				$class = $info['key'];
				$class = preg_replace('#\[.*\]#', '', $class);
			}else{
				$class = $info['gpOutCmd'];
			}

			//add gpMenu class to all menu areas for better styling
			$add_menu_class = '';
			if( isset($info['class']) && $info['class'] == '\gp\tool\Output\Menu' ){
				$add_menu_class = 'gpMenu ';
			}
			$class			= $add_menu_class . 'gpArea_' . str_replace([':', ','], ['_', ''], trim($class, ':'));
			$param			= $container_id . '|' . $info['gpOutCmd'];
			$permission		= self::ShowEditLink('Admin_Theme_Content');


			ob_start();

			//for theme content arrangement
			if( $GP_ARRANGE && $permission && isset($GLOBALS['GP_ARRANGE_CONTENT']) ){
				$empty_container = empty($info['gpOutCmd']); //empty containers can't be removed and don't have labels
				$class .= ' gp_output_area';

				echo '<div class="gp_inner_links nodisplay"><div>';
				echo \gp\tool::Link(
					'Admin_Theme_Content/Edit/' . $page->gpLayout,
					$param,
					'cmd=DragArea&dragging=' . urlencode($param) . '&to=%s',
					['data-cmd' => 'creq', 'class' => 'dragdroplink nodisplay']
				); //drag-drop link

				echo '<div class="output_area_label">';
				if( $empty_container ){
					echo $langmessage['Empty Container'];
				}else{
					echo self::GpOutLabel($info);
				}
				echo '</div>';

				echo '<div class="output_area_link">';
				echo ' ' . \gp\tool::Link(
					'Admin_Theme_Content/Edit/' . $page->gpLayout,
					'<i class="fa fa-plus"></i> ' . $langmessage['insert'],
					'cmd=SelectContent&param=' . $param,
					['data-cmd' => 'gpabox']
				);
				if( !$empty_container ){
					echo ' ' . \gp\tool::Link(
						'Admin_Theme_Content/Edit/' . $page->gpLayout,
						'<i class="fa fa-times"></i> ' . $langmessage['remove'],
						'cmd=RemoveArea&param=' . $param,
						['data-cmd' => 'creq']
					);
				}
				echo '</div>';

				echo '</div></div>';

			}

			//editable links only .. other editable_areas are handled by their output functions
			if( $permission ){
				if( isset($info['link']) ){
					$label = $langmessage[$info['link']];

					$edit_link = self::EditAreaLink(
						$edit_index,
						'Admin_Theme_Content/Edit/' . urlencode($page->gpLayout),
						$langmessage['edit'],
						'cmd=LayoutMenu&handle=' . $param,
						['data-cmd' => 'gpabox', 'title' => $label ]
					);
					echo '<span class="nodisplay" id="ExtraEditLnks' . $edit_index . '">';
					echo $edit_link;
					echo \gp\tool::Link(
						'Admin/Menu',
						$langmessage['file_manager'],
						'',
						['class' => 'nodisplay']
					);
					//call to current also not needed, there will only be 1 entry);
					echo '</span>';

					self::$edit_area_id = 'ExtraEditArea'.$edit_index;

				}elseif( isset($info['key']) && ($info['key'] == 'CustomMenu') ){

					$edit_link = self::EditAreaLink(
						$edit_index,
						'Admin_Theme_Content/Edit/' . urlencode($page->gpLayout),
						$langmessage['edit'],
						'cmd=LayoutMenu&handle=' . $param,
						['data-cmd' => 'gpabox', 'title' => $langmessage['Links']]
					);

					echo '<span class="nodisplay" id="ExtraEditLnks' . $edit_index . '">';

					echo $edit_link;

					echo \gp\tool::Link(
						'Admin/Menu',
						$langmessage['file_manager'],
						'',
						['class' => 'nodisplay']
					);

					echo '</span>';

					self::$edit_area_id = 'ExtraEditArea' . $edit_index;
				}
			}

			self::$editlinks .= ob_get_clean();

			echo '<div class="' . $class . ' GPAREA">';
			self::ExecArea($info);
			echo '</div>';

			$GP_ARRANGE				= true;
			$gp_current_container	= false;
		}


		public static function ExecArea($info){
			//retreive from gadget cache if set
			if( isset($info['gpOutCmd']) ){
				$gadget = $info['gpOutCmd'];
				if( substr($gadget, 0, 7) == 'Gadget:' ){
					$gadget = substr($gadget, 7);
				}
				if( isset(self::$gadget_cache[$gadget]) ){
					echo self::$gadget_cache[$gadget];
					return;
				}
			}

			$info += ['arg' => ''];
			$args = [$info['arg'], $info];

			$info = \gp\tool\Plugins::Filter('ExecArea', [$info, $args]);
			if( !$info ){
				return;
			}

			self::ExecInfo($info, $args);
		}


		/**
		 * Execute a set of directives for theme areas, hooks and special pages
		 *
		 */
		public static function ExecInfo($info, $args=[]){
			global $addonFolderName, $installed_addon, $page;

			$args += ['page' => $page];

			//addonDir is deprecated as of 2.0b3
			$addon = false;
			if( isset($info['addonDir']) ){
				$addon = $info['addonDir'];
			}elseif( isset($info['addon']) ){
				$addon = $info['addon'];
			}

			if( $addon !== false ){
				if( gp_safe_mode ){
					return $args;
				}
				\gp\tool\Plugins::SetDataFolder($addon);
			}

			//if addon was just installed
			if( $installed_addon && $installed_addon === $addonFolderName){
				\gp\tool\Plugins::ClearDataFolder();
				return $args;
			}

			// check for fatal errors
			if( self::FatalNotice('exec', $info) ){
				return $args;
			}

			try{
				$args = self::_ExecInfo($info, $args);
			}catch(\Throwable $e){
				\showError(E_ERROR,
					'ExecInfo() Fatal Error: ' . $e->getMessage(),
					$e->GetFile(),
					$e->GetLine(),
					[],
					$e->getTrace()
				);
			}

			if( $addon !== false ){
				\gp\tool\Plugins::ClearDataFolder();
			}

			self::PopCatchable();

			return $args;
		}


		public static function _ExecInfo($info, $args=[]){
			global $dataDir, $gp_overwrite_scripts;

			// get data
			if( !empty($info['data']) ){
				IncludeScript($dataDir . $info['data'], 'include_if', ['page', 'dataDir', 'langmessage']);
			}

			// get script
			$has_script = false;
			if( !empty($info['script']) ){

				if( is_array($gp_overwrite_scripts) && isset($gp_overwrite_scripts[$info['script']]) ){
					$full_path = $gp_overwrite_scripts[$info['script']];
				}else{
					$full_path = $dataDir . $info['script'];
				}

				if( !file_exists($full_path) ){
					self::ExecError(\CMS_NAME . ' Error: Addon hook script doesn\'t exist.', $info, 'script');
					return $args;
				}

				if( IncludeScript($full_path, 'include_once', ['page', 'dataDir', 'langmessage']) ){
					$has_script = true;
				}
			}

			//class & method execution
			if( !empty($info['class_admin']) && \gp\tool::LoggedIn() ){
				return self::ExecClass($has_script, $info['class_admin'], $info, $args);
			}elseif( !empty($info['class']) ){
				return self::ExecClass($has_script, $info['class'], $info, $args);
			}

			//method execution
			if( !empty($info['method']) ){
				return self::ExecMethod($has_script, $info, $args);
			}

			return $args;
		}


		/**
		 * Execute hooks that have a ['class'] defined
		 *
		 */
		private static function ExecClass($has_script, $exec_class, $info, $args){

			if( !class_exists($exec_class) ){
				self::ExecError(\CMS_NAME . ' Error: Addon class doesn\'t exist.', $info, 'class');
				return $args;
			}

			$object = new $exec_class($args);

			if( !empty($info['method']) ){
				if( method_exists($object, $info['method']) ){
					$args[0] = call_user_func_array([$object, $info['method']], array_values($args));
				}elseif( $has_script ){
					self::ExecError(\CMS_NAME . ' Error: Addon hook method doesn\'t exist (1).', $info, 'method');
				}
			}
			return $args;
		}


		/**
		 * Execute hooks that have a ['method'] defined
		 *
		 */
		private static function ExecMethod($has_script, $info, $args){

			$callback = $info['method'];

			//object callbacks since 3.0
			if( is_string($callback) && strpos($callback, '->') !== false ){
				$has_script = true;
				list($object, $method) = explode('->', $callback);
				if( isset($GLOBALS[$object]) &&
					is_object($GLOBALS[$object]) &&
					method_exists($GLOBALS[$object], $method)
				){
					$callback = [$GLOBALS[$object], $method];
				}
			}

			if( is_callable($callback) ){
				$args[0] = call_user_func_array($callback, array_values($args));
			}elseif( $has_script ){
				self::ExecError(\CMS_NAME.' Error: Addon hook method doesn\'t exist (2).', $info, 'method');
			}

			return $args;
		}


		/**
		 * Trigger an error
		 *
		 */
		public static function ExecError($msg, $exec_info, $error_info){
			global $config, $addonFolderName;

			// append addon name
			if( !empty($addonFolderName) && isset($config['addons'][$addonFolderName]) ){
				$msg	.= ' Addon: ' . $config['addons'][$addonFolderName]['name'] . '. ';
			}

			// which piece of $exec_info is the problem
			if( !isset($exec_info[$error_info]) ){
				$msg	.= $error_info;
			}elseif( is_array($exec_info[$error_info]) ){
				$msg	.= $error_info . ': ' . implode('::', $exec_info[$error_info]);
			}else{
				$msg	.= $error_info . ': ' . $exec_info[$error_info];
			}

			trigger_error($msg);
		}


		/**
		 * Check for fatal errors corresponing to $hash
		 * Notify administrators of disabled components
		 *
		 */
		public static function FatalNotice($type, $info){
			global $dataDir, $page;
			static $notified = false;

			$info					= (array)$info;
			$info['catchable_type']	= $type;

			$hash_dir				= $dataDir . '/data/_site/fatal_' . $type . '_' . \gp\tool::ArrayHash($info);
			$hash_request			= $hash_dir . '/' . \gp\tool::ArrayHash($_REQUEST);

			self::$catchable[$hash_request]	= $info;

			if( !self::FatalLimit($hash_dir) ){
				return false;
			}

			if( !$notified ){
				error_log( 'Warning: A component of this page has been disabled because it caused fatal errors' );
				$notified = true;
			}

			self::PopCatchable();

			return true;
		}


		/**
		 * Return true if the limit of fatal errors has been reached
		 *
		 */
		public static function FatalLimit($hash_dir){

			//no folder = no fatal error
			if( !file_exists($hash_dir) ){
				return false;
			}

			// if the error didn't occur for the exact request and it hasn't happend a lot, allow the code to keep working
			$fatal_hashes = scandir($hash_dir);
			if( $fatal_hashes !== false && count($fatal_hashes) < (gp_allowed_fatal_errors + 3) ){
				// add 3 for ".", ".." and "index.html" entries
				return false;
			}

			return true;
		}


		public static function PopCatchable(){
			array_pop(self::$catchable);
		}


		/**
		 * Determine if an inline edit link should be shown for the current user
		 *
		 * @param string $permission
		 * @return bool
		 */
		public static function ShowEditLink($permission=null){

			if( !is_null($permission) ){
				return !self::$nested_edit && \gp\tool::LoggedIn() && \gp\admin\Tools::HasPermission($permission);
			}
			return !self::$nested_edit && \gp\tool::LoggedIn();
		}


		/**
		 * @param int $index
		 * @param string $href
		 * @param string $label
		 * @param string $query
		 * @param string|array $attr
		 *
		 */
		public static function EditAreaLink(&$index, $href, $label, $query='', $attr=''){
			self::$edit_index++;
			$index = self::$edit_index; //since &$index is passed by reference

			if( is_array($attr) ){
				$attr += [
					'class'				=> 'ExtraEditLink nodisplay',
					'id'				=> 'ExtraEditLink' . $index,
					'data-gp-area-id'	=> $index,
				];
			}else{
				$attr .= ' class="ExtraEditLink nodisplay" ' .
					'id="ExtraEditLink' . $index . '" ' .
					'data-gp-area-id="' . $index . '"';
			}
			return \gp\tool::Link($href, $label, $query, $attr);
		}


		/**
		 * Unless the gadget area is customized by the user, this function will output all active gadgets
		 * If the area has been reorganized, it will output the customized areas
		 * This function is not called from \gp\tool\Output::Get('GetAllGadgets')
		 * so that each individual gadget area can be used as a drag area
		 *
		 */
		public static function GetAllGadgets(){
			global $config, $page, $gpLayouts, $get_all_gadgets_called;
			$get_all_gadgets_called = true;

			//if we have handler info
			if( isset($gpLayouts[$page->gpLayout]['handlers']['GetAllGadgets']) ){
				self::ForEachOutput($gpLayouts[$page->gpLayout]['handlers']['GetAllGadgets'], 'GetAllGadgets');
				return;
			}

			//show all gadgets if no changes have been made
			if( !empty($config['gadgets']) ){
				$count = 0;
				foreach($config['gadgets'] as $gadget => $info){
					if( isset($info['addon']) ){
						$info['gpOutCmd'] = $info['key'] = $gadget;
						self::CallOutput($info, 'GetAllGadgets');
						$count++;
					}
				}
				if( $count ){
					return;
				}
			}

			//Show the area as editable if there isn't anything to show
			$info				= [];
			$info['gpOutCmd']	= '';
			self::CallOutput($info, 'GetAllGadgets');
		}


		/**
		 * Simply determine if a Gadget exists
		 * allows us to check whether a plugin gadget exists before loading it in template.php
		 * @since 5.2-rc
		 * @param string Gadget id
		 * @return boolean
		 */
		public static function GadgetExists($id){
			global $config;
			return isset($config['gadgets'][$id]);
		}


		/**
		 * Get a Single Gadget
		 * This method should be called using \gp\tool\Output::Fetch('Gadget',$gadget_name)
		 *
		 */
		public static function GetGadget($id){
			global $config;

			if( !isset($config['gadgets'][$id]) ){
				return;
			}

			self::ExecArea($config['gadgets'][$id]);
		}


		/**
		 * Prepare the gadget content before getting template.php
		 * so that gadget functions can add css and js to the head
		 * @return null
		 */
		public static function PrepGadgetContent(){
			global $page;

			//not needed for admin pages
			if( $page->pagetype == 'admin_display' ){
				return;
			}

			$gadget_info = self::WhichGadgets($page->gpLayout);

			foreach($gadget_info as $gpOutCmd => $info){
				if( !isset(self::$gadget_cache[$gpOutCmd]) ){
					ob_start();
					self::ExecArea($info);
					self::$gadget_cache[$gpOutCmd] = ob_get_clean();
				}
			}
		}


		/**
		 * Return information about the gadgets being used in the current layout
		 * @return array
		 */
		public static function WhichGadgets($layout){
			global $config, $gpLayouts;

			$gadget_info = $temp_info = [];
			if( !isset($config['gadgets']) ){
				return $gadget_info;
			}

			$layout_info = & $gpLayouts[$layout];

			$GetAllGadgets = true;
			if( isset($layout_info['all_gadgets']) && !$layout_info['all_gadgets'] ){
				$GetAllGadgets = false;
			}

			if( isset($layout_info['handlers']) ){
				foreach($layout_info['handlers'] as $handler => $out_cmds){
					//don't prep even if GetAllGadgets is set in the layout's config
					if( $handler == 'GetAllGadgets' && !$GetAllGadgets ){
						continue;
					}
					foreach($out_cmds as $gpOutCmd){
						$temp_info[$gpOutCmd] = self::GetgpOutInfo($gpOutCmd);
					}
				}
			}

			//add all gadgets if $GetAllGadgets is true and the GetAllGadgets handler isn't overwritten
			if( $GetAllGadgets && !isset($layout_info['handlers']['GetAllGadgets']) ){
				foreach($config['gadgets'] as $gadget => $temp){
					if( isset($temp['addon']) ){
						$temp_info[$gadget] = self::GetgpOutInfo($gadget);
					}
				}
			}

			foreach($temp_info as $gpOutCmd => $info){
				if( isset($info['is_gadget']) &&
					$info['is_gadget'] &&
					!isset($info['disabled'])
				){
					$gadget_info[$gpOutCmd] = $info;
				}
			}

			return $gadget_info;
		}


		public static function GetExtra($name='Side_Menu', $info=[]){
			echo \gp\tool\Output\Extra::GetExtra($name);
		}


		public static function GetImage($src, $attributes=[]){
			global $page, $dataDir, $langmessage, $gpLayouts;

			//$width,$height,$attributes = ''
			$attributes				= (array)$attributes;
			$attributes				+= ['class' => ''];
			$attributes['class'] 	.= 'GPAREA filetype-image';
			unset($attributes['id']);

			//default image information
			$img_rel = dirname($page->theme_rel) . '/' . ltrim($src, '/');

			//container id
			$container_id = 'Image:' . $src;
			$container_id = self::GetContainerID($container_id);

			//select custom image
			if( isset($gpLayouts[$page->gpLayout]) &&
				isset($gpLayouts[$page->gpLayout]['images']) &&
				isset($gpLayouts[$page->gpLayout]['images'][$container_id]) &&
				is_array($gpLayouts[$page->gpLayout]['images'][$container_id])
			){
				//shuffle($gpLayouts[$page->gpLayout]['images'][$container_id]);
				//Does not make sense ? There will always be only 1 entry in
				//for this container as it is per img element
				//call to current also not needed, there will only be 1 entry
				$image = $gpLayouts[$page->gpLayout]['images'][$container_id][0];

				$img_full = $dataDir.$image['img_rel'];
				if( file_exists($img_full) ){
					$img_rel				= $image['img_rel'];
					$attributes['width']	= $image['width'];
					$attributes['height']	= $image['height'];
				}
			}

			//attributes
			if( !isset($attributes['alt']) ){
				$attributes['alt'] = '';
			}

			//edit options
			$editable = self::ShowEditLink('Admin_Theme_Content');
			if( $editable ){
				$edit_link = self::EditAreaLink(
					$edit_index,
					'Admin_Theme_Content/Image/' . $page->gpLayout,
					$langmessage['edit'],
					'file=' . rawurlencode($img_rel) . '&container=' . $container_id . '&time=' . time(),
					['title' => 'Edit Image', 'data-cmd' => 'inline_edit_generic' ]
				);
				self::$editlinks 		.= '<span class="nodisplay" id="ExtraEditLnks' .
												$edit_index . '">' .
												$edit_link .
											'</span>';
				$attributes['class']	.= ' editable_area';
				$attributes['id']		= 'ExtraEditArea' . $edit_index;
			}

			//remove class if empty, commented because class will never be empty anymore because of filetype-image
			// $attributes['class'] = trim($attributes['class']);
			// if( empty($attributes['class']) ){
			//	unset($attributes['class']);
			//}

			//convert attributes to string
			$str = '';
			foreach($attributes as $key => $value){
				$str .= ' ' . $key . '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false) . '"';
			}
			echo '<img src="' . \gp\tool::GetDir($img_rel, true) . '"' . $str . '/>';
		}


		/*
		 *
		 * Output Additional Areas
		 *
		 */

		/* draggable html and editable text */
		public static function Area($name,$html){
			global $gpOutConf;
			if( self::$out_started ){
				trigger_error('\gp\tool\Output::Area() must be called before all other output functions');
				return;
			}
			$name 						= '[text]' . $name;
			$gpOutConf[$name]			= [];
			$gpOutConf[$name]['method']	= ['\\gp\\tool\\Output', 'GetAreaOut'];
			$gpOutConf[$name]['html']	= $html;
		}


		public static function GetArea($name, $text){
			$name = '[text]' . $name;
			self::Get($name, $text);
		}


		public static function GetAreaOut($text,$info){
			global $config, $langmessage, $page;

			$html =& $info['html'];

			$wrap = self::ShowEditLink('Admin_Theme_Content');
			if( $wrap ){
				self::$editlinks .= self::EditAreaLink(
					$edit_index,
					'Admin_Theme_Content/Text',
					$langmessage['edit'],
						'cmd=EditText&key=' . urlencode(is_null($text) ? "" : urlencode($text)) . '&return=' . urlencode(is_null($page->title) ? "" : urlencode($page->title)),
					['title' => htmlspecialchars($text), 'data-cmd'	=> 'gpabox']
				);
				echo '<div class="editable_area inner_size" id="ExtraEditArea' . $edit_index . '">';
				// class="edit_area" added by javascript
			}

			if( isset($config['customlang'][$text]) ){
				$text = $config['customlang'][$text];

			}elseif( isset($langmessage[$text]) ){
				$text = $langmessage[$text];
			}

			echo str_replace('%s', $text, $html); //in case there's more than one %s

			if( $wrap ){
				echo '</div>';
			}
		}


		/**
		 * Get additional CSS classes that can be added to the
		 * html or body elements' class atrribute
		 * e.g. to display an larger header only on the homepage
		 * Use in template.php: <html class="<?php gpOutput::GetPageInfoClasses(); ?>">
		 * @since 5.2-rc
		 * @param boolean $echo_classes (defaults to true), output the class names as space-separated string
		 * @return array of css class names that apply
		 *
		 */
		public static function GetPageInfoClasses($echo_classes=true){
			global $page;

			$classes = [];

			if( self::is_front_page() ){
				$classes[] = 'is-homepage';
			}

			if( $page->pagetype == 'special_display' ){
				$classes[] = 'is-special-page';
			}

			switch( $page->gp_index ){
				case 'special_contact':
					$classes[] = 'is-contactform-page';
					break;

				case 'special_galleries':
					$classes[] = 'is-galleries-page';
					break;

				case 'special_gpsearch':
					$classes[] = 'is-search-page';
					break;

				case 'special_missing':
					$classes[] = 'is-missing-page';
					break;

				case 'special_blog':
					$classes[] = 'is-blog-page';
					if( isset($_GET['id']) ){
						$classes[] = 'is-single-post-page';
					}
					break;

				case 'special_blog_categories':
					$classes[] = 'is-blog-page';
					$classes[] = 'is-blog-categories-page';
					if( isset($_GET['cat']) ){
						$classes[] = 'is-single-category-page';
					}
					break;
			}

			if( $echo_classes && !empty($classes) ){
				echo ' ' . implode(' ', $classes) . ' ';
			}

			return $classes;
		}


		/*
		 *
		 * editable text, not draggable
		 *
		 *
		 */

		/**
		 * similar to ReturnText() but links to script for editing all addon texts
		 * the $html parameter should primarily be used when the text is to be placed
		 * inside of a link or other element that cannot have a link and/or span as a child node
		 */
		public static function GetAddonText($key, $html='%s', $wrapper_class=''){
			global $addonFolderName;

			if( !$addonFolderName ){
				return self::ReturnText($key, $html, $wrapper_class);
			}

			$query = 'cmd=AddonTextForm&addon=' . urlencode($addonFolderName) . '&key=' . urlencode($key);
			return self::ReturnTextWorker($key, $html, $query, $wrapper_class);
		}


		public static function ReturnText($key,$html='%s', $wrapper_class=''){
			$query = 'cmd=EditText&key='.urlencode($key);
			return self::ReturnTextWorker($key, $html, $query, $wrapper_class);
		}


		public static function ReturnTextWorker($key, $html, $query, $wrapper_class=''){
			global $langmessage;

			$text		= self::SelectText($key);
			$result		= str_replace('%s', $text, $html); //in case there's more than one %s

			$editable	= self::ShowEditLink('Admin_Theme_Content');
			if( $editable ){

				$title = htmlspecialchars(strip_tags($key));
				if( strlen($title) > 20 ){
					$title = substr($title, 0, 20) . '...'; //javscript may shorten it as well
				}

				self::$editlinks .= self::EditAreaLink(
					$edit_index,
					'Admin_Theme_Content/Text',
					$langmessage['edit'],
					$query,
					['title' => $title, 'data-cmd' => 'gpabox']
				);
				return '<span class="editable_area ' . $wrapper_class .'" '
				 . 'id="ExtraEditArea' . $edit_index . '">' . $result . '</span>';
			}

			if( $wrapper_class ){
				return '<span class="' . $wrapper_class . '">' . $result . '</span>';
			}

			return $result;
		}


		/**
		 * Returns the user translated string if it exists or
		 * $key (the untranslated string) if a translation doesn't exist
		 *
		 */
		public static function SelectText($key){
			global $config,$langmessage;

			$text = $key;
			if( isset($config['customlang'][$key]) ){
				$text = $config['customlang'][$key];

			}elseif( isset($langmessage[$key]) ){
				$text = $langmessage[$key];
			}
			return $text;
		}


		/*
		 *
		 * Generate and output the <head> portion of the html document
		 *
		 */

		 public static function GetHead(){
			\gp\tool\Plugins::Action('GetHead');
			self::PrepGadgetContent();
			echo '<!-- get_head_placeholder ' . \gp_random . ' -->';
		}


		public static function HeadContent(){
			global $config, $page, $wbMessageBuffer;

			//before ob_start() so plugins can get buffer content
			\gp\tool\Plugins::Action('HeadContent');


			if( \gp\tool::LoggedIn() ){
				\gp\tool::AddColorBox();
			}

			//always include javascript when there are messages
			if( $page->admin_js || !empty($page->jQueryCode) || !empty($wbMessageBuffer) || isset($_COOKIE['cookie_cmd']) ){
				\gp\tool::LoadComponents('gp-main');
			}
			//defaults
			\gp\tool::LoadComponents('jquery,gp-additional');

			//get css and js info
			$scripts = \gp\tool\Output\Combine::ScriptInfo( self::$components );

			ob_start();
			self::GetHead_TKD();
			self::$head_content = ob_get_clean();

			ob_start();
			self::GetHead_CSS($scripts['css']); //css before js so it's available to scripts
			self::$head_css = ob_get_clean();

			//javascript
			ob_start();
			self::GetHead_Lang();
			self::GetHead_JS($scripts['js']);
			self::GetHead_InlineJS();
			self::$head_js = ob_get_clean();

			//gadget info
			if( isset($config['addons']) ){
				foreach($config['addons'] as $addon_info){
					if( !empty($addon_info['html_head']) ){
						self::MoveScript($addon_info['html_head']);
					}
				}
			}

			if( !empty($page->head) ){
				self::MoveScript($page->head);
			}
		}


		/**
		 * Move <script>..</script> to self::$head_js
		 *
		 */
		public static function MoveScript($string){

			//conditional comments with script tags
			$patt = '#' . preg_quote('<!--[if', '#') . '.*?' . preg_quote('<![endif]-->', '#') . '#s';
			if( preg_match_all($patt,$string, $matches) ){
				foreach($matches[0] as $match){
					if( strpos($match,'<script') !== false ){
						$string = str_replace($match, '', $string);
						self::$head_js .= "\n" . $match;
					}
				}
			}

			//script tags
			if( preg_match_all('#<script.*?</script>#i',$string,$matches) ){
				foreach($matches[0] as $match){
					$string = str_replace($match, '', $string);
					self::$head_js .= "\n" . $match;
				}
			}

			//add the rest to the head_content
			self::$head_content .= "\n" . $string;
		}


		/**
		 * Output the title, keywords, description and other meta for the current html document
		 * @static
		 */
		public static function GetHead_TKD(){
			global $config, $page, $gpLayouts;

			//charset
			if( $page->gpLayout &&
				isset($gpLayouts[$page->gpLayout]) &&
				isset($gpLayouts[$page->gpLayout]['doctype'])
			){
				echo $gpLayouts[$page->gpLayout]['doctype'];
			}

			//title, keyords & description
			$page_title = self::MetaTitle();
			self::MetaKeywords($page_title);
			self::MetaDescription($page_title);

			if( !empty($page->TitleInfo['rel']) ){
				echo "\n" . '<meta name="robots" content="' . $page->TitleInfo['rel'] . '" />';
			}

			echo "\n" . '<meta name="generator" content="' . \CMS_NAME_FULL . '" />';
		}


		/**
		 * Add the <title> tag to the page
		 * return the value
		 *
		 */
		public static function MetaTitle(){
			global $page, $config;

			$meta_title = '';
			$page_title = '';
			if( !empty($page->TitleInfo['browser_title']) ){
				$page_title = $page->TitleInfo['browser_title'];
			}elseif( !empty($page->label) ){
				$page_title = strip_tags($page->label);
			}elseif( isset($page->title) ){
				$page_title = \gp\tool::GetBrowserTitle($page->title);
			}
			$meta_title .= $page_title;
			if( !empty($page_title) && !empty($config['title']) ){
				$meta_title .=  ' - ';
			}
			$meta_title .= $config['title'];

			$meta_title = \gp\tool\Plugins::Filter(
				'MetaTitle',
				[$meta_title, $page_title, $config['title']]
			);

			echo "\n" . '<title>' . strip_tags($meta_title) . '</title>';
			return $page_title;
		}


		/**
		 * Add the <meta name="keywords"> tag to the page
		 *
		 */
		public static function MetaKeywords($page_title){
			global $page, $config;

			if( count($page->meta_keywords) ){
				$keywords = $page->meta_keywords;
			}elseif( !empty($page->TitleInfo['keywords']) ){
				$keywords = explode(',', $page->TitleInfo['keywords']);
			}
			$keywords[]		= strip_tags($page_title);
			$page->label .= "";
			$keywords[]		= strip_tags($page->label);

			$site_keywords	= explode(',', $config['keywords']);
			$keywords		= array_merge($keywords, $site_keywords);
			$keywords		= array_unique($keywords);
			$keywords		= array_filter($keywords);

			echo "\n<meta name=\"keywords\" content=\"" . implode(', ', $keywords) . "\" />";
		}


		/**
		 * Add the <meta name="dscription"> tag to the page
		 *
		 */
		public static function MetaDescription($page_title){
			global $page, $config;

			$description = '';
			if( !empty($page->meta_description) ){
				$description .= $page->meta_description;
			}elseif( !empty($page->TitleInfo['description']) ){
				$description .= $page->TitleInfo['description'];
			}else{
				$description .= $page_title;
			}
			$description = self::EndPhrase($description);

			if( !empty($config['desc']) ){
				$description .= htmlspecialchars($config['desc']);
			}
			$description = trim($description);

			if( !empty($description) ){
				echo "\n" . '<meta name="description" content="' . $description . '" />';
			}
		}


		/**
		 * Prepare and output any inline Javascript for the current page
		 * @static
		 */
		public static function GetHead_InlineJS(){
			global $page, $gp_titles;

			if( isset($page->gp_index) &&
				isset($gp_titles[$page->gp_index]['vis']) &&
				$gp_titles[$page->gp_index]['vis'] == 'private'
			){
				$page->jQueryCode .= '$("html").addClass("isPrivate");' . "\n";
			}

			if( \gp\tool::LoggedIn() && $page->pagetype !== 'admin_display' ){
				$page->jQueryCode .= '$gp.HideAdminUI.init();' . "\n";
				// get available classes
				$avail_classes		= \gp\admin\Settings\Classes::GetClasses();
				$avail_classes		= \gp\tool\Plugins::Filter('AvailableClasses', [$avail_classes]);
				$page->head_script .= "\n" . 'var gp_avail_classes = ' . json_encode($avail_classes) . ';';
			}

			// get customizer js vars
			$layout_js_vars = self::GetLayoutJsVars();
			// debug('$layout_js_vars = <em>' . $layout_js_vars . '</em>');

			ob_start();

			echo $layout_js_vars;

			echo $page->head_script . "\n";

			if( !empty($page->jQueryCode) ){
				echo '$(function(){' . "\n";
				echo $page->jQueryCode . "\n";
				echo '});';
			}

			$inline = ob_get_clean();
			$inline = ltrim($inline);

			echo "\n" . '<script type="text/javascript">' . "\n" . $inline . "\n" . '</script>' . "\n";
		}



		/**
		 * Add language values to the current page
		 * @static
		 */
		public static function GetHead_Lang(){
			global $langmessage;

			if( !count(self::$lang_values) ){
				return;
			}

			echo "\n" . '<script type="text/javascript">';
			echo 'var gplang = {';
			$comma = '';
			foreach(self::$lang_values as $from_key => $to_key){
				echo $comma;
				echo $to_key . ':"'	. str_replace(['\\', '"'], ['\\\\', '\"'], $langmessage[$from_key]) . '"';
				$comma = ',';
			}
			echo "}; </script>";
		}


		/**
		 * Prepare and output the Javascript for the current page
		 * @static
		 */
		public static function GetHead_JS($scripts){
			global $page, $config;

			$combine	= $config['combinejs'] && !\gp\tool::loggedIn() && ($page->pagetype !== 'admin_display');
			$scripts	= self::GetHead_CDN('js', $scripts);

			//just local jquery
			if( !count($page->head_js) && count($scripts) === 1 && isset($scripts['jquery']) ){
				echo '<!-- jquery_placeholder ' . \gp_random . ' -->';
				return;
			}

			if( !$combine || $page->head_force_inline ){
				echo "\n<script type=\"text/javascript\">\n";
				\gp\tool::jsStart();
				echo "\n</script>";
			}

			if( is_array($page->head_js) ){
				$scripts += $page->head_js; //other js files
			}else{
				trigger_error('$page->head_js is not an array');
			}

			Output\Assets::CombineFiles($scripts, 'js', $combine);
		}


		/**
		 * Prepare and output the css for the current page
		 * @static
		 */
		public static function GetHead_CSS($to_add){
			global $page, $config, $dataDir;

			$scripts	= [];
			$to_add		= self::GetHead_CDN('css', $to_add);
			$scripts	= Output\Assets::MergeScripts($scripts, $to_add);


			if( isset($page->css_user) ){
				$scripts	= Output\Assets::MergeScripts($scripts, $page->css_user);
			}

			// add theme css
			if( !empty($page->theme_name) && $page->get_theme_css === true ){
				$scripts	= Output\Assets::MergeScripts($scripts, Output\Assets::LayoutStyleFiles());
			}

			//styles that need to override admin.css should be added to $page->css_admin;
			if( isset($page->css_admin)  ){
				$scripts	= Output\Assets::MergeScripts($scripts, $page->css_admin);
			}

			// disable 'combine css' if 'create_css_sourcemaps' is set to true in /gpconfig.php
			$combinecss = \create_css_sourcemaps ? false : $config['combinecss'];

			Output\Assets::CombineFiles($scripts, 'css', $combinecss);
		}


		/**
		 * Add CDN hosted resources to the page
		 *
		 */
		public static function GetHead_CDN($type, $scripts){
			global $config;

			if( empty($config['cdn']) ){
				return $scripts;
			}

			$cdn		= $config['cdn'];

			foreach($scripts as $key => $script_info){

				if( !isset($script_info['cdn']) || !isset($script_info['cdn'][$cdn]) ){
					continue;
				}

				$cdn_url = $script_info['cdn'][$cdn];

				//remove packages
				if( isset($script_info['package']) ){
					foreach($scripts as $_key => $_info){
						if( isset($_info['package']) && $_info['package'] == $script_info['package'] ){
							unset($scripts[$_key]);
						}
					}
				}
				unset($scripts[$key]);

				echo Output\Assets::FormatAsset($type,$cdn_url);
			}

			return $scripts;
		}


		/**
		 * Get the path for the custom css/scss/less file
		 *
		 */
		public static function CustomStyleFile($layout, $style_type){
			global $dataDir;

			if( $style_type == 'scss' ){
				return $dataDir . '/data/_layouts/' . $layout . '/custom.scss';
			}

			return $dataDir . '/data/_layouts/' . $layout . '/custom.css';
		}


		/**
		 * Get the path for the customizer css/scss/less file
		 *
		 * @since 5.2
		 */
		public static function CustomizerStyleFile($layout, $style_type){
			global $dataDir;

			if( $style_type == 'scss' ){
				return $dataDir . '/data/_layouts/' . $layout . '/customizer.scss';
			}

			return $dataDir . '/data/_layouts/' . $layout . '/customizer.css';
		}


		/**
		 * Get the path for the custom layout config file
		 *
		 * @since 5.2
		 */
		public static function LayoutConfigFile($layout){
			global $dataDir;

			return $dataDir . '/data/_layouts/' . $layout . '/config.php';
		}


		/**
		 * Get the filetype of the style.* file
		 *
		 * @return string
		 */
		public static function StyleType($dir){

			$types = ['less','scss'];

			foreach($types as $type){
				$path = $dir . '/style.'.$type;
				if( file_exists($path) ){
					return $type;
				}
			}
			return 'css';
		}


		/**
		 * Determines whether the passed directory qualifies as layout
		 * by checking whether a style.css, style.less or style.css file exists
		 * @return boolean
		 */
		public static function IsLayoutDir($dir){

			$types = ['less','scss','css'];

			foreach($types as $type){
				$path = $dir . '/style.' . $type;
				if( file_exists($path) ){
					return true;
				}
			}
			return false;
		}


		/**
		 * Complete the response by adding final content to the <head> of the document
		 * @static
		 * @since 2.4.1
		 * @param string $buffer html content
		 * @return string finalized response
		 */
		public static function BufferOut($buffer){
			global $config;

			//add error notice if there was a fatal error
			if( !ini_get('display_errors') ){
				$last_error	= self::LastFatal();
				if( !empty($last_error) ){
					self::RecordFatal($last_error);
					$buffer .= self::FatalMessage($last_error);
				}
			}

			//remove lock
			if( defined('gp_has_lock') && \gp_has_lock ){
				\gp\tool\Files::Unlock('write', \gp_random);
			}

			//make sure whe have a complete html request
			$placeholder = '<!-- get_head_placeholder ' . \gp_random . ' -->';
			if( strpos($buffer,$placeholder) === false ){
				return $buffer;
			}

			$replacements		= [];

			//performace stats
			if( class_exists('admin_tools') ){
				$replacements	= self::PerformanceStats();
			}

			//head content
			//add css to bottom of <body>
			if( \load_css_in_body ){
				$buffer = self::AddToBody($buffer, self::$head_css);
				$replacements[$placeholder]	= self::$head_content;
			}else{
				$replacements[$placeholder]	= self::$head_css . self::$head_content;
			}

			//add js to bottom of <body>
			$buffer = self::AddToBody($buffer, self::$head_js);

			//add jquery if needed
			$placeholder = '<!-- jquery_placeholder ' . \gp_random . ' -->';
			$replacement = '';
			if( !empty(self::$head_js) || stripos($buffer, '<script') !== false ){
				$replacement = Output\Assets::FormatAsset('js',\gp\tool::GetDir('/include/thirdparty/js/jquery.js')); // TODO: restore this line
			}

			$replacements[$placeholder]	= $replacement;

			//messages
			$pos = strpos($buffer, '<!-- message_start ' . \gp_random . ' -->');
			$len = strpos($buffer, '<!-- message_end -->') - $pos;
			if( $pos && $len ){
				$replacement = GetMessages(false);
				$buffer = substr_replace($buffer, $replacement, $pos, $len + 20);
			}

			return str_replace( array_keys($replacements), array_values($replacements), $buffer);
		}


		/**
		 * Add content to the html document before the </body> tag
		 *
		 */
		public static function AddToBody($buffer, $add_string){

			if( empty($add_string) ){
				return $buffer;
			}

			$pos_body = stripos($buffer, '</body');
			if( $pos_body !== false ){
				return substr_replace($buffer, "\n" . $add_string . "\n", $pos_body, 0);
			}

			return $buffer;
		}


		/**
		 * Return the message displayed when a fatal error has been caught
		 *
		 */
		public static function FatalMessage($error_details){

			$message = '<p>Oops, an error occurred while generating this page.<p>';

			if( !\gp\tool::LoggedIn() ){

				//reload non-logged in users automatically if there were catchable errors
				if( !empty(self::$catchable) ){
					$message .= 'Reloading... <script type="text/javascript">'
						. 'window.setTimeout(function(){window.location.href = '
						. 'window.location.href},1000);</script>';
				}else{
					$message .= '<p>If you are the site administrator, you can troubleshoot '
						. 'the problem by changing php\'s display_errors setting to 1 in '
						. 'the gpconfig.php file.</p><p>If the problem is being caused by an addon, '
						. 'you may also be able to bypass the error by enabling ' . \CMS_NAME . '\'s '
						. 'safe mode in the gpconfig.php file.</p><p>More information is available '
						. 'in the <a href="' . \CMS_DOMAIN . '/Docs/Main/Troubleshooting">Documentation</a>.'
						. '</p><p><a href="?">Reload this page to continue</a>.</p>';
				}

				return $message;
			}

			$message .= '<h3>Error Details</h3>'
					.pre($error_details)
					. '<p><a href="?">Reload this page</a></p>'
					. '<p style="font-size:90%">Note: Error details are only '
					. 'displayed for logged in administrators</p>'
					. \gp\tool::ErrorBuffer(true, false);

			return $message;
		}



		/**
		 * Determine if a fatal error has been fired
		 * @return array
		 */
		public static function LastFatal(){
			$fatal_errors	= [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
			$last_error		= error_get_last();
			if( is_array($last_error) && in_array($last_error['type'], $fatal_errors) ){
				return $last_error;
			}
		}



		/**
		 * Record fatal errors in /data/_site/ so we can prevent subsequent requests from having the same issue
		 *
		 */
		public static function RecordFatal($last_error){
			global $config, $addon_current_id, $addonFolderName;

			$last_error['request'] = $_SERVER['REQUEST_URI'];
			if( $addon_current_id ){
				$last_error['addon_name'] = $config['addons'][$addonFolderName]['name'];
				$last_error['addon_id'] = $addon_current_id;
			}

			$last_error['file'] = realpath($last_error['file']);//may be redundant
			showError(
				$last_error['type'],
				$last_error['message'],
				$last_error['file'],
				$last_error['line'],
				false
			); //send error to logger

			if( empty(self::$catchable) ){
				return;
			}

			$last_error['time'] = time();
			$last_error['request_method'] = $_SERVER['REQUEST_METHOD'];
			if( !empty($last_error['file']) ){
				$last_error['file_modified'] = filemtime($last_error['file']);
				$last_error['file_size'] = filesize($last_error['file']);
			}

			$content	= json_encode($last_error);
			$temp		= array_reverse(self::$catchable);

			foreach($temp as $filepath => $info){

				\gp\tool\Files::Save($filepath,$content);

				if( $info['catchable_type'] == 'exec' ){
					break;
				}
			}
		}


		/**
		 * Return Performance Stats about the current request
		 *
		 * @return array
		 */
		public static function PerformanceStats(){

			$stats = [];

			if( function_exists('memory_get_peak_usage') ){
				$stats['<span cms-memory-usage>?</span>']	= \gp\admin\Tools::FormatBytes(memory_get_usage());
				$stats['<span cms-memory-max>?</span>']		= \gp\admin\Tools::FormatBytes(memory_get_peak_usage());
			}

			if( isset($_SERVER['REQUEST_TIME_FLOAT']) ){
				$time	= microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
			}else{
				$time	= microtime(true) - gp_start_time;
			}

			$stats['<span cms-seconds>?</span>']	= round($time, 3);
			$stats['<span cms-ms>?</span>']			= round($time * 1000);

			return $stats;
		}


		/**
		 * Return true if the user agent is a search engine bot
		 * Detection is rudimentary and shouldn't be relied on
		 * @return bool
		 */
		public static function DetectBot(){
			$user_agent =& $_SERVER['HTTP_USER_AGENT'];
			return (bool)preg_match('#bot|yahoo\! slurp|ask jeeves|ia_archiver|spider|crawler#i', $user_agent);
		}

		/**
		 * Return true if the current page is the home page
		 */
		public static function is_front_page(){
			global $config, $page;
			return $page->gp_index == $config['homepath_key'];
		}


		/**
		 * Outputs the sitemap link, admin login/logout link, powered by link and messages
		 * @static
		 */
		public static function GetAdminLink($messages=true){
			self::GetSitemapLink(); // as of 5.2-rc
			echo ' ';
			self::GetLoginLink(); // as of 5.2-rc
			echo ' ';
			self::GetPoweredByLink(); // as of 5.2-rc

			\gp\tool\Plugins::Action('GetAdminLink');

			if( $messages ){
				echo GetMessages();
			}
		}


		/**
		 * Outputs only the sitemap link
		 * as of 5.2-rc
		 * @static
		 */
		public static function GetSitemapLink(){
			global $config, $langmessage, $page;

			if( !isset($config['showsitemap']) || $config['showsitemap'] ){
				echo ' <span class="sitemap_link">';
				echo \gp\tool::Link(
					'Special_Site_Map',
					$langmessage['site_map']
				);
				\gp\tool\Plugins::Action('GetSitemapLink');
				echo '</span>';
			}
		}


		/**
		 * Outputs only the login/logout link
		 * as of 5.2-rc
		 * @static
		 */
		public static function GetLoginLink($force_show=false){
			global $config, $langmessage, $page;

			if( $force_show || !isset($config['showlogin']) || $config['showlogin'] ){
				echo '<span class="login_link">';
					if( \gp\tool::LoggedIn() ){
						echo \gp\tool::Link(
							$page->title,
							$langmessage['logout'],
							'cmd=logout',
							['data-cmd' => 'cnreq', 'rel' => 'nofollow']
						);
					}else{
						echo \gp\tool::Link(
							'Admin',
							$langmessage['login'],
							'file=' . rawurlencode($page->title),
							['data-cmd' => 'login', 'rel' => 'nofollow']
						);
					}
				\gp\tool\Plugins::Action('GetLoginLink');
				echo '</span>';
			}
		}


		public static function AdminLinkGadget(){
			self::GetAdminLink(false);
		}


		public static function LoginLinkGadget(){
			self::GetLoginLink(true);
		}


		/**
		 * Outputs only the powered_by link
		 * as of 5.2-rc
		 * @static
		 */
		public static function GetPoweredByLink($always_show=false){
			global $config;

			if( !isset($config['showgplink']) || $config['showgplink'] ){
				if( self::is_front_page() || $always_show ){
					echo '<span id="powered_by_link">';
					echo 'Powered by <a href="' . \CMS_DOMAIN . '" target="_blank">' . \CMS_NAME . '</a>';
					\gp\tool\Plugins::Action('GetPoweredByLink');
					echo '</span>';
				}
			}
		}


		/**
		 * Add punctuation to the end of a string if it isn't already punctuated.
		 * Looks for !?.,;: characters
		 *
		 * @static
		 * @since 2.4RC1
		 */
		public static function EndPhrase($string){
			$string = trim($string);
			if( empty($string) ){
				return $string;
			}
			$len = strspn($string, '!?.,;:', -1);
			if( $len == 0 ){
				$string .= '.';
			}
			return $string . ' ';
		}


		public static function RunOut(){
			global $langmessage, $page;

			$page->RunScript();

			//prepare the admin content
			if( \gp\tool::LoggedIn() ){
				\gp\admin\Tools::AdminHtml();
			}

			//decide how to send the content
			self::Prep();
			switch(\gp\tool::RequestType()){

				// <a data-cmd="admin_box">
				case 'flush':
					self::Flush();
					break;

				// remote request
				// file browser
				case 'body':
					\gp\tool::CheckTheme();
					self::BodyAsHTML();
					break;

				case 'admin':
					self::AdminHtml();
					break;

				// <a data-cmd="gpajax">
				// <a data-cmd="gpabox">
				// <input data-cmd="gpabox">
				case 'json':
					\gp\tool::CheckTheme();
					\gp\tool\Output\Ajax::Response();
					break;

				case 'content':
					self::Content();
					break;

				default:
					\gp\tool::CheckTheme();
					self::Template();
					break;
			}

			// if logged in, don't send 304 response
			if( \gp\tool::LoggedIn() ){
				//empty edit links if there isn't a layout
				if( !$page->gpLayout ){
					self::$editlinks = '';
				}
				return;
			}

			// attempt to send 304 response
			if( $page->fileModTime > 0 ){
				global $wbMessageBuffer;
				$len	= ob_get_length();
				$etag	= \gp\tool::GenEtag(
					$page->fileModTime,
					$len,
					json_encode($wbMessageBuffer),
					self::$head_content,
					self::$head_js
				);
				\gp\tool::Send304($etag);
			}
		}


		/**
		 * Add one or more components to the page. Output the <script> and/or <style> immediately
		 * @param string $names comma separated list of components
		 *
		 */
		public static function GetComponents($names=''){
			$scripts = \gp\tool\Output\Combine::ScriptInfo($names);

			$scripts['css'] = self::GetHead_CDN('css', $scripts['css']);
			Output\Assets::CombineFiles($scripts['css'], 'css', false);

			$scripts['js'] = self::GetHead_CDN('js', $scripts['js']);
			Output\Assets::CombineFiles($scripts['js'], 'js', false);
		}

	}
}

namespace{
	class gpOutput extends gp\tool\Output{}
}
