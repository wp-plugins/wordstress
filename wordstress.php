<?php
/**
 * Plugin Name: wordstress
 * Plugin URI: https://wordpress.org/plugins/wordstress
 * Description: wordstress is a whitebox security scanner for wordpress powered websites. This plugin introduces a wordstress virtual post with all installed plugins and themes and their version number. This information will be used by the <a href="https://rubygems.org/gems/wordstress">wordstress security scanner</a> to give you a false positives free result. <strong>For security reasons</strong> the page needs a key passed in the query string in order to render the plugin and themes list. A blank page is shown if no key is provided or if it mismatches the one saved in wordstress plugin preference pane. To get started: 1) Click the "Activate" link to the left of this description, 2) Go into Settings->Wordstress admin page, 3) A new key is automagically generated, to increase entropy you may want to reload the page a couple of times, 4) When you're comfortable with the generated key, press the "Save Changes" button. The virtual page is now available calling http://youblogurl/wordstress?worstress-key=the_key, 5) Install worstress rubygem on your scanning machine (you need a working ruby environment to do this): gem install wordstress, 6) From a command line, use wordstress security scanner this way: worstress -u http://yourblogurl/wordstress -k the_key, 7) Enjoy results
 * Version: 0.6.0
 * Author: Paolo Perego - paolo@codiceinsicuro.it
 * Author URI: https://codiceinsicuro.it
 * License: GPL2
 */

/*  Copyright 2015  Paolo Perego  (email : paolo@codiceinsicuro.it)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined('ABSPATH') or die("Hi there! I\'m just a plugin, not much I can do when called directly.");


add_action('init', 'wordstress_init_external');
add_action('admin_menu', 'wordstress_admin_menu');

if (!class_exists('WordstressPage')) {
  class WordstressPage
  {
    private $slug = "wordstress";
    private $title = "Wordstress security scanner feed";
    private $content = "";
    private $author = 1;
    private $date=NULL;
    private $dategmt=NULL;
    private $type = 'page';

    public function __construct($args)
    {
      $this->content=isset($args['content'])?$args['content']:'';
      $this->date=current_time('mysql');
      $this->dategmt=current_time('mysql',1);

      add_filter('the_posts', array(&$this, 'virtualPage'));
    }
    public function virtualPage($posts)
    {
      global $wp, $wp_query;
      if (count($posts) == 0 && (strcasecmp($wp->request, $this->slug) == 0 || $wp->query_vars['page_id'] == $this->slug))
      {
        $post = new stdClass;
        $post->ID = -1;
        $post->post_author=$this->author;
        $post->post_date= $this->date;
        $post->post_date_gmt= $this->dategmt;
        $post->post_content= $this->content;
        $post->post_title= $this->title;
        $post->post_excerpt='';
        $post->post_status='published';
        $post->comment_status='closed';
        $post->ping_status='closed';
        $post->post_password='';
        $post->post_name=$this->slug;
        $post->to_ping='';
        $post->pinged='';
        $post->modified=$post->post_date;
        $post->modified_gmt=$post->post_date_gmt;
        $post->post_content_filtered='';
        $post->post_parent=0;
        $post->guid=get_home_url('/'.$this->slug);
        $post->menu_order=0;
        $post->post_type=$this->type;
        $post->post_mime_type='';
        $post->comment_count=0;

        $posts = array($post);
        $wp_query->is_page = TRUE;
        $wp_query->is_singular = TRUE;
        $wp_query->is_home = FALSE;
        $wp_query->is_archive = FALSE;
        $wp_query->is_category = FALSE;
        unset($wp_query->query['error']);
        $wp_query->query_vars['error'] = '';
        $wp_query->is_404 = FALSE;
      }
      return ($posts);
    }
  }
}

function endsWith($haystack, $needle) {
  return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
}
function wordstress_init_external(){
  if ( ! function_exists( 'get_plugins' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
  }

  $url = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
  $ret ="";
  if (endsWith($url,'wordstress'))
  {
    if (isset($_GET['wordstress-key'])) {
      $key = esc_html($_GET['wordstress-key']);
    } else {
      $key = "none";
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    $authorized_key = get_option('wordstress-api-key');
    if ($key != $authorized_key) {
      $ret="";
    } else {
      $ret .= "<h3>Version</h3><p id='wp_version'>".get_bloginfo('version', 'raw')."</p>";
      $ret .= "<h3>All plugins</h3><ul id='all_plugins'>";
      $plugins = get_plugins();
      foreach ($plugins as $key => $plugin) {
        if (is_plugin_active($key)) {
          $act = "active";
        }else{
          $act = "inactive";
        }
        $ret .= "<li id='all_plugin'>".$plugin["Name"].",".$plugin["Version"].",".$key.",".$act."</li>";
      }
      $ret .= "</ul>";

      $ret .= "<h3>All themes</h3><ul id='all_themes'>";
      $themes = wp_get_themes();
      $theme_name = wp_get_theme();
      foreach ($themes as $key => $theme) {
        $active = "inactive";
        if ($theme['Name'] == $theme_name) {
          $active = "active";
        }
        $ret .= "<li id='all_theme'>".$theme["Name"].",".$theme["Version"].",".$key.",".$active."</li>";
      }
      $ret .= "</ul>";
    }

    $args=array('content'=>$ret);
    $page=new WordstressPage($args);
  }
}

function create_key() {
  $now  = time();
  $desc = get_option('blogdescription');
  $name = get_option('blogname');
  $url  = get_option('siteurl');
  $rand = rand();
  $now2 = time();
  $secret = rand();

  return hash_hmac('ripemd160', $desc.$now.$rand.$name.$now.$rand.$url, hash_hmac('ripemd160', $now.$now2.$secret.$url, $secret.$now2));
}

function wordstress_admin_menu() {
  add_options_page("Wordstress", "Wordstress", 'manage_options', 'wordstress_admin', 'wordstress_options');
  add_action( 'admin_init', 'register_wordstress_settings' );

}
function register_wordstress_settings() {
  register_setting('wordstress-settings-group', 'wordstress-api-key');
}

function wordstress_options() {
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }
?>
  <div class="wrap">
  <h3>Wordstress</h3>
  <p><a href="https://rubygems.org/gems/wordstress">Wordstress</a> is a security scanner designed for wordpress powered websites. In order to achieve a <strong>false positives free</strong> security reports, a whitebox scan is the best choice. This implies security scanner to be run by websites owner who must quickly react to vulnerabilities without losing time in check security advisories seeing if the affected version matches the one installed.</p>
  <p>Wordstress approach is to have in input a list of installed plugins and themes with their version number and using <a href="http://wpvulndb.com/">this great public wordpress vulnerability database</a> to give <strong>only</strong> the vulnerabilities your website really has.</p>
  <p>This plugin will introduce a wordstress virtual page you can call at http://yourblogurl/wordstress. In this page there will be stored wordpress, plugins and themes names with version numbe.r</p>
  <p>In order to use <a href="https://rubygems.org/gems/wordstress">wordstress</a> gem, you must have a key. We don't want bad guys to have information on what is installed on this website.</p>
  <p>The key is, hopefully, generated in a secure way in order to minimize guessing attempts. If you don't like the automatically generated key, just reload this page.</p>
<?php
  if (get_option('wordstress-api-key')) {
    echo '<p>Current wordstress key is '.esc_attr(get_option('wordstress-api-key')).'</p>';
  } else
    echo '<p>You don\'t have a key defined. Save the one generated below.</p>';
  ?>
  <form method="post" action="options.php">
    <?php settings_fields( 'wordstress-settings-group' ); ?>
    <?php do_settings_sections( 'wordstress-settings-group' ); ?>
   <table class="form-table">
      <tr valign="top">
      <th scope="row">New API Key:</th>
       <td> <input type="text"  name="wordstress-api-key" id="wordstress-api-key" value="<?php echo create_key();?>" size="40"></input></td>
</tr>
</table>
    <?php submit_button(); ?>
  </form>
  </div>
<?php } ?>
