<?php

if (!defined('IN_ANWSION'))
{
    die;
}

class column extends AWS_ADMIN_CONTROLLER
{
    public function setup()
    {
         $this->crumb(AWS_APP::lang()->_t('专栏管理'), 'admin/column/');

        if (!$this->user_info['permission']['is_administortar'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('你没有访问权限, 请重新登录'), '/');
        }

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(299));
    }

    public function list_action()
    {   
		$list= $this->model('column')->fetch_column_list($this->user_id,$_GET['page'], 20);
        
		if($list){

			foreach ($list AS $key => $val)
            {   

                $list_uids[$val['uid']] = $val['uid'];

                $list[$key]['user_info'] = $this->model('account')->get_user_info_by_uid($val['uid']);

            }
		}


        TPL::assign('column_list', $list);

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/column/list/'),
            'total_rows' => $this->model('column')->found_rows(),
            'per_page' => 20
        ))->create_links());

        TPL::output('admin/column/list');
    }
	
	public function check_action()
    {
        $list= $this->model('column')->fetch_column_check($_GET['page'], 20);
        
        if($list){

            foreach ($list AS $key => $val)
            {   

                $list_uids[$val['uid']] = $val['uid'];

                $list[$key]['user_info'] = $this->model('account')->get_user_info_by_uid($val['uid']);

            }
        }
        TPL::assign('column_list', $list);

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/column/check/'),
            'total_rows' => $this->model('column')->found_rows(),
            'per_page' => 20
        ))->create_links());

        TPL::output('admin/column/check');
    }
}