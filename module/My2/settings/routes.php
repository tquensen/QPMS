<?php
$MiniMVC_routes['test'] = array(
    'route' => 'test',
    'controller' => 'My2_Default',
    'action' => 'index',
    'parameter' => array('foo' => 'bar')
);

$MiniMVC_routes['my.formtest'] = array(
    'route' => 'form',
    'controller' => 'My2_Default',
    'action' => 'form'
);

$MiniMVC_routes['baum'] = array(
    'route' => 'baum/:foo:/',
    'controller' => 'My2_Default',
    'action' => 'index',
    'parameter' => array('foo' => 'bar')
);

$MiniMVC_routes['a'] = array(
    'route' => 'a/:foo:/:action:(.:_format:)',
    'routePattern' => 'a/:foo:/:action:(\.:_format:)?',
    'controller' => 'My2_Default',
    'action' => 'test',
    'parameter' => array('foo' => 'bar', '_format' => 'html'),
    'parameterPatterns' => array('_format' => '(json)')
);
