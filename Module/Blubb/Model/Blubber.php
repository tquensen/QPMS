<?php
class Blubber extends MiniMVC_Model
{

	public function __construct($table = null)
	{
        if ($table) {
            $this->_table = $table;
        } else {
            $this->_table = new BlubberTable();
        }
	}

}