<?php

class Helper_Partial extends MiniMVC_Helper
{

    public function get($_partial, $_data = array(), $_module = null, $_format = null, $_app = null)
    {
        if ($_module === null) {
            $_module = $this->module;
        }
        $_app = ($_app) ? $_app : $this->registry->settings->get('currentApp');
        $_theme = $this->registry->layout->getTheme();
        $_format = $_format ? $_format : $this->registry->layout->getFormat();

        try {
            ob_start();
            $_file = null;
            if ($_cache = $this->registry->cache->get('partialCached/'.$_app.'_'.$_theme.'_'.$_module.'_'.$_format.'_'.str_replace('/', '__', $_partial))) {
                $_file = $_cache;
            } else {
                if (!$_format) {
                    if ($_theme && $_module !== null && file_exists(APPPATH . $_app . '/partial/' . $_theme . '/' . $_module . '/' . $_partial . '.php')) {
                        $_file = APPPATH . $_app . '/partial/' . $_theme . '/' . $_module . '/' . $_partial . '.php';
                    } elseif ($_theme && $_module !== null && file_exists(DATAPATH . 'partial/' . $_theme . '/' . $_module . '/' . $_partial . '.php')) {
                        $_file = DATAPATH . 'partial/' . $_theme . '/' . $_module . '/' . $_partial . '.php';
                    } elseif ($_theme && $_module !== null && file_exists(THEMEPATH . $_theme . '/partial/' . $_module . '/' . $_partial . '.php')) {
                        $_file = THEMEPATH . $_theme . '/partial/' . $_module . '/' . $_partial . '.php';
                    } elseif ($_module !== null && file_exists(APPPATH . $_app . '/partial/' . $_module . '/' . $_partial . '.php')) {
                        $_file = APPPATH . $_app . '/partial/' . $_module . '/' . $_partial . '.php';
                    } elseif ($_module !== null && file_exists(DATAPATH . 'partial/' . $_module . '/' . $_partial . '.php')) {
                        $_file = DATAPATH . 'partial/' . $_module . '/' . $_partial . '.php';
                    } elseif ($_module !== null && file_exists(MODULEPATH . $_module . '/partial/' . $_partial . '.php')) {
                        $_file = MODULEPATH . $_module . '/partial/' . $_partial . '.php';
                    } elseif ($_theme && file_exists(APPPATH . $_app . '/partial/' . $_theme . '/' . $_partial . '.php')) {
                        $_file = APPPATH . $_app . '/partial/' . $_theme . '/' . $_partial . '.php';
                    } elseif ($_theme && file_exists(DATAPATH . 'partial/' . $_theme . '/' . $_partial . '.php')) {
                        $_file = DATAPATH . 'partial/' . $_theme . '/' . $_partial . '.php';
                    } elseif ($_theme && file_exists(THEMEPATH . $_theme . '/partial/' . $_partial . '.php')) {
                        $_file = THEMEPATH . $_theme . 'partial/' . $_partial . '.php';
                    } elseif (file_exists(APPPATH . $_app . '/partial/' . $_partial . '.php')) {
                        $_file = APPPATH . $_app . '/partial/' . $_partial . '.php';
                    } elseif (file_exists(DATAPATH . 'partial/' . $_partial . '.php')) {
                        $_file = DATAPATH . 'partial/' . $_partial . '.php';
                    } elseif (file_exists(MINIMVCPATH . 'data/partial/' . $_partial . '.php')) {
                        $_file = MINIMVCPATH . 'data/partial/' . $_partial . '.php';
                    }
                    if (!$_file) {
                        $_defaultFormat = $this->registry->settings->get('config/defaultFormat');
                        if ($_theme && $_module !== null && file_exists(APPPATH . $_app . '/partial/' . $_theme . '/' . $_module . '/' . $_partial . '.' . $_defaultFormat . '.php')) {
                            $_file = APPPATH . $_app . '/partial/' . $_theme . '/' . $_module . '/' . $_partial . '.' . $_defaultFormat . '.php';
                        } elseif ($_theme && $_module !== null && file_exists(DATAPATH . 'partial/' . $_theme . '/' . $_module . '/' . $_partial . '.' . $_defaultFormat . '.php')) {
                            $_file = DATAPATH . 'partial/' . $_theme . '/' . $_module . '/' . $_partial . '.' . $_defaultFormat . '.php';
                        } elseif ($_theme && $_module !== null && file_exists(THEMEPATH . $_theme . '/partial/' . $_module . '/' . $_partial . '.' . $_defaultFormat . '.php')) {
                            $_file = THEMEPATH . $_theme . '/partial/' . $_module . '/' . $_partial . '.' . $_defaultFormat . '.php';
                        } elseif ($_module !== null && file_exists(APPPATH . $_app . '/partial/' . $_module . '/' . $_partial . '.' . $_defaultFormat . '.php')) {
                            $_file = APPPATH . $_app . '/partial/' . $_module . '/' . $_partial . '.' . $_defaultFormat . '.php';
                        } elseif ($_module !== null && file_exists(DATAPATH . 'partial/' . $_module . '/' . $_partial . '.' . $_defaultFormat . '.php')) {
                            $_file = DATAPATH . 'partial/' . $_module . '/' . $_partial . '.' . $_defaultFormat . '.php';
                        } elseif ($_module !== null && file_exists(MODULEPATH . $_module . '/partial/' . $_partial . '.' . $_defaultFormat . '.php')) {
                            $_file = MODULEPATH . $_module . '/partial/' . $_partial . '.' . $_defaultFormat . '.php';
                        } elseif ($_theme && file_exists(APPPATH . $_app . '/partial/' . $_theme . '/' . $_partial . '.' . $_defaultFormat . '.php')) {
                            $_file = APPPATH . $_app . '/partial/' . $_theme . '/' . $_partial . '.' . $_defaultFormat . '.php';
                        } elseif ($_theme && file_exists(DATAPATH . 'partial/' . $_theme . '/' . $_partial . '.' . $_defaultFormat . '.php')) {
                            $_file = DATAPATH . 'partial/' . $_theme . '/' . $_partial . '.' . $_defaultFormat . '.php';
                        } elseif ($_theme && file_exists(THEMEPATH . $_theme . '/partial/' . $_partial . '.' . $_defaultFormat . '.php')) {
                            $_file = THEMEPATH . $_theme . 'partial/' . $_partial . '.' . $_defaultFormat . '.php';
                        } elseif (file_exists(APPPATH . $_app . '/partial/' . $_partial . '.' . $_defaultFormat . '.php')) {
                            $_file = APPPATH . $_app . '/partial/' . $_partial . '.' . $_defaultFormat . '.php';
                        } elseif (file_exists(DATAPATH . 'partial/' . $_partial . '.' . $_defaultFormat . '.php')) {
                            $_file = DATAPATH . 'partial/' . $_partial . '.' . $_defaultFormat . '.php';
                        } elseif (file_exists(MINIMVCPATH . 'data/partial/' . $_partial . '.' . $_defaultFormat . '.php')) {
                            $_file = MINIMVCPATH . 'data/partial/' . $_partial . '.' . $_defaultFormat . '.php';
                        }
                    }
                } else {
                    if ($_theme && $_module !== null && file_exists(APPPATH . $_app . '/partial/' . $_theme . '/' . $_module . '/' . $_partial . '.' . $_format . '.php')) {
                        $_file = APPPATH . $_app . '/partial/' . $_theme . '/' . $_module . '/' . $_partial . '.' . $_format . '.php';
                    } elseif ($_theme && $_module !== null && file_exists(DATAPATH . 'partial/' . $_theme . '/' . $_module . '/' . $_partial . '.' . $_format . '.php')) {
                        $_file = DATAPATH . 'partial/' . $_theme . '/' . $_module . '/' . $_partial . '.' . $_format . '.php';
                    } elseif ($_theme && $_module !== null && file_exists(THEMEPATH . $_theme . '/partial/' . $_module . '/' . $_partial . '.' . $_format . '.php')) {
                        $_file = THEMEPATH . $_theme . '/partial/' . $_module . '/' . $_partial . '.' . $_format . '.php';
                    } elseif ($_module !== null && file_exists(APPPATH . $_app . '/partial/' . $_module . '/' . $_partial . '.' . $_format . '.php')) {
                        $_file = APPPATH . $_app . '/partial/' . $_module . '/' . $_partial . '.' . $_format . '.php';
                    } elseif ($_module !== null && file_exists(DATAPATH . 'partial/' . $_module . '/' . $_partial . '.' . $_format . '.php')) {
                        $_file = DATAPATH . 'partial/' . $_module . '/' . $_partial . '.' . $_format . '.php';
                    } elseif ($_module !== null && file_exists(MODULEPATH . $_module . '/partial/' . $_partial . '.' . $_format . '.php')) {
                        $_file = MODULEPATH . $_module . '/partial/' . $_partial . '.' . $_format . '.php';
                    } elseif ($_theme && file_exists(APPPATH . $_app . '/partial/' . $_theme . '/' . $_partial . '.' . $_format . '.php')) {
                        $_file = APPPATH . $_app . '/partial/' . $_theme . '/' . $_partial . '.' . $_format . '.php';
                    } elseif ($_theme && file_exists(DATAPATH . 'partial/' . $_theme . '/' . $_partial . '.' . $_format . '.php')) {
                        $_file = DATAPATH . 'partial/' . $_theme . '/' . $_partial . '.' . $_format . '.php';
                    } elseif ($_theme && file_exists(THEMEPATH . $_theme . '/partial/' . $_partial . '.' . $_format . '.php')) {
                        $_file = THEMEPATH . $_theme . 'partial/' . $_partial . '.' . $_format . '.php';
                    } elseif (file_exists(APPPATH . $_app . '/partial/' . $_partial . '.' . $_format . '.php')) {
                        $_file = APPPATH . $_app . '/partial/' . $_partial . '.' . $_format . '.php';
                    } elseif (file_exists(DATAPATH . 'partial/' . $_partial . '.' . $_format . '.php')) {
                        $_file = DATAPATH . 'partial/' . $_partial . '.' . $_format . '.php';
                    } elseif (file_exists(MINIMVCPATH . 'data/partial/' . $_partial . '.' . $_format . '.php')) {
                        $_file = MINIMVCPATH . 'data/partial/' . $_partial . '.' . $_format . '.php';
                    }
                }
                if ($_file === null) {
                    return ob_get_clean();
                }
                $this->registry->cache->set('partialCached/'.$_app.'_'.$_theme.'_'.$_module.'_'.$_format.'_'.str_replace('/', '__', $_partial), $_file);
            }
            extract($_data);
            $h = $this->registry->helper;
            $o = $h->text;
            include $_file;
            return ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }

}