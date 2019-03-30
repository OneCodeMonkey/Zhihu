<?php


define('IN_AJAX', TRUE);


if (!defined('IN_ANWSION'))
{
    die;
}

class ajax extends AWS_CONTROLLER
{
    public function get_access_rule()
    {

        $rule_action['rule_type'] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
        $rule_action['actions'] = array(
            'list',
            'article_list',
            'follow_column',
        );
        return $rule_action;

    }
    public function setup()
    {
        HTTP::no_cache_header();
    }
    public function list_action()
    {

    	if ($_GET['per_page'])
		{
			$per_page = intval($_GET['per_page']);
		}
		else
		{
			$per_page = 6;
		}

        if (!$_GET['sort'])
        {
            $_GET['sort'] = 'sum';
        }

        $column_info = $this->model('column')->fetch_column_list($this->user_id,$_GET['page']? :0 , $per_page ,$_GET['sort']);

        /** 最新 */
        TPL::assign('column_info', $column_info);
        TPL::output('column/ajax/column_list');
    }


    public function article_list_action()
	{
		if ($_GET['per_page'])
		{
			$per_page = intval($_GET['per_page']);
		}
		else
		{
			$per_page = 6;
		}

	    $article_list = $this->model('article')->get_articles_list( null,$_GET['page'], $per_page,  'add_time desc', null, false);

	    foreach ($article_list as $key => $value) {
              $article_list[$key]['user'] = $this->model('account')->get_user_info_by_uid($value['uid']);
        }

	    TPL::assign('article_list', $article_list);
	    TPL::output('column/ajax/article_list');
	}

	public function delete_column_action(){
        if (!$column_id=$_POST['column_id']) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, '未选择要删除的专栏'));
        }
        if(!$column_info = $this->model('column')->get_column_by_id($column_id)){
            H::ajax_json_output(AWS_APP::RSM(null, -1, '未找到该专栏'));
        }
        if (!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'] AND !$this->user_info['permission']['edit_column'] AND $column_info['uid'] != $this->user_id)
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, '你没有权限删除该专栏'));
        }
        // !注: 来路检测后面不能再放报错提示
        if (!valid_post_hash($_POST['post_hash']))
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('页面停留时间过长,或内容已提交,请刷新页面')));
        }
        if ($this->user_id != $column_info['uid'])
        {
            $this->send_delete_message($column_info['uid'], $column_info['column_name'], $column_info['column_description']);
        }

        $this->model('column')->delete_column($column_id);
        H::ajax_json_output(AWS_APP::RSM(null,1,null));
    }

    public function send_delete_message($uid, $title, $message)
    {
        $delete_message = AWS_APP::lang()->_t('你申请的专栏 %s 已被管理员删除', $title);
        $delete_message .= "\r\n----- " . AWS_APP::lang()->_t('专栏简介') . " -----\r\n" . $message;
        $delete_message .= "\r\n-----------------------------\r\n";
        $delete_message .= AWS_APP::lang()->_t('如有疑问, 请联系管理员');

        $this->model('email')->action_email('QUESTION_DEL', $uid, get_js_url('/inbox/'), array(
            'question_title' => $title,
            'question_detail' => $delete_message
        ));
        return true;
    }

    public function logo_upload_action()
    {
        AWS_APP::upload()->initialize(array(
            'allowed_types' => 'jpg,jpeg,png,gif',
            'upload_path' => get_setting('upload_dir') . '/column',
            'is_image' => TRUE,
            'max_size' => get_setting('upload_avatar_size_limit'),
        ))->do_upload('aws_upload_file');

        if (AWS_APP::upload()->get_error())
        {
            switch (AWS_APP::upload()->get_error())
            {
                default:
                    die("{'error':'错误代码: " . AWS_APP::upload()->get_error() . "'}");
                    break;

                case 'upload_invalid_filetype':
                    die("{'error':'文件类型无效'}");
                    break;

                case 'upload_invalid_filesize':
                    die("{'error':'文件尺寸过大, 最大允许尺寸为 " . get_setting('upload_avatar_size_limit') .  " KB'}");
                    break;
                case 'upload_file_exceeds_limit':
                    die("{'error':'文件尺寸超出服务器限制'}");
                    break;
            }
        }

        if (! $upload_data = AWS_APP::upload()->data())
        {
            die("{'error':'上传失败, 请与管理员联系'}");
        }

        echo htmlspecialchars(json_encode(array(
            'success' => true,
            'thumb' => get_setting('upload_url') . '/column/'.$upload_data['file_name'])
        ), ENT_NOQUOTES);
    }


    public function apply_action()
    {
        if (!$this->user_info['permission']['publish_column'])
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, '你所在用户组没有权限申请专栏'));
        }
        $column_name = $_POST['name'];
        $column_description = $_POST['description'];
        $column_pic = $_POST['logo_img'];
        $post_hash = $_POST['post_hash'];
        if (!$column_name) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, "请输入专栏名称"));
        }
        if (!$column_description) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, "请输入专栏简介"));
        }
        if (cjk_strlen($column_description) > 60) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, "专栏简介字数不得超过60"));
        }
        if (get_setting('upload_enable') == 'Y' AND get_setting('advanced_editor_enable' == 'Y') AND !$column_pic) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, "请上传专栏封面"));
        }
        // !注: 来路检测后面不能再放报错提示
        if (!valid_post_hash($post_hash))
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('页面停留时间过长,或内容已提交,请刷新页面')));
        }

        $column_id=$this->model('column')->apply_column($column_name, $column_description, $column_pic, $this->user_id);
        H::ajax_json_output(AWS_APP::RSM(['url'=>get_js_url('/column/my/')], 1, '申请成功'));
    }
    public function edit_apply_action()
    {
        if (!$column_info = $this->model('column')->get_column_by_id($_POST['id'])){
            H::ajax_json_output(AWS_APP::RSM(null,-1,'指定专栏不存在'));
        }
        if (!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'] and !$this->user_info['permission']['edit_column'] and $column_info['uid']!=$this->user_id)
        {
            H::ajax_json_output(AWS_APP::RSM(null,-1,'你没有权限编辑这个专栏'));
        }
        if ($column_info['is_verify'] == 0) {
            H::ajax_json_output(AWS_APP::RSM(null,-1,'审核中的专栏无法编辑'));
        }
        $column_name = $_POST['name'];
        $column_description = $_POST['description'];
        $column_pic = $_POST['logo_img'];
        $post_hash = $_POST['post_hash'];
        if (!$column_name) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, "请输入专栏名称"));
        }
        if (!$column_description) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, "请输入专栏简介"));
        }
        if (cjk_strlen($column_description) > 60) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, "专栏简介字数不得超过60"));
        }
        if (get_setting('upload_enable') == 'Y' AND get_setting('advanced_editor_enable' == 'Y') AND!$column_pic) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, "请上传专栏封面"));
        }
        // !注: 来路检测后面不能再放报错提示
        if (!valid_post_hash($post_hash))
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('页面停留时间过长,或内容已提交,请刷新页面')));
        }

        $this->model('column')->edit_apply_column($column_info['column_id'],$column_name, $column_description, $column_pic);
        H::ajax_json_output(AWS_APP::RSM(['url'=>get_js_url('/column/my/')], 1, '编辑成功'));
    }

    public function focus_column_list_action()
    {
        $column_list = $this->model('column')->get_focus_column_list($this->user_id, intval($_GET['page']) * 10 . ', 10');
        TPL::assign('column_list', $column_list);
        TPL::output('topic/ajax/focus_column_list');
    }


    public function focus_column_action()
    {
        H::ajax_json_output(AWS_APP::RSM(array(
            'type' => $this->model('column')->add_focus_column($this->user_id, intval($_POST['column_id']))
        ), '1', null));
    }

    public function load_my_column_page_action()
    {
        $page = intval($_GET['page']);
        if ($columns = $this->model('column')->get_my_column_page($this->user_id,$page,5)) {
            foreach ($columns as $key=>$column) {
                $nums = $this->model('column')->column_info_nums($column['column_id']);
                $columns[$key]['article_count'] = $nums['article_count'];
                $columns[$key]['view_count'] = $nums['view_count'];
                $columns[$key]['vote_count'] = $nums['vote_count'];
            }
        };
        TPL::assign('columns', $columns);
        TPL::output('column/ajax/my_list');
    }
}
