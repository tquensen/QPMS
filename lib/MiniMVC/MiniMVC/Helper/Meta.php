<?php

class Helper_Meta extends MiniMVC_Helper
{
    protected $title = array();
    protected $metas = array();
    protected $links = array();
    protected $titleSeparator = '';
    protected $titleAlign = '';


    public function __construct($module = null)
    {
        parent::__construct($module);

        $i18n = $this->registry->helper->i18n->get('_default');
        $this->title = array($i18n->pageTitle);
        $this->setMeta('description', $i18n->pageDescription);

        $this->titleSeparator = $this->registry->settings->get('view/meta/titleSeparator', ' ');
        $titleAlign = $this->registry->settings->get('view/meta/titleAlign', 'rtl');
        $this->titleAlign = ($titleAlign == 'rtl') ? 'rtl' : 'ltr';
    }

    public function setTitle($title, $append = true)
    {
        if ($append) {
            if ($this->titleAlign == 'ltr') {
                array_push($this->title, $title);
            } else {
                array_unshift($this->title, $title);
            }
        } else {
            if ($this->titleAlign == 'ltr') {
                $pageTitle = array_shift($this->title);
                $this->title = array($pageTitle, $title);
            } else {
                $pageTitle = array_pop($this->title);
                $this->title = array($title, $pageTitle);
            }
        }
    }

    public function getTitle($array = false)
    {
        return ($array) ? $this->title : implode($this->titleSeparator, $this->title);
    }

    public function setMeta($name, $content, $isHttpEquiv=false)
    {
        $this->metas[$name] = $isHttpEquiv ? array('http-equiv' => $name, 'content' => $content) : array('name' => $name, 'content' => $content);
    }

    public function getMeta($name = null, $contentOnly = true)
    {
        if ($name === null) {
            return $this->metas;
        }
        return isset($this->metas[$name]) ? ($contentOnly ? $this->metas[$name]['content'] : $this->metas[$name]) : null;
    }

    public function setLink($rel, $href = null, $title = null, $type = null)
    {
        $this->links[] = array('rel' => $rel, 'href' => $href, 'title' => $title, 'type' => $type);
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function setDescription($content)
    {
        $this->setMeta('description', $content);
    }

    public function getDescription()
    {
        return $this->getMeta('description');
    }

    public function setKeywords($content)
    {
        $this->setMeta('keywords', $content);
    }

    public function getKeywords()
    {
        return $this->getMeta('keywords');
    }

    public function get()
    {
        return array('title' => $this->getTitle(), 'metas' => $this->getMeta(), 'links' => $this->getLinks());
    }

    public function getHtml($module = null, $partial = 'meta')
    {
        return $this->registry->helper->partial->get($partial, $this->get(), $module ? $module : $this->module);
    }









}