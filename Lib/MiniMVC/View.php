<?php
/**
 * MiniMVC_View is the default view class
 *
 * @property MiniMVC_Helpers $helper
 * @property MiniMVC_Translation $t
 */
class MiniMVC_View
{
	protected $vars = array();
	protected $registry = null;
    protected $module = null;
    protected $controller = null;
    protected $action = null;
    /**
     *
     * @var MiniMVC_Helpers
     */
	protected $helper = null;
    /**
     *
     * @var MiniMVC_Translation
     */
    protected $t = null;

    /**
     *
     * @param mixed $module the name of the associated module or null
     */
	public function __construct($module = null, $controller = null, $action = null)
	{
        $this->module = $module;
        $this->controller = $controller;
        $this->action = $action;
		$this->registry = MiniMVC_Registry::getInstance();

        $this->helper = $this->registry->helper;
        $this->t = $this->helper->I18n->get($this->module);
	}

    /**
     *
     * @return mixed returns the name of the associated module or null
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     *
     * @param string $var the name of the var to set
     * @param mixed $value the value to store
     */
	public function __set($var, $value)
	{
		$this->vars[$var] = $value;
	}

    /**
     *
     * @param string $var the name of the var
     * @return mixed the stored value
     */
	public function __get($var)
	{
        if ($var == 'helper')
        {
            return $this->helper;
        }
        elseif($var == 't')
        {
            return $this->t;
        }
		return (isset($this->vars[$var])) ? $this->vars[$var] : '';
	}

    /**
     *
     * @param string $file the file to parse
     * @param mixed $module the name of the module that contains the file or null to use the current module
     * @return string returns the parsed output of the file
     */
	public function parse($file = null, $module = null)
	{
        if ($module === null)
        {
            if ($this->module === null)
            {
                return false;
            }
            $module = $this->module;
        }
        if ($file === null) {
            if (!$this->controller || !$this->action) {
                return false;
            }
            $file = $this->controller . '/' . $this->action;
        }
		$app = $this->registry->settings->currentApp;
		
        $format = $this->registry->template->getFormat();
        $formatString = ($format) ? '.'.$format : '';

        if ($module != '_default')
        {
            $appPath = 'App/'.$app.'/View/'.$module.'/'.$file.$formatString.'.php';
            $path = 'Module/'.$module.'/View/'.$file.$formatString.'.php';
        }
        else
        {
            $appPath = false;
            $path = 'App/'.$app.'/View/'.$file.$formatString.'.php';
        }
        
        if ($appPath && is_file(BASEPATH.$appPath))
		{
			$path = $appPath;
		}
		elseif (!is_file(BASEPATH.$path))
		{
			throw new Exception('View "'.$path.'" not found!');
		}
		extract($this->vars);
        $helper = $this->helper;
        $t = $this->t;
		ob_start();
		include (BASEPATH.$path);
		return ob_get_clean();
	}
	
	public function toJSON($data = null)
	{
		return json_encode(($data !== null) ? $this->vars : $data);
	}
	
	public function toXML($data)
	{
		$xml = new XmlWriter();
		$xml->openMemory();
		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElement('root');
				
		$this->writeXML($xml, $data);
		
		$xml->endElement();
		echo $xml->outputMemory(true);
	}
	
	private function writeXML(XMLWriter $xml, $data){
	    foreach($data as $key => $value){
	        if(is_array($value)){
	            $xml->startElement($key);
	            $this->writeXML($xml, $value);
	            $xml->endElement();
	            continue;
	        }
	        $xml->writeElement($key, $value);
	    }
	}
}