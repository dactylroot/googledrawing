<?php
/**
 * action.php for Plugin googleDrawings
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Guillaume Turri <guillaume.turri@gmail.com>
 */

if(!defined('DOKU_INC')) die();
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_googledrawing extends Dokuwiki_Action_Plugin {
	
  function register(Doku_Event_Handler $controller) {
    $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array ());
  }

  /**
   * Inserts a toolbar button
   */
   
  function insert_button(&$event, $param) {
    $event->data[] = array (
        'type' => 'format',
        'title' => $this->getLang('button'),
        'icon' => '/lib/plugins/googledrawing/drawings.png',
        'open' => '{{gdraw>1EJyBXdSdnJ1mi6h371GXWdL0lz0Oj0lO9vcq_burdfs',
        'close' => ' width= title= center}}',
        );
  }
}
