<?php

/**
 * Base_TestExampleModel
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $default_name
 * @property string $name
 * @property blob $description
 * @property integer $status
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Base_TestExampleModel extends MiniMVC_Model
{
    public function setTableDefinition()
    {
        $this->setTableName('test_example_model');
        $this->hasColumn('default_name', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('description', 'blob', null, array(
             'type' => 'blob',
             ));
        $this->hasColumn('status', 'integer', null, array(
             'type' => 'integer',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $i18n0 = new Doctrine_Template_I18n(array(
             'fields' => 
             array(
              0 => 'name',
              1 => 'description',
             ),
             ));
        $this->actAs($i18n0);
    }
}