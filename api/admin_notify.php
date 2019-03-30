<?php

$notify = <<<EOF
<li><a href="http://wenda.wecenter.com/question/29645" target="_blank">WeCenter 3.1.9 发布</a></li>
EOF;

$notify = str_replace(array("\n", "\r", "\t"), '', $notify);

echo $_GET['jsoncallback'] . '({
	"notify" : "' . addslashes($notify) . '",
})';

