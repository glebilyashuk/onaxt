<?php

/*
Plugin Name: WPDM - Import Filebase Data
Description: Import Filebase Data to wpdm
Version: 1.0
*/




function wpdm_importfbase(){
    global $wpdb;
    $o_upload_dir = wp_upload_dir();
    $o_upload_dir = $o_upload_dir['basedir'].'/filebase';
    $fbs = get_option('wpfilebase');
    $fbs = maybe_unserialize($fbs);
    $upload_dir = ABSPATH.'/'.$fbs['upload_path'];
    $ids = get_option('_wpdm_fbs_ids',array());
    $item = array();
    if(isset($_POST['task'])&&$_POST['task']=='wdm_save_settings'){
        if(!is_array($ids)) $ids = array();
        if(!is_array($_POST['id'])) $_POST['id'] = array();
        foreach($_POST['id'] as $fid){
            //if(!in_array($fid, $ids)){
            $file = $wpdb->get_row("select * from {$wpdb->prefix}wpfb_files where file_id='$fid'", ARRAY_A);

            $files = strstr($file['file_path'],"://")?array($file['file_path']):array($upload_dir.'/'.$file['file_path']);
            $access = $file['file_user_roles'] != ''?explode("|", $file['file_user_roles']):array('guest');



            foreach($file['files'] as $filepath){
                $fileinfo[$filepath] = array('title'=>basename($filepath), 'password'=>'');
            }



//            $cats = maybe_unserialize($file['category']);
//            $id = wp_insert_post(array(
//                'post_type' => 'wpdmpro',
//                'post_title'=>$file['file_display_name'],
//                'post_content' => $file['file_description'],
//                'post_status' => 'publish',
//                'post_author' => $file['file_added_by'],
//                'tax_input' => array('wpdmcategory'=>explode(",", $file['file_tags'])),
//                'post_date' => $file['file_date'],
//                'comment_status' => 'open'
//            ));

            $file = array(
                'post_type' => 'wpdmpro',
                'post_title'=>$file['file_display_name'],
                'post_content' => $file['file_description'],
                'post_status' => 'publish',
                'post_author' => $file['file_added_by'],
                'tax_input' => array('wpdmcategory'=>explode(",", $file['file_tags'])),
                'post_date' => $file['file_date'],
                'comment_status' => 'open',
                'access' => $access,
                'files' => $files,
                'password' => '',
                'fileinfo' => $fileinfo,
                'page_template' => 'page-template-default.php'
            );

            $id = \WPDM\Package::Create($file);
            update_post_meta($id, '__wpdm_password', '');




        }
        if(is_array($ids))
            $ids = array_unique(array_merge($ids, $_POST['id']));
        else
            $ids = $_POST['id'];
        /*foreach($_POST as $optn=>$optv){
            update_option($optn, $optv);
        }                                      */

        update_option('_wpdm_fbs_ids',$ids);
        die('Copied successfully');
    }

    $res = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpfb_files`", ARRAY_A);

    ?>
    <div class="clear"></div>

    <div class="update-nag" style="margin: 10px 0">Please don't select more then 100 packages at a time</div><Br/>
    <div class="clear"></div>

    <table cellspacing="0" class="table table-striped">
        <thead>
        <tr>
            <th style="width: 30px" class="manage-column column-cb check-column" id="cb" scope="col" ><input class="call m" type="checkbox"></th>
            <th style="" class="manage-column column-media" id="media" scope="col">Filebase Items</th>
            <th style="width: 60px" class="manage-column column-parent" id="parent" scope="col">Imported</th>
        </tr>
        </thead>



        <tbody>
        <?php $altr = 'alternate'; foreach($res as $media) {   $copied = @in_array($media['file_id'],$ids)?'<span style="color: #008800">Yes</span>':'No';  ?>
            <tr>

                <th class="check-column" scope="row"><input type="checkbox" value="<?php echo $media['file_id'];?>" class="m" name="id[]"></th>

                <td class="media column-media">
                    <?php echo $media['file_display_name']?>
                </td>
                <td class="parent column-parent"><b><?php echo $copied; ?></b></td>

            </tr>
        <?php } ?>
        </tbody>
    </table>

    <script language="JavaScript">
        <!--
        jQuery('.call').click(function(){
            if(this.checked)
                jQuery('.m').attr('checked','checked');
            else
                jQuery('.m').removeAttr('checked');
        });
        //-->
    </script>

<?php
}

if(function_exists('add_wdm_settings_tab'))
    add_wdm_settings_tab("importfbase","Filebase Import",'wpdm_importfbase');
