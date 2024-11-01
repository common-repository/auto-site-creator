<?php
/*
Plugin Name: Auto Site Creator
Plugin URI: https://profiles.google.com/u/0/klinger.ofir/about
Description: Auto create site in a Multi-Site installation for every new user regisration (sub-folder installations only!)
Version: 0.1a 
Author: Ofir Klinger
Author URI: https://sites.google.com/site/autositecreator/
*/
/*  Copyright 2009  Ofir Klinger  (email : klinger.ofir@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! is_multisite() )
    wp_die( __( 'Multisite support is not enabled.' ) );

class auto_site_creator
{
    /**
     * Open a new site in multi-site installation for the new user (with $user_id)
     *
     * The steps are:
     * 1. Get the user name, and its email address
     * 2. Open a new site using the above
     *
     * If there is no email address for this user, abort.
     * 
     *
     *
     * @package AutoSiteCreator
     * @since   0.1a
     * @param integer $user_id The ID of the new user
     * @return void
     */
    public function create_site_for_new_user($user_id)
    {
	global $base, $current_site;
	
	// @see http://codex.wordpress.org/Function_Reference/get_userdata
	$user_info = get_userdata($user_id);
	$email = $user_info->user_email;
	$username = $user_info->user_nicename;
	
	// @see wordpress-root/wp-admin/network/site-new.php
	if (!preg_match( '/(--)/', $username) && preg_match('|^([a-zA-Z0-9-])+$|', $username)) {
	    $domain = strtolower($username);
	} else {
	    return;
	}
	
	// Make sure the domain isn't a reserved word
	$subdirectory_reserved_names = apply_filters('subdirectory_reserved_names', array('page', 'comments', 'blog', 'files', 'feed'));
	if (in_array($domain, $subdirectory_reserved_names))
	    return;
	if (empty($email))
	    return;
	if (!is_email($email))
	    return;
	
	$newdomain = $current_site->domain;
	$path = $base . $domain . '/';
	
	$id = wpmu_create_blog($newdomain, $path, $username . "'s Personal Site", $user_id , array('public' => 1), $current_site->id);
	if (is_wp_error($id)) {
	    // wp_die($id->get_error_message());
	    return;
	}
    }
}

// Initiate class
$auto_site_creator = new auto_site_creator();

// Register widgets
add_action('user_register', array($auto_site_creator, 'create_site_for_new_user'));

?>