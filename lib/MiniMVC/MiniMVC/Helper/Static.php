<?php

class Helper_Static extends MiniMVC_Helper
{
	public function get($file, $module = null, $app = null)
	{
        if ($module === null)
        {
            $module = $this->module;
        }
        $app = ($app) ? $app : $this->registry->settings->get('currentApp');

        if ($cache = $this->registry->settings->get('view/staticCached/'.$app.'_'.$module.'_'.str_replace('/', '__', $file)))
        {
            return $cache;
        }

        $prefixHash = $this->registry->settings->get('view/static/prefixHash', false);

        if ($baseurl = $this->registry->settings->get('apps/'.$app.'/baseurlStatic')) {
            if (is_array($baseurl)) {
                $baseurl = array_values($baseurl);
                $baseurl = $baseurl[hexdec(substr(md5($file), 0, 6)) % count($baseurl)];
            }
        }
        else {
            $baseurl = $this->registry->settings->get('apps/'.$app.'/baseurl', '');
        }

        if ($module !== null && file_exists(APPPATH.$app.'/web/'.$module.'/'.$file))
        {
            if ($prefixHash) {
                $parts = explode('.', $file);
                $ext = array_pop($parts);
                $file = implode('.', $parts) . '_' . md5(file_get_contents(APPPATH.$app.'/web/'.$module.'/'.$file)) . '_.' . $ext;
            }           
            $url = $baseurl.'app/'.$app.'/'.$module.'/'.$file;
        }
        elseif ($module !== null && file_exists(MODULEPATH.$module.'/web/'.$file))
        {
            if ($prefixHash) {
                $parts = explode('.', $file);
                $ext = array_pop($parts);
                $file = implode('.', $parts) . '_' . md5(file_get_contents(MODULEPATH.$module.'/web/'.$file)) . '_.' . $ext;
            }
            $url = $baseurl.'module/'.$module.'/'.$file;
        }
        elseif (file_exists(APPPATH.$app.'/web/'.$file))
        {
            if ($prefixHash) {
                $parts = explode('.', $file);
                $ext = array_pop($parts);
                $file = implode('.', $parts) . '_' . md5(file_get_contents(APPPATH.$app.'/web/'.$file)) . '_.' . $ext;
            }
            $url = $baseurl.'app/'.$app.'/'.$file;
        }
        elseif (file_exists(WEBPATH.$file))
        {
            if ($prefixHash) {
                $parts = explode('.', $file);
                $ext = array_pop($parts);
                $file = implode('.', $parts) . '_' . md5(file_get_contents(WEBPATH.$file)) . '_.' . $ext;
            }
            $url = $baseurl.$file;
        }
        else
        {
            $url = $baseurl.$file;
        }

        $this->registry->settings->set('view/staticCached/'.$app.'_'.$module.'_'.str_replace('/', '__', $file), $url);

        return $url;
	}
}