<?php
//$rights = MiniMVC_Registry::getInstance()->rights;
$MiniMVC_routes['MODLC.defaultIndex'] = array(
    'route' => 'MODLC',
    'controller' => 'MODULE_Default',
    'action' => 'index',
    'parameter' => array(),
    'method' => 'GET',
    'rights' => 0 //$rights->getRights('user')
);
$MiniMVC_routes['MODLC.defaultIndex.json'] = array(
    'route' => 'MODLC/index.json',
    'controller' => 'MODULE_Default',
    'action' => 'index',
    'format' => 'json',
    'method' => 'GET',
    'parameter' => array(),
    'rights' => 0 //$rights->getRights('user')
);
$MiniMVC_routes['MODLC.defaultCreate'] = array(
    'route' => 'MODLC/create',
    'controller' => 'MODULE_Default',
    'action' => 'create',
    'method' => array('GET', 'POST'),
    'parameter' => array(),
    'active' => false, //this route must be activated for each app to work
    'rights' => 0 //$rights->getRights('publish')
);
$MiniMVC_routes['MODLC.defaultShow'] = array(
    'route' => 'MODLC/:id:',
    'controller' => 'MODULE_Default',
    'action' => 'show',
    'method' => 'GET',
    //'model' => array('MODULE', 'id'), // array(modelname, property, parameter) or array('model1' => array(modelname, property, parameter),'modelx' => array(modelname, property, parameter))
                                        //automatically load a model with the name modelname by the field 'property' (defaults to the models identifier) with the value provided py routeparameter :parameter: (defaults to the property)
                                        // returns null if not found // in your controller, you can access it with $params['model'] (or $params['model']['model1'], $params['model']['modelx'] if multiple models were defined)
    'parameter' => array('id' => false),
    'rights' => 0 //$rights->getRights('user')
);
$MiniMVC_routes['MODLC.defaultEdit'] = array(
    'route' => 'MODLC/:id:/edit',
    'controller' => 'MODULE_Default',
    'action' => 'edit',
    'method' => array('GET', 'POST'),
    //'model' => array('MODULE', 'id'),
    'parameter' => array('id' => false),
    'active' => false, //this route must be activated for each app to work
    'rights' => 0 //$rights->getRights('publish')
);
$MiniMVC_routes['MODLC.defaultDelete'] = array(
    'route' => 'MODLC/:id:/delete',
    'controller' => 'MODULE_Default',
    'action' => 'delete',
    'method' => array('DELETE'),
    //'model' => array('MODULE', 'id'),
    'parameter' => array('id' => false),
    'active' => false, //this route must be activated for each app to work
    'rights' => 0 //$rights->getRights('publish')
);