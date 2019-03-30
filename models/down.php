<?php
/**
 *  仅限官网下载使用
 * User: wecenter
 * Date: 2018/5/31
 * Time: 下午3:14
 */

if (!defined('IN_ANWSION')) {
    die;
}

class down_class extends AWS_MODEL
{
    /**
     * 官网下载保存记录
     * @param $company_name 公司名称
     * @param $person_name 个人名称
     * @param $mobile 手机号
     * @param $file_name 文件名
	 * @param $common_email 邮箱
     * @return int id
     */
    public function save_record($company_name, $person_name, $mobile, $file_name,$common_email)
    {
        return $this->insert('down_record', [
            'company_name' => $company_name,
            'person_name' => $person_name,
            'mobile' => $mobile,
            'file_name' => $file_name,
			'common_email' => $common_email,
            'add_time' => time(),
        ]);
    }
}