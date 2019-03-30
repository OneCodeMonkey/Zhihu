<?php
/**
 * Created by PhpStorm.
 * User: huangzhijie
 * Date: 2018/5/16
 * Time: 下午4:17
 */

ini_set('session.cookie_lifetime', 1800);
ini_set('session.cookie_domain', '.wecenter.com');
ini_set('session.cookie_path', '/');
session_start();
if (!defined('IN_ANWSION')) {
    die;
}
header('Access-Control-Allow-Origin:http://www.wecenter.com');
header('Access-Control-Allow-Credentials:true');

/**
 * 仅限官网下载使用
 * Class main
 */
class main extends AWS_CONTROLLER
{
    public function get_access_rule()
    {
        $rule_action['rule_type'] = "white";    // 黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
        $rule_action['actions'][] = 'send_code';
        $rule_action['actions'][] = 'valid_code';
        $rule_action['actions'][] = 'download';
        return $rule_action;
    }

    public function send_code_action()
    {
        $company_name = $_GET['companyName'];
        $person_name = $_GET['personName'];
		$common_email = $_GET['companyEmail'];
        $mobile = $_GET['mobile'];
        $file_name = $_GET['fileName'];
        if (!isset($company_name)) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, "请输入公司名称"));
        }
        if (!isset($person_name)) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, "请输入姓名"));
        }
        if (!isset($mobile) || strlen($mobile) != 11) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, "请输入正确的11位手机号"));
        }
		if (!H::valid_email($common_email))
   		{
 			H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('请输入正确的邮箱地址')));
  		}
		
        $this->model('down')->save_record($company_name, $person_name, $mobile, $file_name,$common_email);
        $code = rand(pow(10, 5), pow(10, 6) - 1);
        $res = $this->sendSms($mobile, "SMS_135170094", ["code" => $code]);
        if (!isset($res) || $res->Code != 'OK') {
            H::ajax_json_output(AWS_APP::RSM(null, -1, "短信发送失败"));
        }

        if (isset($_SESSION['smsCode'])) {
            $next_send_time = $_SESSION['smsCode']['next_send_time'];
            if ($next_send_time > time()) {
                H::ajax_json_output(AWS_APP::RSM(null, -1, '请等待' . ($next_send_time - time()) . '秒后重现发送短信'));
            }
        }
        $session_data = array(
            'code' => $code,
            'next_send_time' => time() + 60,
            'expire' => time() + 1800
        );
        $_SESSION['smsCode'] = $session_data;
        H::ajax_json_output(AWS_APP::RSM(null, 1, "短信发送成功"));
    }

    public function valid_code_action()
    {
        $code = $_GET['code'];
        $fileName = $_GET['fileName'];
        if (!isset($code)) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, '请填写验证码'));
        }
        if (!isset($fileName)) {
            H::ajax_json_output(AWS_APP::RSM(null, -1, '未找到文件'));
        }
        if (isset($_SESSION['smsCode']) and $code == $_SESSION['smsCode']['code']) {
            $_SESSION['smsCode']['valid'] = true;
            $_SESSION['smsCode']['fileName'] = $fileName;
            H::ajax_json_output(AWS_APP::RSM(null, 1));
        } else {
            H::ajax_json_output(AWS_APP::RSM(null, -1, '验证码填写不正确或已过期'));
        }
    }

    public function download_action()
    {
        if ($_SESSION['smsCode']['valid'] != true) {
            die("下载无权限");
        }
        $fileName = $_SESSION['smsCode']['fileName'];
        unset($_SESSION);
        session_destroy();
        header('Content-type:	application/octet-stream');
        header('Content-Disposition: attachment;filename=' . $fileName);
        header('X-Accel-Redirect: /download/' . $fileName);
        header("X-Accel-Buffering: yes");
    }

    public function sendSms($phone, $templateCode, $templateParam)
    {

        $params = array();

        // *** 需用户填写部分 ***

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "dZvGJmkWWWROcYAA";
        $accessKeySecret = "8p7FmHWAJhyY2peRVIQZNMNIC1y7nI";

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $phone;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "辛普科技";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $templateCode;

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = $templateParam;


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求

        // 此处可能会抛出异常，注意catch
        $content = $this->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );

        return $content;
    }

    public function request($accessKeyId, $accessKeySecret, $domain, $params, $security = false)
    {
        $apiParams = array_merge(array(
            "SignatureMethod" => "HMAC-SHA1",
            "SignatureNonce" => uniqid(mt_rand(0, 0xffff), true),
            "SignatureVersion" => "1.0",
            "AccessKeyId" => $accessKeyId,
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "Format" => "JSON",
        ), $params);
        ksort($apiParams);

        $sortedQueryStringTmp = "";
        foreach ($apiParams as $key => $value) {
            $sortedQueryStringTmp .= "&" . $this->encode($key) . "=" . $this->encode($value);
        }

        $stringToSign = "GET&%2F&" . $this->encode(substr($sortedQueryStringTmp, 1));

        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $accessKeySecret . "&", true));

        $signature = $this->encode($sign);

        $url = ($security ? 'https' : 'http') . "://{$domain}/?Signature={$signature}{$sortedQueryStringTmp}";

        try {
            $content = $this->fetchContent($url);
            return json_decode($content);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

    private function fetchContent($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-sdk-client" => "php/2.0.0"
        ));

        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $rtn = curl_exec($ch);

        if ($rtn === false) {
            trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
        }
        curl_close($ch);

        return $rtn;
    }
}

