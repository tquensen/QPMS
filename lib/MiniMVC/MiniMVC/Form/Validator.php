<?php
class MiniMVC_Form_Validator
{
	protected $options = array();
    protected $element = null;
    protected $form = null;

	public function __construct($options = array())
	{
		$this->options = $options;
	}

	public function getOption($option)
	{
		return (isset($this->options[$option])) ? $this->options[$option] : null;
	}

	public function setOption($option, $value)
	{
		$this->options[$option] = $value;
	}

	public function __get($option)
	{
		return $this->getOption($option);
	}

	public function __set($option, $value)
	{
		$this->setOption($option, $value);
	}

    public function setElement($element)
    {
        if ($element) {
            $this->element = $element;
            $this->form = $element->getForm();
        }
    }

    public function setForm($form)
    {
        if ($form) {
            $this->form = $form;
        }
    }

    public function getElement()
    {
        return $this->element;
    }

    public function getForm()
    {
        return $this->form;
    }

	public function validate($value)
	{
		return true;
	}
}