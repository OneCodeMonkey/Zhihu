<?php

if (!defined('IN_ANWSION'))
{
	die;
}

class main extends AWS_CONTROLLER
{

	public function get_access_rule()
	{
		$rule_action['rule_type'] = "white";

		$rule_action['actions'] = array(
			'index',
			'details',
			'my',
		);

		return $rule_action;
	}

	public function setup()
	{
		HTTP::no_cache_header();
        if (get_setting('enable_column') != 'Y')
        {
            H::redirect_msg(AWS_APP::lang()->_t('本站未启用专栏功能'), '/');
        }
		$this->crumb(AWS_APP::lang()->_t('专栏'), '/column/');
	}

	public function index_action()
	{  
        if(!$this->user_id)
        {
            H::redirect_msg(AWS_APP::lang()->_t('请先登陆'), '/');
        }
        
        if (!$_GET['sort'])
        {
            $_GET['sort'] = 'sum';
        }

		$column_info = $this->model('column')->fetch_column_list($this->user_id , 1 , 6 ,$_GET['sort']);

        $this->crumb(AWS_APP::lang()->_t('专栏'), '/column/');
        
        $article_list = $this->model('article')->get_articles_list( null,1, 6,  'add_time desc', null, false);

        foreach ($article_list as $key => $value) {
              $article_list[$key]['user'] = $this->model('account')->get_user_info_by_uid($value['uid']);
        }        

		$recommend_info = $this->model('column')->fetch_column_list($this->user_id, 1 , 5 , 'sum' ,'month');
   
        TPL::import_js('js/layout.js');
        
        TPL::import_css('css/nindex.css');
        //首页专栏
        TPL::assign('column_info', $column_info);

        TPL::assign('column_list_bit', TPL::output('column/ajax/column_list', false));
        //推荐专栏
		TPL::assign('recommend_info', $recommend_info);
		//文章推荐
		TPL::assign('article_list', $article_list);

		TPL::output('column/index');
		
	}



	public function details_action()
	{   
        
        if(!$this->user_id)
        {
            H::redirect_msg(AWS_APP::lang()->_t('请先登陆'), '/');
        }

		if (!$column_info = $this->model('column')->get_column_by_id($_GET['id']))
		{
			H::redirect_msg(AWS_APP::lang()->_t('专栏不存在'), '/');
		}
		if ($column_info['is_verify'] != 1 && $this->user_id != $column_info['uid'])
		{   
			H::redirect_msg(AWS_APP::lang()->_t('专栏未启用或者未审核'), '/');
		}
        
        

		$this->crumb($column_info['column_name'], '/column/details/' . $column_info['column_id']);

		$column_info['user_info']=$this->model('account')->get_user_info_by_uid($column_info['uid']);
        

        $column_info['article_sum_count'] = $this->model('column')->get_column_views_num($column_info['column_id']);
        
        $column_info['article_num'] = $this->model('column')->get_column_article_num($column_info['column_id']);

		$article_list = $this->model('column')->fetch_user_article_list($column_info['column_id'], $_GET['page']? :1 , 6 ,'建议');
        

		TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/column/details/id-' . $column_info['column_id'].''),
            'total_rows' => $this->model('column')->found_rows(),
            'per_page' => 6
        ))->create_links());

		TPL::assign('article_list', $article_list);

		TPL::assign('column_info', $column_info);

        TPL::import_js('js/layout.js');

        TPL::import_css('css/nindex.css');

        TPL::assign('has_focus_column', $this->model('column')->has_focus_column($this->user_id, $column_info['column_id']));
        
        TPL::assign('user_actions', $this->model('actions')->get_user_actions($user['uid'], 5, implode(',', array(
			ACTION_LOG::ADD_QUESTION,
			ACTION_LOG::ANSWER_QUESTION,
			ACTION_LOG::ADD_REQUESTION_FOCUS,
			ACTION_LOG::ADD_AGREE,
			ACTION_LOG::ADD_TOPIC,
			ACTION_LOG::ADD_TOPIC_FOCUS,
			ACTION_LOG::ADD_ARTICLE
		)), $this->user_id));
        //热门文章
        TPL::assign('hot_article_list',$this->model('article')->get_articles_list(null , 1 , 10 , 'views DESC' , null , $column_info['uid']));

		TPL::output('column/details');

	}


	public function my_action(){
        $this->crumb(AWS_APP::lang()->_t('我的专栏'), '/column/my/');
        if ($columns = $this->model('column')->get_my_column_page($this->user_id,1,5)) {
            foreach ($columns as $key=>$column) {
                $nums = $this->model('column')->column_info_nums($column['column_id']);
                $columns[$key]['article_count'] = $nums['article_count'];
                $columns[$key]['view_count'] = $nums['view_count'];
                $columns[$key]['vote_count'] = $nums['vote_count'];
            }
        };
        TPL::assign('sidebar_recommend_users_topics', $this->model('module')->recommend_users_topics($this->user_id));
        TPL::assign('post_hash', new_post_hash());
        TPL::assign('columns', $columns);
        TPL::import_css('css/nindex.css');
        TPL::output('column/my');
    }

    public function apply_action()
    {
        if (!$this->user_info['permission']['publish_column'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('你所在用户组没有权限申请专栏'));
        }
        TPL::import_js('js/fileupload.js');
        TPL::import_css('css/nindex.css');
        TPL::output('column/apply');
    }

    public function edit_apply_action()
    {
        if (!$column_info = $this->model('column')->get_column_by_id($_GET['id'])){
            H::redirect_msg(AWS_APP::lang()->_t('指定专栏不存在'),'/');
        }
        if (!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'] and !$this->user_info['permission']['edit_column'] and $column_info['uid']!=$this->user_id)
        {
            H::redirect_msg(AWS_APP::lang()->_t('你没有权限编辑这个专栏'),'/');
        }
        if ($column_info['is_verify'] == 0) {
            H::redirect_msg(AWS_APP::lang()->_t('审核中的专栏无法编辑'),'/');
        }
        TPL::assign('column', $column_info);
        TPL::import_js('js/fileupload.js');
        TPL::import_css('css/nindex.css');
        TPL::output('column/edit_apply');
    }
}
