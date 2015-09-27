<?php
/**
 * DokuWiki plugin PageTitle; Helper component
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */

if (!defined('DOKU_INC')) die();

class helper_plugin_pagetitle extends DokuWiki_Plugin {

    /**
     * Hierarchical breadcrumbs for PageTitle plugin
     *
     * @param int    $start_depth of hierarchical breadcrumbs
     * @param bool   $print if false return content
     * @return bool|string html, or false if no data, true if printed
     */
    function tpl_youarehere($start_depth = 0, $print = true) {

        $out = $this->html_youarehere($start_depth);
        if ($print) {
            echo $out; return (bool) $out;
        }
        return $out;
    }

    function html_youarehere($start_depth = 0) {
        global $conf, $ID, $lang;

        if ($ID == $conf['start']) {
            $page = '';
        } elseif (noNS($ID) == $conf['start']) {
            $page = ':'.getNS($ID);  // drop tailing start
        } else {
            $page = ':'.$ID;
        }

        $parts = explode(':', $page);
        $depth = count($parts) -1;

        $out = '';
        //$out = '<span class="bchead">'.$lang['youarehere'].' </span>';

        $ns = '';
        for ($i = $start_depth; $i < count($parts); $i++) {
            $ns.= $parts[$i];
            $id = $ns ?: $conf['start'];
            resolve_pageid('', $id, $exists);
            if (!$exists) {
                $id = $ns.':';
                resolve_pageid('', $id, $exists);
            }
            $name = p_get_metadata($id, 'shorttitle') ?: $parts[$i];
            $out.= '<bdi>'.$this->html_pagelink($id, $name, $exists).'</bdi>';
            if ($i < $depth) $out.= ' ›&#x00A0;'; // separator
            $ns.= ':';
        }
        return $out;
    }


    /**
     * Prints a link to a WikiPage
     * a customised function based on 
     *   tpl_pagelink() defined in inc/template.php,
     *   html_wikilink() defined in inc/html.php, 
     *   internallink() defined in inc/parser/xhtml.php
     *
     * @param string $id    page id
     * @param string $name  the name of the link
     * @param bool   $exists
     * @param bool   $print if false return content
     * @return bool|string html, or false if no data, true if printed
     */
    function tpl_pagelink($id = null, $name = null, $exists = null, $print = true) {
        global $conf;

        $out = $this->html_pagelink($id, $name, $exists);
        if ($print) {
            echo $out; return (bool) $out;
        } 
        return $out;
    }

    function html_pagelink($id = null, $name = null, $exists = null) {
        global $conf, $ID;

        if (is_null($id)) $id = $ID;

        $title = p_get_metadata($id, 'title');
        if (empty($title)) $title = $id;

        if (is_null($exists)) {
            $class = (page_exists($id)) ? 'wikilink1' : 'wikilink2';
        } else {
            $class = ($exists) ? 'wikilink1' : 'wikilink2';
        }

        $short_title = $name;
        if (empty($name)) {
            $short_title = p_get_metadata($id, 'shorttitle') ?: noNS($id);
        }

        $out = '<a href="'.$this->wl($id).'" class="'.$class.'" title="'.hsc($title).'">';
        $out.= hsc($short_title).'</a>';
        return $out;
    }


    /**
     * builds url of a wikipage
     * a simplified function of DokuWiki wl() defined inc/common.php
     *
     * @param string   $id  page id
     * @return string
     */
    function wl($id = null) {
        global $conf;

        if (noNS($id) == $conf['start']) $id = ltrim(getNS($id).':', ':');
        idfilter($id);

        $xlink = DOKU_BASE;

        switch ($conf['userewrite']) {
            case 2: // eg. DOKU_BASE/doku.php/wiki:syntax
                $xlink .= DOKU_SCRIPT.'/'.$id;
            case 1: // eg. DOKU_BASE/wiki:syntax
                $xlink .= $id;
                break;
            default:
                $xlink .= DOKU_SCRIPT;
                $xlink .= ($id) ? '?id='.$id : '';
        }
        return rtrim($xlink,'/');
    }


    /**
     * Prints or returns the title of the given page (current one if none given)
     * modified from DokuWiki original function tpl_pagetitle() 
     * defined in inc/template.php
     * This variant function returns title from metadata, ignoring $conf['useheading']
     *
     * @param string $id    page id
     * @param bool   $print if false return content
     * @return bool|string html, or false if no data, true if printed
     */
    function tpl_pagetitle($id = null, $print = true) {

        $out = $this->pagetitle($id);
        if ($print) {
            echo $out; return (bool) $out;
        } 
        return $out;
    }

    function pagetitle($id = null) {
        global $ACT, $ID, $conf, $lang;

        if (is_null($id)) {
            $title = (p_get_metadata($ID, 'title')) ?: $ID;
        } else {
            $title = (p_get_metadata($id, 'title')) ?: $id;
        }

        // default page title is the page name, modify with the current action
        switch ($ACT) {
            // admin functions
            case 'admin' :
                $page_title = $lang['btn_admin'];
                // try to get the plugin name
                /** @var $plugin DokuWiki_Admin_Plugin */
                if ($plugin = plugin_getRequestAdminPlugin()){
                    $plugin_title = $plugin->getMenuText($conf['lang']);
                    $page_title = $plugin_title ? $plugin_title : $plugin->getPluginName();
                }
                break;

            // user functions
            case 'login' :
            case 'profile' :
            case 'register' :
            case 'resendpwd' :
                $page_title = $lang['btn_'.$ACT];
                break;

            // wiki functions
            case 'search' :
            case 'index' :
                $page_title = $lang['btn_'.$ACT];
                break;

            // page functions
            case 'edit' :
                $page_title = "✎ ".$title;
                break;

            case 'revisions' :
                $page_title = $title . ' - ' . $lang['btn_revs'];
                break;

            case 'backlink' :
            case 'recent' :
            case 'subscribe' :
                $page_title = $title . ' - ' . $lang['btn_'.$ACT];
                break;

            default : // SHOW and anything else not included
                $page_title = $title;
        }
        return hsc($page_title);
    }
}
