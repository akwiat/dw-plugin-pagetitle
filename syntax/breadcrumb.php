<?php
/**
 * DokuWiki plugin PageTitle Breadcrums; Syntax component
 * Macro to set the short title of the page in metadata
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_pagetitle_breadcrumb extends DokuWiki_Syntax_Plugin {

    protected $mode;
    protected $pattern = array();

    private   $counter = null;  // helper component "pagetitle_counter"
    private   $handledOnce  = null;
    private   $renderedOnce = null;

    function __construct() {
        $this->mode = substr(get_class($this), 7); // drop 'syntax_' from class name

        //syntax patterns
        $this->pattern[5] = '~~ShortTitle:.*?~~';

        // assign helper component to dedicated class property
        $this->counter = $this->loadHelper('pagetitle_counter', true);
    }

    function getType() { return 'substition'; }
    function getPType(){ return 'normal'; }
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

        // assign a closure function to the class property
        if ($this->handledOnce === null) {
            $this->handledOnce = $this->counter->create_counter($item);
        }

        // ensure first matched pattern only effective
        //if (($n = call_user_func($this->handledOnce, $ID)) > 0) { // PHP < 7.0
        if (($n = ($this->handledOnce)($ID)) > 0) {
            error_log($this->mode.' Handle ignore '.$n.' '.$match.' '.$ID);
            return false;
        }

        $short_title = trim(substr($match, 13, -2));
        return array($state, $short_title, $ID);
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $renderer, $data) {
        global $ID;

        list($state, $short_title, $id) = $data;

        // skip calls for different pages (eg. title of included page)
        if (strcmp($id, $ID) !== 0) return false;

        // assign a closure function to the class property
        if ($this->renderedOnce === null) {
            $this->renderedOnce = $this->counter->create_counter($item);
        }

        // ensure first instruction only effective
        //if (($n = call_user_func($this->renderedOnce, $format)) > 0) { // PHP < 7.0
        if (($n = ($this->renderedOnce)($format)) > 0) {
            error_log($rhis->mode.' Render ignore '.$n.' '.$format.' '.$short_title.' '.$ID);
            return false;
        }

        switch ($format) {
            case 'metadata':
                $renderer->meta['shorttitle'] = $short_title;
                return true;
            default:
                return false;
        }
    }

}
