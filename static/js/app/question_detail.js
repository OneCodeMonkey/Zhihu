var ATTACH_ACCESS_KEY;
var COMMENT_UNFOLD;
var QUESTION_ID;
var UNINTERESTED_COUNT;
var EDITOR;
var EDITOR_CALLBACK;

$(function()
{
    //问题页添加评论
    AWS.Init.init_comment_box('.aw-add-comment');

    if (G_ADVANCED_EDITOR_ENABLE == 'Y' && document.getElementById("wmd-input")){
    	EDITOR = CKEDITOR.replace( 'wmd-input');
    }

	if ($('#c_log_list').attr('id'))
	{
		AWS.load_list_view(G_BASE_URL + '/question/ajax/log/id-' + QUESTION_ID, $('#bp_log_more'), $('#c_log_list'));
	}
	else
	{
		ITEM_IDS = ITEM_IDS.split(',');

		if ($('#wmd-input').length)
		{
			
			if(G_DRAFT_ENABLED == 'Y'){
			//富文本
				if(G_ADVANCED_EDITOR_ENABLE == 'Y'){
					EDITOR_CALLBACK = function (evt){
						if (evt.editor.getData().length){
							$.post(G_BASE_URL + '/account/ajax/save_draft/item_id-' + QUESTION_ID + '__type-' + ANSWER_TYPE, 'message=' + evt.editor.getData(), function (result) {
								$('#answer_content_message').html(result.err + ' <a href="#" onclick="$(\'textarea#advanced_editor\').attr(\'value\', \'\'); AWS.User.delete_draft(QUESTION_ID, ANSWER_TYPE); $(this).parent().html(\' \'); return false;">' + _t('删除草稿') + '</a>');
							}, 'json');
						}
					}
					// 自动保存草稿
					EDITOR.on( 'blur', EDITOR_CALLBACK);
				}else{
					//纯文本
					$('#wmd-input').bind('blur',function(){
						var  article_textarea = $('#wmd-input').val();
		                if(article_textarea != ''){
		                    $.post(G_BASE_URL + '/account/ajax/save_draft/item_id-' + QUESTION_ID + '__type-' + ANSWER_TYPE, 'message=' + article_textarea, function (result) {
								$('#answer_content_message').html(result.err + ' <a href="#" onclick="$(\'textarea#advanced_editor\').attr(\'value\', \'\'); AWS.User.delete_draft(QUESTION_ID, ANSWER_TYPE); $(this).parent().html(\' \'); return false;">' + _t('删除草稿') + '</a>');
							}, 'json');
		                }
					});
					
				}
			}

		}

		if ($('.aw-upload-box').length)
		{
			if (G_ADVANCED_EDITOR_ENABLE == 'Y')
			{
				var fileupload = new FileUpload('file', '.aw-upload-box .btn', '.aw-upload-box .upload-container', G_BASE_URL + '/publish/ajax/attach_upload/id-' + ANSWER_TYPE + '__attach_access_key-' + ATTACH_ACCESS_KEY, {
					'editor' : EDITOR
				});
			}
			else
			{
				var fileupload = new FileUpload('file', '.aw-upload-box .btn', '.aw-upload-box .upload-container', G_BASE_URL + '/publish/ajax/attach_upload/id-' + ANSWER_TYPE + '__attach_access_key-' + ATTACH_ACCESS_KEY, {
					'editor' : $('.wmd-input')
				});
			}
		}


		//折叠回复
		$.each($('.aw-question-comment .aw-item'), function (i, e)
		{
			if ($(this).attr('uninterested_count') >= UNINTERESTED_COUNT || $(this).attr('force_fold') == 1)
			{
				$('#uninterested_answers_list').append($(e));
			}
		});

		//折叠回复数量
		if ($('#uninterested_answers_list div.aw-item').length > 0)
		{
			$('#load_uninterested_answers span.hide_answers_count').html($('#uninterested_answers_list div.aw-item').length);
			$('#load_uninterested_answers').fadeIn();
		}

		//回复折叠显示按钮
	    $('#load_uninterested_answers a').click(function()
	    {
	    	$('#uninterested_answers_list').toggle();
	    });

		//自动展开评论
		if (COMMENT_UNFOLD == 'all')
		{
			$('.aw-add-comment').click();
		}
		else if (COMMENT_UNFOLD == 'question')
		{
			$('.aw-question-detail-meta .aw-add-comment').click();
		}

		//回复高亮
		$.each(ITEM_IDS, function (i, answer_id) {
			if ($('#answer_list_' + answer_id).attr('id'))
			{
				if ($('#answer_list_' + answer_id).find('.aw-add-comment').data('comment-count') > 0)
				{
					$('#answer_list_' + answer_id).find('.aw-add-comment').click();
				}

				AWS.hightlight($('#answer_list_' + answer_id), 'active');
			}
		});
	}

	//关注用户列表
	$.get(G_BASE_URL + '/question/ajax/get_focus_users/question_id-' + QUESTION_ID, function (result) {
		if (result)
		{
			$.each(result, function (i, e) {
				if (e['uid'])
				{
					$('#focus_users').append('<a href="' + e['url'] + '"><img src="' + e['avatar_file'] + '" class="aw-user-name" data-id="' + e['uid'] + '" alt="' + e['user_name'] + '" /></a> ');
				}
				else
				{
					$('#focus_users').append('<a href="javascript:;" title="' + _t('匿名用户') + '"><img src="' + e['avatar_file'] + '" alt="' + _t('匿名用户') + '" /></a> ');
				}
			});
		}
	}, 'json');

    //邀请回答按钮操作
    $('.aw-question-detail .aw-invite-replay').click(function()
    {
    	$('.aw-question-detail .aw-comment-box, .aw-question-detail .aw-question-related-box').hide();
    	if ($('.aw-question-detail .aw-invite-box').is(':visible'))
    	{
    		$('.aw-question-detail .aw-invite-box').fadeOut();
    	}
    	else
    	{
    		$('.aw-question-detail .aw-invite-box').fadeIn();
    	}
    });

    //邀请初始化
    for (var i = 0; i < 4; i++)
    {
    	$('.aw-question-detail .aw-invite-box ul li').eq(i).show();
    }

    // 邀请翻页
    if ($('.aw-question-detail .aw-invite-box .mod-body ul li').length <=4 )
    {
    	//长度小于4翻页隐藏
    	$('.aw-question-detail .aw-invite-box .mod-footer').hide();
    }
    else
    {
    	//邀请上一页
	    $('.aw-question-detail .aw-invite-box .prev').click(function()
	    {
	    	if (!$(this).hasClass('active'))
	    	{
	    		var flag = 0, list = $('.aw-question-detail .aw-invite-box ul li');

	    		$.each(list, function (i, e)
		    	{
		    		if ($(this).is(':visible') == true)
		    		{
		    			flag = $(this).index();

		    			return false;
		    		}
		    	});

		    	list.hide();

		    	for (var i = 0; i < 4; i++)
	    		{
	    			flag--;

	    			if (flag >= 0)
	    			{
	    				list.eq(flag).show();
	    			}
	    		}
	    		if (flag <= 0)
				{
					$('.aw-question-detail .aw-invite-box .prev').addClass('active');
				}

		    	$('.aw-question-detail .aw-invite-box .next').removeClass('active');
	    	}
	    });

	    //邀请下一页
	    $('.aw-question-detail .aw-invite-box .next').click(function()
	    {
	    	if (!$(this).hasClass('active'))
	    	{
	    		var flag = 0, list = $('.aw-question-detail .aw-invite-box ul li');

	    		$.each(list, function (i, e)
		    	{
		    		if ($(this).is(':visible') == true)
		    		{
		    			flag = $(this).index();
		    		}
		    	});

	    		list.hide();

	    		for (var i = 0; i < 4; i++)
	    		{
	    			if (flag + 1 <= list.length)
	    			{
	    				flag++;

	    				list.eq(flag).show();

	    				if (flag + 1 == list.length)
	    				{
	    					$('.aw-question-detail .aw-invite-box .next').addClass('active');
	    				}
	    			}
	    		}

		 		$('.aw-question-detail .aw-invite-box .prev').removeClass('active');

	    	}
	    });
    }

    //邀请用户下拉绑定
    AWS.Dropdown.bind_dropdown_list($('.aw-invite-box #invite-input'), 'invite');

    //邀请用户回答点击事件
	$(document).on('click', '.aw-invite-box .aw-dropdown-list a', function () {
	    AWS.User.invite_user($(this),$(this).find('img').attr('src'));
	});

	//相关链接按钮
	$('.aw-question-detail .aw-add-question-related').click(function()
	{
		$('.aw-question-detail .aw-comment-box, .aw-question-detail .aw-invite-box').hide();
    	if ($('.aw-question-detail .aw-question-related-box').is(':visible'))
    	{
    		$('.aw-question-detail .aw-question-related-box').fadeOut();
    	}
    	else
    	{
    		/*给邀请三角形定位*/
    		$('.aw-question-detail .aw-question-related-box .i-dropdown-triangle').css('left', $(this).position().left + 14);
    		$('.aw-question-detail .aw-question-related-box').fadeIn();
    	}
	});
	// 手动保存草稿
    $('#draft').click(function() {
        if (G_ADVANCED_EDITOR_ENABLE == 'Y' && G_DRAFT_ENABLED == 'N'){
            var  article_textarea = CKEDITOR.instances['wmd-input'].getData();
        }else{
            var  article_textarea = $('#wmd-input').val();
        }
        if(article_textarea != ''){
            $.post(G_BASE_URL + '/account/ajax/save_draft/item_id-' + QUESTION_ID + '__type-' + ANSWER_TYPE, 'message=' + article_textarea, function (result) {
				$('#answer_content_message').html(result.err + ' <a href="#" onclick="$(\'textarea#advanced_editor\').attr(\'value\', \'\'); AWS.User.delete_draft(QUESTION_ID, ANSWER_TYPE); $(this).parent().html(\' \'); return false;">' + _t('删除草稿') + '</a>');
			}, 'json');
        }
    });
    //回复内容超链接新窗口打开
    $('.markitup-box a').attr('target','_blank');
$(function(){setTimeout(uplaodImage,400);});
//使用FormData形式，将base64图片转换成formData再提交(图片不限制大小)
function uplaodImage(){
   EDITOR.on('change',function(e){//content为textarea的id
              var a = e.editor.document ;
              var b = a.find("img");
              var count = b.count();
              for(var i=0;i<count;i++){
                       var src =b.getItem(i).$.src;//获取img的src
                       if(src.substring(0,10)=='data:image'){ //判断是否是二进制图像，是才处理
                           var img1=src.split(',')[1]; 
                          var img2=window.atob(img1); 
                                $.ajax({
                                        type:"POST",
                                        url:G_BASE_URL + '/publish/ajax/paste/',//服务器url
                                        async:false,//同步，因为修改编辑器内容的时候会多次调用change方法，所以要同步，否则会多次调用后台
                                        data:{data:img1},
                                        dataType:'json',
                                        // processData: false,
                                        // contentType: false,
                                        success:function(json){
                                             var imgurl=json.path; //获取回传的图片url
                                             console.log(json);
                                              b.getItem(i).$.src=imgurl;
                                            var a =EDITOR.document.$.getElementsByTagName("img")[i]; //content为textarea的id
                                            a.setAttribute('data-cke-saved-src',imgurl);
                                                  }
                                });
                       }
              }
    });
}
});

function one_click_add_topic(selector, topic_title, question_id)
{
	$.post(G_BASE_URL + '/topic/ajax/save_topic_relation/', 'type=question&item_id=' + question_id + '&topic_title=' + topic_title, function (result) {
		if (result.err)
		{
			AWS.alert(result.err);
		}
		else
		{
			$('.aw-topic-bar .tag-bar').prepend('<span class="topic-tag" data-id="' + result.rsm.topic_id + '"><a class="text" href="topic/' + result.rsm.topic_id + '">' + topic_title + '</a></a></span>').hide().fadeIn();

			selector.hide();
		}
	}, 'json');
}
