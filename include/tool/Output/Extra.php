<?php

namespace gp\tool\Output;

class Extra{

	/**
	 * Return extra content area
	 * @param string $name
	 * @return string
	 *
	 */
	public static function GetExtra($name='Side_Menu'){
		global $dataDir,$langmessage;

		$attrs			= [];
		$name			= str_replace(' ', '_', $name);
		$is_draft		= false;
		$extra_content	= self::ExtraContent( $name, $is_draft );
		$file_stats		= \gp\tool\Files::$last_stats;
		$wrap			= \gp\tool\Output::ShowEditLink('Admin_Extra');
		$section_num	= 0;

		if( !self::ExtraIsVisible($name) ){
			return '';
		}

		if( !$wrap ){
			return \gp\tool\Output\Sections::GetSection($extra_content, $section_num);
		}

		$edit_link = \gp\tool\Output::EditAreaLink(
			$edit_index,
			'Admin/Extra/' . rawurlencode($name),
			$langmessage['edit'],
			'cmd=edit',
			array(
				'title'		=>	str_replace('_', ' ', $name),
				'data-cmd'	=> 'inline_edit_generic'
			)
		);


		ob_start();
		echo '<span class="nodisplay" id="ExtraEditLnks' . $edit_index . '">';
		echo $edit_link;
		echo \gp\Page\Edit::IncludeLink($extra_content[0]);
		echo \gp\tool::Link(
			'Admin/Extra',
			$langmessage['theme_content'],
			'',
			array('class' => 'nodisplay')
		);
		echo '</span>';
		\gp\tool\Output::$editlinks .= ob_get_clean();

		$attrs['data-gp_label']		= str_replace('_', ' ', $name);
		$attrs['class']				= ' editable_area';
		$attrs['id']				= 'ExtraEditArea' . $edit_index;
		$attrs['data-draft']		= (int)$is_draft;

		$extra_content[0]					+= ['attributes'=>[]];
		$extra_content[0]['attributes']		+= $attrs;

		// image type comes with an empty string for $attrs['class'] so it will not get replaced using +=
		$extra_content[0]['attributes']['class'] .= $attrs['class'];

		return \gp\tool\Output\Sections::GetSection($extra_content, $section_num);
	}



	/**
	 * Get and return the extra content specified by $title
	 *
	 */
	public static function ExtraContent($title, &$is_draft=false){

		//draft?
		$draft_file = '_extra/'.$title.'/draft';
		if( \gp\tool::LoggedIn() && \gp\tool\Files::Exists($draft_file) ){
			$is_draft = true;
			return \gp\tool\Files::Get($draft_file,'file_sections');
		}

		//new location
		$file = '_extra/'.$title.'/page';
		if( \gp\tool\Files::Exists($file) ){
			return \gp\tool\Files::Get($file,'file_sections');
		}

		$file					= '_extra/'.$title;
		$extra_section			= [];
		$extra_section_string	= '';

		if( \gp\tool::LoggedIn() ){
			$extra_section_string = '{{Missing extra content "'.$title.'"}}';
		}

		if( \gp\tool\Files::Exists($file) ){
			ob_start();
			$extra_section			= \gp\tool\Files::Get($file,'extra_content');
			$extra_section_string	= ob_get_clean();
		}

		if( empty($extra_section) ){
			$extra_section['content'] = $extra_section_string;
		}

		$extra_section 	+= array('type'=>'text','content'=>'');
		return array($extra_section);
	}



	public static function ExtraIsVisible($title){
		global $page;


		if( is_object($page) && $page->pagetype == 'admin_display' ){
			return true;
		}

		$vis = \gp\tool\Files::Get('_extra/' . $title . '/visibility', 'data');
		if( !$vis ){
			return true;
		}

		$vis += ['visibility_type'=>0,'pages'=>[]];

		// not visible on any pages
		if( $vis['visibility_type'] == 1 ){
			return false;
		}

		// visible on pages in list
		if( $vis['visibility_type'] == 2 ){
			return array_key_exists($page->gp_index,$vis['pages']);
		}

		// hidden on pages in list
		if( $vis['visibility_type'] == 3 ){
			return !array_key_exists($page->gp_index,$vis['pages']);
		}

		return true;
	}

}
