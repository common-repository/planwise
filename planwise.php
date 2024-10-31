<?php
/*
Plugin Name: Planwise 
Plugin URI: http://planwise.com
Version: 2.0
Author: Planwise
Author URI: http://planwise.com
Description: A simple plugin to manage your money.
Copyright 2012 Planwise  Andy Killen  (email : andy  [a t ] phat hyphen reaction DOT com)

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

    please note that under the GNU GPL license only the code is usable,
    the images are not part of the code and therefore under seperate
    copyrights and licensing.

*/

if (!class_exists("planwise")) {
class planwise {

    public $options;

    protected $options_name;

    public $iframe_url;

    protected  $callhome_url;

    function planwise(){
        $this->options_name = 'planwise';
        $this->options = get_option($this->options_name);
        $this->iframe_url = "https://get.planwise.com";
        $this->callhome_url = "https://planwise.com/registerpartner.htm";

        if(empty($this->options) || $this->options === false ){
            $this->options['email'] = '';
            $this->options['background_color'] = '#FFFFFF';
            $this->options['transparent'] = 'yes';
            $this->options['registered'] = 'no';
            update_option($this->options_name, $this->options);
        }
    }

    /*
     *  THE ADMIN PAGE
     *
     *  called by the external add_options_page
     * 
     *
     */

    function printAdminPage(){
     if (isset($_POST['update_callhome']) && isset($_POST['callinghome']) ) {
        if(wp_verify_nonce($_POST['callinghome'],'planwise_callhome')){

            $this->options['email'] = $_POST['email'];
            $this->options['url'] = $_POST['url'];
            update_option($this->options_name, $this->options);
            

            if(!empty($this->options['email']) && !empty($this->options['url']) ){
                echo "<div id='registering'><img src='".plugins_url('/planwise/images/registering.gif')."' height='108' width='98' /><h4>registering</h4></div>";
                        @ob_flush();
                        @flush();                        
                    
                
                $did_not_work = false;
                if(!class_exists("Pos_RemoteConnector")){
                    require_once('RemoteConnector.php');
                }
                $url = $this->callhome_url."?domain=".urlencode($this->options['url'])."&email=".$this->options['email'];
                
                $get_registration = new Pos_RemoteConnector($url);
                if(function_exists('json_decode')){
                    $result = json_decode($get_registration->__toString());
                    
                    if($result->response=="success"){
                        $this->options['registered'] = 'yes';
                        update_option($this->options_name, $this->options);
                    }else{
                        $did_not_work = true;
                    }

                }else{
                    $result = strpos($get_registration->__toString(),"success");
                    if($result !== false){
                        $this->options['registered'] = 'yes';
                        update_option($this->options_name, $this->options);
                    }else{
                        $did_not_work = true;
                    }
                }
            }
            ?>
            <style type="text/css">#registering{display:none}</style>
            <?php
            if($did_not_work == false){
             ?><div class="updated"><p><strong><?php _e("Registration Settings Updated.", "planwise");?></strong></p></div><?php
            }else{
                ?><div class="updated error"><p><strong><?php _e("Registration Settings Not Updated.  Please contact Planwise at <a href='http://planwise.com/contact/'>http://planwise.com/contact/</a>", "planwise");?></strong></p></div><?php
            }
        }
     }

    if (isset($_POST['update_colors']) && isset($_POST['colors']) ) {
        if(wp_verify_nonce($_POST['colors'],'planwise_pretty')){

            $this->options['background_color'] = $_POST['background_color'];
            $this->options['transparent'] = $_POST['transparent'];
            update_option($this->options_name, $this->options);

             ?><div class="updated"><p><strong><?php _e("Color Settings Updated.", "planwise");?></strong></p></div><?php
        }
     }


      if (isset($_POST['update_copy']) && isset($_POST['template']) ) {
        if(wp_verify_nonce($_POST['template'],'planwise_copy')){

            $directory  = $_POST['copydir'];
            
            $result = copy (plugin_dir_path( __FILE__ ).'/page-planwise.php', $directory.'/page-planwise.php');
            
            if($result){
             ?><div class="updated"><p><strong><?php _e("Copied file.", "planwise");?></strong></p></div><?php
            }
            else {
             ?><div class="error"><p><strong><?php _e("Problem Copying file, you had better do it by hand.", "planwise");?></strong></p></div><?php
            }
        }
     }


        ?>

<div class="wrap" >
    <img src='<?php echo plugins_url('/planwise/images/logo.png') ?>' height='253' width='695' /><br />
    <div class="metabox-holder" style="max-width: 800px">
    <div class="postbox">
    <h3 class="hndle"><span>Register for support from Planwise</span></h3>
    <div class="inside">
    <p>Please take the time to register this plugin with us.  By filling out the form it will send two pieces of information to us.  It will send us your URL which we get from the wordpress settings, and the email address that you fill out below.</p>

    <form action="<?php $_SERVER['REQUEST_URI'] ?>" name="send_home" method="post">
        <?php wp_nonce_field('planwise_callhome','callinghome'); ?>
        <input type="hidden" name="url" value="<?php bloginfo('url'); ?>" />
        <p><label for="email">Your email address</label><br/>
            <input type="email" value="<?php echo $this->options['email'] ?>" name="email" id="email" /><br /><br />

            <input type="submit" name="update_callhome" id="update_callhome" value="register now" class="button-primary" />

    </form>
    </div>
    </div>
    <div class="postbox">
    <h3 class="hndle"><span>Setup Color Settings</span></h3>
    <div class="inside">
    <p>By default we have made everything transparent so it will show on top of your sites normal color.  However at times this may not work so well, for example if you have chosen the same color background as we have for text, the text will seem to disappear.</p>

    <form action="<?php $_SERVER['REQUEST_URI'] ?>" name="send_home" method="post">
        <?php wp_nonce_field('planwise_pretty','colors'); ?>
        <p><label for="background_color">Wanted background color</label><br/>
           <input type="text" value="<?php echo $this->options['background_color'] ?>" name="background_color" id="background_color" /><div id="colorpicker"></div><br /><br />
            <label for="transparent">Set as transparent: </label>

        <input type="hidden" name="transparent" value="no" />
        <input type="checkbox" name="transparent" value="yes" <?php echo ($this->options['transparent'] == 'yes') ? "checked" : "" ; ?> /><br /><br />
       

        <input type="submit" name="update_colors" id="update_callhome" value="setup colors" class="button-primary" />

    </form>
    </div>
    </div>



    <div class="postbox">
    <h3 class="hndle"><span>Copy Page Template</span></h3>
    <div class="inside">
    <p>If you don't have a page template that has no sidebar and the iframe does not fit properly, you can use our page template.  It should work instantly with your theme.  You can either copy it by hand from the planwise plugins directory or you can prese the button below to let it be done automatically</p>
    <p>If there is a problem that stops it from copying and you have to do it by hand, the file is called <code>page-planwise.php</code>. </p>

    <form action="<?php $_SERVER['REQUEST_URI'] ?>" name="copy_info" method="post">
        <?php wp_nonce_field('planwise_copy','template'); ?>
        <input type="hidden" value="<?php echo TEMPLATEPATH ?>" name="copydir" />

        <input type="submit" name="update_copy" id="update_copy" value="Copy planwise template " class="button-primary" />

    </form>
    </div>
    </div>
</div>
</div>

        <?php

    }

    /*
     * The Documentation Page
     *
     *
     *
     */
    public function  printDocumentationPage(){
        echo "<div class='wrap'>";
        echo "<img src='" .plugins_url('/planwise/images/logo.png') .  "' height='253' width='695' /><br />";
        include('documentation.html');
        echo "</div>";
        
    }



    /*
     *  Shortcode funtion to show the iframe on a page
     *
     *
     */

    function planwise_shortcode($atts, $content){
             extract(shortcode_atts(array(
                        'url'=>$this->iframe_url,
                        'height'=>'700',
                        'width'=>'100%',
                        'marginheight'=>'0',
                        'marginwidth'=>'0',
                        'scrolling'=>'no',
                        'frameborder'=>'0'
                        ), $atts));

             if($this->options['transparent']=='yes'){
                $color = '';
             }else{
                $color = 'background-color:'.$this->options['background_color'];
             }
             $html = "<div style='{$color};width:100%;overflow:visible' ><iframe height='{$height}' width='{$width}' src='{$url}' marginwidth='{$marginwidth}' marginheight='{$marginheight}' scrolling='{$scrolling}' frameborder='{$frameborder}' ></iframe></div> ";

             return $html; // shortcodes should be a return, not a print or echo as it only puts it at the top of the post
    }


    /*
     * set up admin scripts and styles
     *
     */
    function admin_init_planwise(){
        if($_GET['page']=='planwise.php'){
            wp_register_script('colorpicker_admin', plugins_url('/planwise/farbtastic/farbtastic.js'), array('jquery'),1,false);
            wp_enqueue_script( 'colorpicker_admin' );

             wp_register_script('planwise_admin', plugins_url('/planwise/js/planwise-admin.js'), array('jquery','colorpicker_admin'),1,false);
             wp_enqueue_script( 'planwise_admin' );

            wp_register_style( 'colorpicker_admin_css', plugins_url('/planwise/farbtastic/farbtastic.css'), false, '1' );
            
            wp_enqueue_style( 'colorpicker_admin_css' );
            
        }
    }

    }
}

//  setup new instance of plugin
if (class_exists("planwise")) {$settings_planwise = new planwise();}

//Actions and Filters
if (isset($settings_planwise)) {
    //Initialize the admin panel
        if (!function_exists("planwise_ap")) {
                function planwise_ap() {
                        global $settings_planwise;
                        if (!isset($settings_planwise)) {
                                return;
                        }
                        if (function_exists('add_options_page') || function_exists('add_menu_page')) {
                        //    add_options_page('Planwise', 'Planwise', 'manage_options', , );
                        add_menu_page('Planwise', 'Planwise', 'manage_options', basename(__FILE__), array(&$settings_planwise, 'printAdminPage'), plugins_url('planwise/images/icon.png'));
                            add_submenu_page( 'planwise.php','Planwise Documentation', 'Documentation', 'manage_options', 'planwise_documentation', array(&$settings_planwise, 'printDocumentationPage') );
                        }
                }
        }
}

  add_action('admin_menu', 'planwise_ap',1); //admin page
  add_action ('admin_enqueue_scripts',array(&$settings_planwise, 'admin_init_planwise'));  // add admin page scripts
  add_shortcode('planwise', array(&$settings_planwise,'planwise_shortcode'),1); // setup shortcode [planwise]

?>
