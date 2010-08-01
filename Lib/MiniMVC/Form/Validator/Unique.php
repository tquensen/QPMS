<?php

class MiniMVC_Form_Validator_Unique extends MiniMVC_Form_Validator
{

    public function validate($element, $value)
    {
        if ($this->getOption('values')) {
            return (!in_array($value, $this->getOption('values')));
        } else {
            $model = $this->getOption('model');
            if (method_exists((object)$model, 'getTable')) {
                try {
                    $entry = $model->getTable()->loadOneBy($element->getName() . ' = "' . mysqli_real_escape_string($value).'"');
                    if (!$entry || $entry->getIdentifier() == $model->getIdentifier()) {
                        return true;
                    }
                } catch (Exception $e) {
                    return false;
                }
            }
        }
        return false;
    }

}

