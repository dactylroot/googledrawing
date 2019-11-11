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

    // Url for use to linking to google image document
    private function getImageUrl($imageId)
    {
        $baseUrl = $this->getGoogleUrl();
        $imageUrl = $baseUrl."drawings/d/".$imageId;
        return $imageUrl;
    }

    public function connectTo($mode) {
                $this->Lexer->addSpecialPattern('{{gdraw>.*?}}',$mode,'plugin_googledrawing'); 

                $baseUrl = $this->getGoogleUrl();
                $baseUrl = str_replace('/', '\\/', $baseUrl);
                
                $this->Lexer->addSpecialPattern(
                        "{$baseUrl}drawings\\/(?:[a-z]+\\?id=|d\\/)[a-zA-Z\\-_0-9]+(?:\\/edit)?(?:\\&w=[0-9]+)?(?:\\&h=[0-9]+)?",
                        $mode,
                        'plugin_googledrawing');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler){

        $data = array(
                     'id'  => '1EJyBXdSdnJ1mi6h371GXWdL0lz0Oj0lO9vcq_burdfs',
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
                        // Parse a drawings URL
        else
        {
            preg_match("/{$baseUrl}drawings\\/(?:[a-z]+\\?id=|d\\/)([^\\/\\&]+)(?:\\/edit)?(?:\\&w=([0-9]+))?(?:\\&h=([0-9]+))?/",
                       $conf, 
                       $match);

            $data['id'] = $match[1];
            $data['width'] = $match[2];
            $data['height'] = $match[3];
        } 
        if($data['width'] == null)
        {
            $data['width'] = $this->getConf('defaultImageWidth');
        }
        if($data['height'] == null)
        {
            $data['height'] = $this->getConf('defaultImageHeight');
        }

        return $data;
    }

    // TODO: Clean up the image tag generated in this function
    public function render($mode, Doku_Renderer $renderer, $data) {
        if($mode != 'xhtml') return false;

        $baseUrl = $this->getGoogleUrl();
        $imageUrl = $this->getImageUrl($data['id']);
        $tag = '<img';
		$url = ' src="' . $baseUrl . 'drawings/d/' . $data['id'] . "/export/png";
        if($data['width'] > 0)
        {
            $tag .= ' width="' . $data['width'] . '"';
        }

        if($data['height'] > 0)
        {
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
        $tag = "<a target='_blank' href='{$imageUrl}'>{$tag}</a>";
        $renderer->doc .= $tag;

        return true;
    }
}
