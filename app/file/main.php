<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

class main extends AWS_CONTROLLER
{
	public function download_action()
	{
		if (!$this->user_info['permission']['down_flie'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('你所在用户组没有权限下载附件'));
        }
		$url = @base64_decode($_GET['url']);

		if (! $url)
		{
			HTTP::error_404();
		}

		$path = get_setting('upload_dir') . '/' . str_replace(get_setting('upload_url'), '', $url);

		if (strstr($path, '..') OR !file_exists($path))
		{
			HTTP::error_404();
		}

		HTTP::force_download_header(base64_decode($_GET['file_name']), filesize($path));

		readfile($path);
	}
}
