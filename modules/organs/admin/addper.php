<?php
/**
 * @Project NUKEVIET 3.0
 * @Author VINADES., JSC (contact@vinades.vn)
 * @Copyright (C) 2010 VINADES ., JSC. All rights reserved
 * @Createdate Dec 3, 2010  11:33:22 AM 
 */

if ( ! defined( 'NV_IS_FILE_ADMIN' ) ) die( 'Stop!!!' );

$page_title = $lang_module['addper_title'];
$month_dir_module = nv_mkdir( NV_UPLOADS_REAL_DIR . '/' . $module_name, date( "Y_m" ), true );

$data = array( 
    "personid" => 0, "name" => '', "photo" => '', "email" => '', "position" => '', "address" => '', "phone" => '', "birthday" => 0, "description" => '', "addtime" => 0, "edittime" => 0, "organid" => 0, "weight" => 0, "active" => 1 
);
$table_name = NV_PREFIXLANG . "_" . $module_data . "_person";

$base_url = NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=" . $op;

////////////////////////////
$data['organid'] = $nv_Request->get_int( 'pid', 'get', 0 );
$sql = "SELECT * FROM `" . NV_PREFIXLANG . "_" . $module_data . "_rows` WHERE organid=" . intval( $data['organid'] );
$result = $db->sql_query( $sql );
$row = $db->sql_fetchrow( $result, 2 );

if ( ! empty( $row ) )
{
    $page_title = $lang_module['addper_title'] . $lang_module['main_sub'] . $row['title'];
}

/*error*/
$error = "";
/**begin get data post**/
if ( $nv_Request->get_int( 'save', 'post' ) == 1 )
{
    $data['organid'] = $nv_Request->get_int( 'organid', 'post', 0 );
    $data['organid_old'] = $nv_Request->get_int( 'organid_old', 'post', 0 );
    $data['name'] = filter_text_input( 'name', 'post', '', 1 );
    $data['description'] = $nv_Request->get_string( 'description', 'post', '' );
    $data['description'] = nv_nl2br( nv_htmlspecialchars( strip_tags( $data['description'] ) ), '' );
    $data['address'] = filter_text_input( 'address', 'post', '', 1 );
    $data['phone'] = filter_text_input( 'phone', 'post', '', 1 );
    $data['email'] = filter_text_input( 'email', 'post', '', 1 );
    $data['photo'] = filter_text_input( 'photo', 'post', '' );
    $data['position'] = filter_text_input( 'position', 'post', '', 1 );
    $data['active'] = $nv_Request->get_int( 'active', 'post', 0 );
    //* check error*//
    if ( empty( $data['name'] ) )
    {
        $error = $lang_module['error_person_title'];
    }
    elseif ( ! empty( $data['email'] ) )
    {
        if ( nv_check_valid_email( $data['email'] ) != '' )
        {
            $error = $lang_module['error_organ_emal'];
        }
    }
    /**action with none error**/
    if ( empty( $error ) )
    {
        $id = $nv_Request->get_int( 'id', 'get', 0 );
        // Xu ly anh minh ha
        if ( ! nv_is_url( $data['photo'] ) and file_exists( NV_DOCUMENT_ROOT . $data['photo'] ) )
        {
            $lu = strlen( NV_BASE_SITEURL . NV_UPLOADS_DIR . "/" . $module_name . "/" );
            $data['photo'] = substr( $data['photo'], $lu );
        }
        elseif ( ! nv_is_url( $data['photo'] ) )
        {
            $data['photo'] = "";
        }
        $check_thumb = false;
        if ( $id > 0 )
        {
            list( $photo ) = $db->sql_fetchrow( $db->sql_query( "SELECT `photo` FROM `" . $table_name . "` WHERE `id`=" . $id ) );
            if ( $data['photo'] != $photo )
            {
                $check_thumb = true;
                if ( file_exists( NV_UPLOADS_REAL_DIR . "/" . $module_name . "/" . $photo ) )
                {
                    nv_deletefile( NV_UPLOADS_REAL_DIR . "/" . $module_name . "/" . $photo );
                }
            
            }
            else
            {
                $data['photo'] = $photo;
            }
        }
        elseif ( ! empty( $data['photo'] ) )
        {
            $check_thumb = true;
        }
        $photo = NV_UPLOADS_REAL_DIR . "/" . $module_name . "/" . $data['photo'];
        if ( $check_thumb and file_exists( $photo ) )
        {
            require_once ( NV_ROOTDIR . "/includes/class/image.class.php" );
            
            $basename = basename( $photo );
            $image = new image( $photo, NV_MAX_WIDTH, NV_MAX_HEIGHT );
            
            $thumb_basename = $basename;
            $i = 1;
            while ( file_exists( NV_UPLOADS_REAL_DIR . '/' . $module_name . '/thumb/' . $thumb_basename ) )
            {
                $thumb_basename = preg_replace( '/(.*)(\.[a-zA-Z]+)$/', '\1_' . $i . '\2', $basename );
                $i ++;
            }
            
            $image->resizeXY( 150, 150 );
            $image->save( NV_UPLOADS_REAL_DIR . '/' . $module_name . '/thumb', $thumb_basename );
            $image_info = $image->create_Image_info;
            $thumb_name = str_replace( NV_UPLOADS_REAL_DIR . '/' . $module_name . '/', '', $image_info['src'] );
            $image->close();
            
            $data['photo'] = $thumb_name;
        }
        if ( $id == 0 ) // insert data
        {
            list( $weight ) = $db->sql_fetchrow( $db->sql_query( "SELECT max(`weight`) FROM " . $table_name . " WHERE `organid`=" . $db->dbescape( $data['organid'] ) . "" ) );
            $weight = intval( $weight ) + 1;
            $sql = "INSERT INTO " . $table_name . " (`personid`, `name`, `photo`, `email`, `position`, `address`, `phone`, `birthday`, `description`, `addtime`, `edittime`, `organid`, `weight`, `active` ) 
         			VALUES (
         				NULL, 
         				" . $db->dbescape( $data['name'] ) . ",
         				" . $db->dbescape( $data['photo'] ) . ",
         				" . $db->dbescape( $data['email'] ) . ",
         				" . $db->dbescape( $data['position'] ) . ",
         				" . $db->dbescape( $data['address'] ) . ",
         				" . $db->dbescape( $data['phone'] ) . ",
         				" . intval( $data['birthday'] ) . ",
         				" . $db->dbescape( $data['description'] ) . ",
         				UNIX_TIMESTAMP(), 
         				UNIX_TIMESTAMP(), 
         				" . intval( $data['organid'] ) . ",		
         				" . intval( $weight ) . ",
         				" . intval( $data['active'] ) . "
         			)";
            $newcatid = intval( $db->sql_query_insert_id( $sql ) );
            if ( $newcatid > 0 )
            {
                nv_insert_logs( NV_LANG_DATA, $module_name, 'log_add_catalog', "id " . $newcatid, $admin_info['userid'] );
                $db->sql_freeresult();
                nv_fix_organ( $data['organid'] );
                nv_del_moduleCache( $module_name );
                Header( "Location: " . NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=listper&pid=" . $data['organid'] . "" );
                die();
            }
            else
            {
                $error = $lang_module['errorsave'];
            }
        }
        else // update data
        {
            $query = "UPDATE " . $table_name . " 
            		  SET `organid` = " . $db->dbescape( $data['organid'] ) . ",
            		  	  `name` = " . $db->dbescape( $data['name'] ) . ", 
            		  	  `active` = " . intval( $data['active'] ) . ",
            		  	  `description` = " . $db->dbescape( $data['description'] ) . ", 
            		  	  `address` = " . $db->dbescape( $data['address'] ) . ", 
            		  	  `email` = " . $db->dbescape( $data['email'] ) . ", 
            		  	  `phone` = " . $db->dbescape( $data['phone'] ) . ", 
            		  	  `photo` = " . $db->dbescape( $data['photo'] ) . ", 
            		  	  `position` = " . $db->dbescape( $data['position'] ) . ",
            		  	  `birthday` = " . intval( $data['birthday'] ) . ",
            		  	  `edittime` = UNIX_TIMESTAMP() 
            		  WHERE `personid` = " . intval( $id ) . "";
            $db->sql_query( $query );
            if ( $db->sql_affectedrows() > 0 )
            {
                nv_insert_logs( NV_LANG_DATA, $module_name, 'log_edit_catalog', "id " . $id, $admin_info['userid'] );
                $db->sql_freeresult();
                if ( $data['organid'] != $data['organid_old'] )
                {
                    list( $weight ) = $db->sql_fetchrow( $db->sql_query( "SELECT max(`weight`) FROM " . $table_name . " WHERE `organid`=" . $db->dbescape( $data['organid'] ) . "" ) );
                    $weight = intval( $weight ) + 1;
                    $sql = "UPDATE " . $table_name . " SET `weight`=" . $weight . " WHERE `organid`=" . intval( $id );
                    $db->sql_query( $sql );
                    nv_fix_organ( $data['organid'] );
                    nv_fix_organ( $data['organid_old'] );
                }
                nv_del_moduleCache( $module_name );
                Header( "Location: " . NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=listper&pid=" . $data['organid'] . "" );
                die();
            }
            else
            {
                $error = $lang_module['errorsave'];
            }
            $db->sql_freeresult();
        }
    }
}
/**end get data post**/
$id = $nv_Request->get_int( 'id', 'get', 0 );
if ( $id > 0 && $nv_Request->get_int( 'save', 'post' ) == 0 ) // insert data
{
    $sql = "SELECT * FROM `" . $table_name . "` WHERE personid=" . intval( $id );
    $result = $db->sql_query( $sql );
    $data = $db->sql_fetchrow( $result, 2 );
    $data['organid_old'] = $data['organid'];
    if ( ! empty( $data['description'] ) ) $data['description'] = nv_htmlspecialchars( $data['description'] );
    
    if ( ! empty( $data['photo'] ) and file_exists( NV_UPLOADS_REAL_DIR . "/" . $module_name . "/" . $data['photo'] ) )
    {
        $data['photo'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . "/" . $module_name . "/" . $data['photo'];
    }

}

$xtpl = new XTemplate( "addper.tpl", NV_ROOTDIR . "/themes/" . $global_config['module_theme'] . "/modules/" . $module_file );
$xtpl->assign( 'LANG', $lang_module );
$xtpl->assign( 'NV_BASE_ADMINURL', NV_BASE_ADMINURL );
$xtpl->assign( 'NV_NAME_VARIABLE', NV_NAME_VARIABLE );
$xtpl->assign( 'NV_OP_VARIABLE', NV_OP_VARIABLE );
$xtpl->assign( 'module_name', $module_name );
$xtpl->assign( 'NV_UPLOADS_DIR', NV_UPLOADS_DIR );
$xtpl->assign( 'UPLOAD_CURRENT', NV_UPLOADS_DIR . '/' . $module_name . '/' . date( "Y_m" ) );
/* begin set input select parentid */
$sql = "SELECT organid, title, lev FROM " . NV_PREFIXLANG . "_" . $module_data . "_rows ORDER BY `order` ASC";
$result = $db->sql_query( $sql );
$array_cat_list = array();
while ( $row = $db->sql_fetchrow( $result, 2 ) )
{
    $xtitle = "";
    if ( $row['lev'] > 0 )
    {
        for ( $i = 1; $i <= $row['lev']; $i ++ )
        {
            $xtitle .= "---";
        }
    }
    $row['title'] = $xtitle . $row['title'];
    $row['select'] = ( $data['organid'] == $row['organid'] ) ? "selected=\"selected\"" : "";
    $xtpl->assign( 'ROW', $row );
    $xtpl->parse( 'main.parent_loop' );
}

/*end set input select parentid*/

/**begin set NV_EDITOR**/
if ( defined( 'NV_EDITOR' ) )
{
    require_once ( NV_ROOTDIR . '/' . NV_EDITORSDIR . '/' . NV_EDITOR . '/nv.php' );
}
if ( defined( 'NV_EDITOR' ) and function_exists( 'nv_aleditor' ) )
{
    $edits = nv_aleditor( 'description', '100%', '300px', $data['description'] );
}
else
{
    $edits = "<textarea style=\"width: 100%\" name=\"description\" id=\"description\" cols=\"20\" rows=\"15\">" . $data['description'] . "</textarea>";
}
$xtpl->assign( 'NV_EDITOR', $edits );
/**end set NV_EDITOR**/

/*begin set active*/
$data['active_check'] = ( $data['active'] == 1 ) ? "checked=\"checked\"" : "";
/*end set active*/
if ( ! empty( $error ) )
{
    $xtpl->assign( 'error', $error );
    $xtpl->parse( 'main.error' );
}
$xtpl->assign( 'DATA', $data );
$xtpl->parse( 'main' );
$contents = $xtpl->text( 'main' );

include ( NV_ROOTDIR . "/includes/header.php" );
echo nv_admin_theme( $contents );
include ( NV_ROOTDIR . "/includes/footer.php" );
?>