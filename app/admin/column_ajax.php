<?php

define('IN_AJAX', TRUE);


if (!defined('IN_ANWSION')) {
    die;
}

class column_ajax extends AWS_ADMIN_CONTROLLER
{
    public function setup()
    {
        HTTP::no_cache_header();
    }

    

    public function remove_column_action()
    {
        if (!$this->user_info['permission']['is_administortar']) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('你没有访问权限, 请重新登录')));
        }

        $this->model('column')->remove_column($_POST['column_id']);

        H::ajax_json_output(AWS_APP::RSM(null, 1, null));
    }

    public function save_column_status_action()
    {
       

        if (!$this->user_info['permission']['is_administortar']) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('你没有访问权限, 请重新登录')));
        }

        if (empty($_POST['column_ids'])) {
            H::ajax_json_output(AWS_APP::RSM(null, 0, AWS_APP::lang()->_t('请选择条目再进行操作')));
        }

        if ($_POST['column_ids']) {
            foreach ($_POST['column_ids'] AS $column_id => $val) {
                    $rs = $this->model('column')->update_column_enabled(array('id'=>$val,'reson'=>$_POST['reson'][$column_id]), $_POST["action_type"]);

            }
        }

        if($_POST["action_type"]==1){
            H::ajax_json_output(AWS_APP::RSM(null, 1, AWS_APP::lang()->_t('启用状态已自动保存')));
        }else if($_POST["action_type"]==-1){
            H::ajax_json_output(AWS_APP::RSM(null, 1, AWS_APP::lang()->_t('禁用状态已自动保存')));
        }
    }

    public function reject_column_action()
    {   

        if (!$this->user_info['permission']['is_administortar']) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('你没有访问权限, 请重新登录')));
        }

        $this->model('column')->update_column_reson($_GET['id'],$_GET['reson']);

        H::ajax_json_output(AWS_APP::RSM(null,1, null));

    }

    /**
     * 修改专栏排序
     */
    public function sort_column_action()
    {
        if (!$this->user_info['permission']['is_administortar']) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('你没有访问权限, 请重新登录')));
        }
        $id = $_GET['id'];
        $sort = $_GET['sort'];

        if (!$id) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请选择专栏进行操作')));
        }

        if (!preg_match("/^\d*$/",$sort)) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请输入数字')));
        }

        $sort = intval($sort);

        if ($sort < 0 || $sort > 9999) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请输入0-9999的数字')));
        }

        $this->model('column')->update_column_sort($id, $sort);
        
        H::ajax_json_output(AWS_APP::RSM(null, 1, null));
    }
}