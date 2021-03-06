<?php
/**
 * DokuWiki plugin PageTitle YouAreHere; Syntax component
 * Hierarchical breadcrumbs
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_pagetitle_youarehere extends DokuWiki_Syntax_Plugin {

    protected $mode;
    protected $pattern =array();

    function __construct() {
        $this->mode = substr(get_class($this), 7); // drop 'syntax_' from class name

        //syntax patterns
        $this->pattern[5] = '<!-- ?YOU_ARE_HERE ?-->';
    }

    function getType() { return 'substition'; }
    function getPType(){ return 'block'; }
    function getSort() { return 990; }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern($this->pattern[5], $mode, $this->mode);
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        global $ID;
        return array($state, $match, $ID);
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $renderer, $data) {
        global $ID;

        list($state, $match, $id) = $data;

        // skip calls that belong to different pages (eg. title of included page)
        if (strcmp($id, $ID) !== 0) return false;

        $template = $this->loadHelper('pagetitle');

        if ($format == 'xhtml') {
            $renderer->doc .= DOKU_LF.$match.DOKU_LF; // html comment
            $renderer->doc .= '<div class="youarehere">';
            $renderer->doc .= $template->html_youarehere(1); // start_depth = 1
            $renderer->doc .= '</div>'.DOKU_LF;
        }
        return true;
    }

}
