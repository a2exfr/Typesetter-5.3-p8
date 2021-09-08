<?php
/**
 * Notifications Example plugin
 * demonstrates the use of the new plugin filter hook 'Notifications'.
 *
 *
 */

defined('is_running') or die('Not an entry point...');

class Notifications_Example{

	static function Notifications($notifications){
		global $langmessage;


		$items = array(
			array(
				'label'		=> 'Check back home!',
				'priority'	=> 80,				// optional: an integer > 100 will overtake working drafts notifications
				'id'		=> 'Example Note Check back home!',			// needs to be unique amongst other notes for filtering

				'action'	=> '<a target="_blank" href="' . \CMS_DOMAIN . '">' . \CMS_READABLE_DOMAIN . '</a>',
			),

			array(
				'label'		=> 'GitHub',
				'priority'	=> 30,
				'id'		=> 'Example Note GitHub',
				'action'	=> '<a target="_blank" href="https://github.com/Typesetter/Typesetter/issues">View issues</a>',
			),

			array(
				'label'		=> 'This Plugin',
				'priority'	=> 110,
				'id'		=> 'Uninstall this plugin',
				'action'	=> \gp\tool::Link(
					'Admin/Addons',
					$langmessage['uninstall'],
					'cmd=uninstall&addon=Notifications-Example',
					array(
						'data-cmd'	=> 'gpabox',
						'title'		=> $langmessage['uninstall']
					)
				),
			),
		);

		$notifications->Add('Plugin Example',$items,'#c594e8','#000');

		/*
		$notifications['example'] = array(	// unique identifier string. using 'drafts', 'private_pages' or 'updates' would overwrite possible existing notes

			'title'			=> 'Plugin Example',	// required: will automatically be checked against $langmessage
			'badge_bg'		=> '#c594e8',			// optional: badge background color
			'badge_color'	=> '#000',				// optional: badge text color


			),
		);
		*/

		return $notifications;
	}

}
