<?php
/**
 * DokuWiki Plugin googledrawing (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Linus Brimstedt <linus@brimstedt.se>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_googledrawing extends DokuWiki_Syntax_Plugin {
    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'normal';
    }

    public function getSort() {
	// Must be before external link (330)
        return 305;
    }


    private function getGoogleUrl()
    {
	$url = $this->getConf('googleUrl');
	if(substr($url, -1) != '/')
	{
		$url .= '/';
	}
	return $url;

    }

    public function connectTo($mode) {
		$this->Lexer->addSpecialPattern('{{gdraw>.*?}}',$mode,'plugin_googledrawing'); 

		$baseUrl = $this->getGoogleUrl();
		$baseUrl = str_replace('/', '\\/', $baseUrl);
		
                $this->Lexer->addSpecialPattern(
                        "{$baseUrl}drawings\\/d\\/[a-zA-Z\\-_0-9]+(?:\\/edit)?(?:\\&w=[0-9]+)?(?:\\&h=[0-9]+)?"
                        ,$mode,'plugin_googledrawing');
		$this->Lexer->addSpecialPattern(
			"{$baseUrl}drawings\\/[a-z]+\\?id=[a-zA-Z\\-_0-9]+(?:\\&w=[0-9]+\\&h=[0-9]+)?"
			,$mode,'plugin_googledrawing'); 
    }


    public function handle($match, $state, $pos, &$handler){

       	$data = array(
       	             'id'  => '1j4V1jYvhZBfOqWDQ-eIsL2vA3nOI9jsNce_ACI9dAwU',
       	             'width'  => 0,
       	             'height'  => 0,
       	             'align'  => '',
       	             'title'  => '',
       	             );
		// Code shamelessly stolen from gchart plugin
		$lines = explode("\n",$match);
	        $conf = array_shift($lines);
	        array_pop($lines);


        // Check for our special {{ }} syntax
	if(substr($match, 0, 2) == '{{')
	{
	

	        if(preg_match('/\bgdraw>([^ ]+?) /i',$conf,$match)) $data['id'] = $match[1];
	        if(preg_match('/\b(left|center|right)\b/i',$conf,$match)) $data['align'] = $match[1];
	        if(preg_match('/\btitle="([^"]+?)"/i',$conf,$match)) $data['title'] = $match[1];
	        if(preg_match('/\b(width|height)=([^\b\}]+?)(\b|})/i',$conf,$match)) $data[$match[1]] = $match[2];

	}
	else
	{
                if(preg_match(
                            "/{$baseUrl}drawings\\/d\\/([a-zA-Z\\-_0-9]+)(?:\\/edit)?(?:\\&w=([0-9]+))?(?:\\&h=([0-9]+))?/"
                           , $conf, $match))
                {
                    $data['id'] = $match[1];
                    $data['width'] = $match[2];
                }
                else
                {
                    preg_match(
                             "/{$baseUrl}drawings\\/[a-z]+\\?id=([a-zA-Z\\-_0-9]+)(?:&w=([0-9]+))?/"
                            , $conf, $match);

                    $data['id'] = $match[1];
                    $data['width'] = $match[2];
                }
	} 
	if($data['width'] == 0 && $data['height'] == 0)
	{
		$data['width'] = 500;
	}

        return $data;
    }

    public function render($mode, &$renderer, $data) {
        if($mode != 'xhtml') return false;

	$baseUrl = $this->getGoogleUrl();
	$tag = '<img';
	$url = ' src="' . $baseUrl . 'drawings/pub?id=' . $data['id'];
	if($data['width'] > 0)
	{
		$url .= '&w=' . $data['width'];
		$tag .= ' width="' . $data['width'] . '"';
	}

	if($data['height'] > 0)
	{
		$url .= '&h=' . $data['height'];
		$tag .= ' height="' . $data['height'] . '"';
	}

	$tag .= ' title="' . $data['title'] . " \n(Click to edit)\"";
	$tag .= ' onError="this.title=\'If the image does not show up, log on to your google docs account!\';"';
	if($data['align'] == 'left')
		$tag .= ' style="display: block; float: left;"';
	if($data['align'] == 'right')
		$tag .= ' style="display: block; float: right;"';
	if($data['align'] == 'center')
		$tag .= ' style="display: block; margin-left: auto; margin-right: auto"';

	$url .= '"';
	$tag .= $url . ' />';
	$tag = "<a target='_blank' href='{$baseUrl}drawings/edit?id={$data['id']}'>{$tag}</a>";
	$renderer->doc .= $tag;

        return true;
    }
}
