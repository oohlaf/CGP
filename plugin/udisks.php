<?php

# Collectd udisks plugin

require_once 'conf/common.inc.php';
#require_once 'type/Default.class.php';
require_once 'type/Udisks.class.php';
require_once 'inc/collectd.inc.php';

## LAYOUT
# udisks-XXXX/
# udisks-XXXX/temperature.rrd
# udisks-XXXX/counter-bad_sectors.rrd
# udisks-XXXX/gauge-SSS_AAAA_T.rrd
#
# XXXX = disk-id
# SSS = SMART attribute id
# AAAA = SMART attribute name
# T = SMART attribute treshold

// TODO move to Udisks class
function format_ds_name($name) {
	$pieces = explode('_', $name);
	$ds_name = implode(' ', array_slice($pieces, 1, -1));
	$ds_name = str_replace('-', ' ', $ds_name);
	$ds_name = ucwords($ds_name);
	return $ds_name;
}

//$obj = new Type_Default($CONFIG);
$obj = new Type_Udisks($CONFIG);

$obj->args['drive'] = str_replace('_', ' ', $obj->args['pinstance']);

// Make graph wider as Disk IDs are long identifiers.
if($obj->width < 600) {
	$obj->width = 600;
}

switch($obj->args['type']) {
	case 'temperature':
		$obj->rrd_title = sprintf('Temperature (%s)', $obj->args['drive']);
		$obj->rrd_vertical = 'Celsius';
	break;
	case 'counter':
		$obj->rrd_title = sprintf('Bad sectors (%s)', $obj->args['drive']);
		$obj->rrd_vertical = 'Count';
	break;
	case 'gauge':
		$obj->rrd_title = sprintf('SMART Health (%s)', $obj->args['drive']);
		$obj->rrd_vertical = 'Normalized Values';
		$obj->rrd_get_sources();
		$obj->ds_names = array_map('format_ds_name', $obj->ds_names);
	break;
}

$obj->rrd_format = '%5.1lf%s';
collectd_flush($obj->identifiers);
$obj->rrd_graph();
