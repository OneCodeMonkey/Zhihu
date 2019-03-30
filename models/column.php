<?php
if (!defined('IN_ANWSION'))
{
	die;
}

class column_class extends AWS_MODEL
{
	
	public function remove_column($id)
	{

		$this->delete_column($id);
        $ret = $this->model('posts')->remove_posts_index($id, 'column');

		return $ret;
	}

	public function get_like_status_by_uid($article_id, $uid)
	{
			return $this->count('favorite_tag', 'item_id = ' . intval($article_id) . ' AND uid = ' . intval($uid).' AND type = "article"');
	}

    public function get_column_by_uid($uid)
    {
        return $this->fetch_all('column', "is_verify = 1 and uid = " . intval($uid));
    }

    public function get_column_by_uid_s($uid)
    {
    	return $this->fetch_all('column', "uid = " . intval($uid));
    }

	public function get_column_by_id($id)
	{
		return $this->fetch_row('column', 'column_id = ' . intval($id));
	}

	public function fetch_column_list($uid , $page, $limit = 10, $sort_type = 'sum' ,$type = false)
	{   

		if($type){//获取本月

			$start = strtotime(date('Y-m-01 00:00:00'));
			$end = time();
			$order = 'focus_count DESC';
			$where = 'is_verify = 1 AND add_time >= '.$start.' AND add_time <= '.$end;
            
		}else{

			$order = 'sort ASC';
			$where = 'is_verify = 1';

		}

		$list = $this->fetch_page('column', $where , $order , $page, $limit);
        
		if(!empty($list)){

			foreach ($list as $key => $val) {

			    $list[$key] = $val;

			    $list[$key]['has_focus_column'] = $this->has_focus_column($uid, $val['column_id']);

                $list[$key]['views_count'] = $this->get_column_views_num($val['column_id']);

                $list[$key]['article_count'] = $this->get_column_article_num($val['column_id']);

                $list[$key]['votes_count'] = $this->get_column_votes_num($val['column_id']);

                $views[$key] = $list[$key]['views_count'];

                $articles[$key] = $list[$key]['article_count'];

                $addtime[$key] = $list[$key]['add_time'];

                $sort[$key] = $list[$key]['sort'];
			}

	        
	        switch ($sort_type) {
	        	case 'new':
	        		array_multisort($addtime, SORT_DESC, SORT_NUMERIC ,$list);
	        		break;
	        	case 'hot':
	        		array_multisort($articles, SORT_DESC,$views, SORT_DESC,$list);
	        		break;
	        	case 'sum':
	        		array_multisort($articles, SORT_DESC, $sort , SORT_ASC,$list);
	        		break;
	        }
		}

		return $list;
	}

	public function fetch_column_check($page, $limit = 10)
	{
		return $this->fetch_page('column','is_verify=0', 'sort ASC', $page, $limit);
	}

    public function update_column_enabled($arr, $enabled)
	{   
         
		return $this->update('column', array(
			'is_verify' => $enabled,
            'reson'=> $this->quote($arr['reson'])
		), 'column_id = ' . intval($arr['id']));
	}


	public function fetch_user_article_list($column_id , $page , $limit = 10 , $filter = '' , $order = 'add_time DESC' )
	{   
		if($column_id)
		{
			$where[] = 'column_id = ' . $column_id;
		}


		if($filter)	
		{
			$category_id = $this->fetch_one('category','id','title like "'.$filter.'"');
			
			if($category_id)
			{
				$category_ids = $this->model('category')->getcategory_ids($category_id);
                
				$where[] = 'category_id not in ('.implode(',',$category_ids) .')';
			}
			
		}

        return $this->fetch_page('article', implode(' AND ', $where) , $order , $page, $limit);
	}


	public function update_column_reson($id , $reson)
	{   
		return $this->update('column', array(
				'reson' => $this->quote($reson),
				'is_verify' => -1
		), 'column_id = ' . intval($id));
	}


	public function update_column_sort($id , $sort)
	{
		return $this->update('column', array(
				'sort' => ($sort),
		), 'column_id = ' . intval($id));
	}

    //板块文章浏览数
	public function get_column_views_num($column_id)
	{   
		$category_ids = $this->check_suggest();
		
		if($category_ids)
		{
			return $this->sum('article','views','column_id = '.$column_id.' and category_id not in ('.implode(",", $category_ids).')');
		}else
		{
			return $this->sum('article','views','column_id = '.$column_id);
		}

        
	}

    //板块文章点赞数
	public function get_column_votes_num($column_id)
	{   
		$category_ids = $this->check_suggest();
		if($category_ids)
		{
			return $this->sum('article','votes','column_id = '.$column_id.' and category_id not in ('.implode(",", $category_ids).')');
		}else
		{
			return $this->sum('article','votes','column_id = '.$column_id);
		}
        
	}

    //板块文章数
	public function get_column_article_num($column_id)
	{   
		$category_ids = $this->check_suggest();

        if($category_ids)
		{
			return $this->count('article','column_id = '.$column_id.' and category_id not in ('.implode(",", $category_ids).')');

		}else
		{
			return $this->count('article','column_id = '.$column_id);
		}

        
	}


	public function column_info_nums($column_id)
	{
		//计算文章数
        $article_count = $this->get_column_article_num($column_id);
		//计算文章浏览数
        $view_count = $this->get_column_views_num($column_id);
		//计算文章点赞数
        $vote_count = $this->get_column_votes_num($column_id);
        return ['article_count'=>$article_count,'view_count'=>$view_count,'vote_count'=>$vote_count];
	}

    public function delete_column($column_id)
    {
        $column_id = intval($column_id);
        $column_info = $this->get_column_by_id($column_id);
        $uid = $column_info['uid'];
        $this->delete('column', "column_id=" . $column_id);
        $this->delete('column_focus', 'column_id=' . $column_id);
        $articles = $this->fetch_all('article', 'column_id=' . $column_id);
        foreach ($articles as $article) {
            $this->model('article')->remove_article($article['id']);
        }
        $this->shutdown_update('users', array(
            'column_count' => $this->count('column', 'uid = ' . intval($uid))
        ), 'uid = ' . intval($uid));
    }

    public function apply_column($column_name,$column_description,$column_pic,$uid)
    {
        $column_id=$this->insert('column', [
            'column_name' => $column_name,
            'column_description' => $column_description,
            'column_pic' => $column_pic,
            'uid' => $uid,
            'add_time' => time(),
        ]);
        $this->shutdown_update('users', array(
            'column_count' => $this->count('column', 'uid = ' . intval($uid))
        ), 'uid = ' . intval($uid));
        return $column_id;
    }

    public function edit_apply_column($column_id,$column_name,$column_description,$column_pic)
    {
        $this->update('column', [
            'column_name' => $column_name,
            'column_description' => $column_description,
            'column_pic' => $column_pic,
            'is_verify' => 0
        ],"column_id=".intval($column_id));
    }

    public function get_focus_column_list($uid, $limit = 20)
    {
        if (!$uid)
        {
            return false;
        }

        if (!$focus_columns = $this->fetch_all('column_focus', 'uid = ' . intval($uid)))
        {
            return false;
        }

        foreach ($focus_columns AS $key => $val)
        {
            $column_ids[] = $val['column_id'];
        }

        $column_list = $this->fetch_all('column', 'column_id IN(' . implode(',', $column_ids) . ')', 'sort DESC', $limit);

        return $column_list;
    }

    //关注
    public function add_focus_column($uid, $column_id)
	{   
		
		if (!$this->has_focus_column($uid, $column_id))
		{   

			if ($this->insert('column_focus', array(
				"column_id" => intval($column_id),
				"uid" => intval($uid),
				"add_time" => time()
			)))
			{
				$this->query('UPDATE ' . $this->get_table('column') . " SET focus_count = focus_count + 1 WHERE column_id = " . intval($column_id));
			}

			$result = 'add';

			// 记录日志
			ACTION_LOG::save_action($uid, $column_id, ACTION_LOG::CATEGORY_COLUMN, ACTION_LOG::ADD_COLUMN_FOCUS);
		}
		else
		{
			if ($this->delete_focus_column($column_id, $uid))
			{
				$this->query('UPDATE ' . $this->get_table('column') . " SET focus_count = focus_count - 1 WHERE column_id = " . intval($column_id));
			}

			$result = 'remove';

			ACTION_LOG::delete_action_history('associate_type = ' . ACTION_LOG::CATEGORY_COLUMN . ' AND associate_action = ' . ACTION_LOG::ADD_COLUMN_FOCUS . ' AND uid = ' . intval($uid) . ' AND associate_id = ' . intval($column_id));
		}

		return $result;
	}


	public function delete_focus_column($column_id, $uid)
	{
		return $this->delete('column_focus', 'uid = ' . intval($uid) . ' AND column_id = ' . intval($column_id));
	}


	public function has_focus_column($uid, $column_id)
	{
		return $this->fetch_one('column_focus', 'focus_id', "uid = " . intval($uid) . " AND column_id = " . intval($column_id));
	}

	

    public function get_my_column_page($uid,$page,$limit)
    {
        return $this->fetch_page('column', "uid = " . intval($uid),'add_time DESC',$page,$limit);
    }


    public function check_suggest()
    {
    	//过滤建议
        $category_id = $this->fetch_one('category','id','title like "建议"');
            
        if($category_id)
        {
            $category_ids = $this->model('category')->getcategory_ids($category_id);
            return $category_ids;
            
        }
        
        return false;

    }


}
