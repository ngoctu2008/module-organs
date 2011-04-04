<?php
/**
 * @Project NUKEVIET 3.0
 * @Author VINADES., JSC (contact@vinades.vn)
 * @Copyright (C) 2010 VINADES ., JSC. All rights reserved
 * @Createdate Dec 3, 2010  11:33:22 AM 
 */

if ( ! defined( 'NV_IS_FILE_ADMIN' ) ) die( 'Stop!!!' );

$page_title = $lang_module['main'];
$base_url = NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=" . $op;
$per_page = 20;
$page = $nv_Request->get_int( 'page', 'get', 0 );
$parentid = $nv_Request->get_int( 'pid', 'get', 0 );

$sql = "SELECT * FROM `" . NV_PREFIXLANG . "_" . $module_data . "_rows` WHERE organid=".intval($parentid);
$result = $db->sql_query( $sql );
$row = $db->sql_fetchrow( $result, 2 );

if (!empty($row)) {
	$page_title = $lang_module['main'] . $lang_module['main_sub'] . $row['title']; 
}

$xtpl = new XTemplate( "main.tpl", NV_ROOTDIR . "/themes/" . $global_config['module_theme'] . "/modules/" . $module_file );
$xtpl->assign( 'LANG', $lang_module );

$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM `" . NV_PREFIXLANG . "_" . $module_data . "_rows` WHERE parentid=".intval($parentid)." ORDER BY weight ASC LIMIT " . $page . "," . $per_page;
$result = $db->sql_query( $sql );

$result_all = $db->sql_query( "SELECT FOUND_ROWS()" );
list( $numf ) = $db->sql_fetchrow( $result_all );
$all_page = ( $numf ) ? $numf : 1;

$i = $page + 1;
while ( $row = $db->sql_fetchrow( $result, 2 ) )
{
    $ck_yes = "";
    $ck_no = "";
    ///////////////////////////////////////////////
    $class = ( $i % 2 ) ? " class=\"second\"" : "";
    if ( $row['active'] == '1' )
    {
        $ck_yes = "selected=\"selected\"";
        $ck_no = "";
    }
    else
    {
        $ck_yes = "";
        $ck_no = "selected=\"selected\"";
    }
    $row['num_no'] = $i;
    $xtpl->assign( 'CHECK_NO', $ck_no );
    $xtpl->assign( 'CHECK_YES', $ck_yes );
    $xtpl->assign( 'class', $class );
    $row['link_edit'] = NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=addrow&amp;id=" . $row['organid'];
    $row['link_del'] = NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=delrow&amp;id=" . $row['organid'];
    $row['link_row'] = NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=".$op."&amp;pid=" . $row['organid'];
    $row['link_per'] = NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=listper&amp;pid=" . $row['organid'];
    $row['select_weight'] = drawselect_number ( $row['organid'], 1, $all_page + 1, $row['weight'], "nv_chang_organs('".$row['organid']."',this,url_change_weight,url_back);" );
    $xtpl->assign( 'ROW', $row );
    $xtpl->parse( 'main.row' );
    $i ++;
}
$xtpl->assign( 'URL_BACK', NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=" . $op ."&amp;pid=" . $parentid );
$xtpl->assign( 'URL_DELALL', NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=delrow" );
$xtpl->assign( 'URL_CHANGE', NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=actrow" );
$xtpl->assign( 'URL_CHANGE_WEIGHT', NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=changeorgan" );
$xtpl->assign( 'URL_ADD', NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=addrow"."&amp;pid=" . $parentid );

$xtpl->assign( 'PAGES', nv_generate_page( $base_url, $all_page, $per_page, $page ) );
$xtpl->parse( 'main' );
$contents = $xtpl->text( 'main' );

include ( NV_ROOTDIR . "/includes/header.php" );
echo nv_admin_theme( $contents );
include ( NV_ROOTDIR . "/includes/footer.php" );

?>