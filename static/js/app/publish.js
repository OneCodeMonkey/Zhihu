$(function()
{
	if ($('#question_id').length)
	{
		ITEM_ID = $('#question_id').val();
	}
	else if ($('#article_id').length)
	{
		ITEM_ID = $('#article_id').val();
	}
    else
    {
        ITEM_ID = '';
    }

    // 判断是否开启ck编辑器
	if (G_ADVANCED_EDITOR_ENABLE == 'Y')
	{
		// 初始化编辑器
		var editor = CKEDITOR.replace( 'wmd-input' );
	}

    if (ATTACH_ACCESS_KEY != '' && $('.aw-upload-box').length)
    {
    	if (G_ADVANCED_EDITOR_ENABLE == 'Y')
		{
	    	var fileupload = new FileUpload('file', '.aw-editor-box .aw-upload-box .btn', '.aw-editor-box .aw-upload-box .upload-container', G_BASE_URL + '/publish/ajax/attach_upload/id-' + PUBLISH_TYPE + '__attach_access_key-' + ATTACH_ACCESS_KEY, {
					'editor' : editor
				});
	    }
	    else {
	    	var fileupload = new FileUpload('file', '.aw-editor-box .aw-upload-box .btn', '.aw-editor-box .aw-upload-box .upload-container', G_BASE_URL + '/publish/ajax/attach_upload/id-' + PUBLISH_TYPE + '__attach_access_key-' + ATTACH_ACCESS_KEY, {
					'editor' : $('.wmd-input')
				});
	    }
    }

    if (ITEM_ID && G_UPLOAD_ENABLE == 'Y' && ATTACH_ACCESS_KEY != '')
    {
        if ($(".aw-upload-box .upload-list").length) {
            $.post(G_BASE_URL + '/publish/ajax/' + PUBLISH_TYPE + '_attach_edit_list/', PUBLISH_TYPE + '_id=' + ITEM_ID, function (data) {
                if (data['err']) {
                    return false;
                } else {
                	if (data['rsm']['attachs'])
                	{
                		$.each(data['rsm']['attachs'], function (i, v) {
	                        fileupload.setFileList(v);
	                    });
                	}
                }
            }, 'json');
        }
    }

    AWS.Dropdown.bind_dropdown_list($('.aw-mod-publish #question_contents'), 'publish');

    //初始化分类
	if ($('#category_id').length)
	{
		var category_data = '', category_id;

		$.each($('#category_id option').toArray(), function (i, field) {
			if ($(field).attr('selected') == 'selected')
			{
				category_id = $(this).attr('value');
			}
			if (i > 0)
			{
				if (i > 1)
				{
					category_data += ',';
				}

				category_data += "{'title':'" + $(field).text() + "', 'id':'" + $(field).val() + "'}";
			}
		});

		if(category_id == undefined)
		{
			category_id = CATEGORY_ID;
		}

		$('#category_id').val(category_id);

		AWS.Dropdown.set_dropdown_list('.aw-publish-title .dropdown.category', eval('[' + category_data + ']'), category_id);

		$('.aw-publish-title .dropdown.category li a').click(function() {
			$('#category_id').val($(this).attr('data-value'));
		});

		$.each($('.aw-publish-title .dropdown.category .aw-dropdown-list li a'),function(i, e)
		{
			if ($(e).attr('data-value') == $('#category_id').val())
			{
                var html = $(e).html().length > 6 ? $(e).html().substr(0, 6) + '...' : $(e).html();
				$('#aw-topic-tags-select-category').html(html);
			}
		});
	}
    //初始化专栏
    if ($('#column_id').length)
    {
        var column_data = '', column_id;

        $.each($('#column_id option').toArray(), function (i, field) {
            if ($(field).attr('selected') == 'selected')
            {
                column_id = $(this).attr('value');
            }
            if (i > 0)
            {
                if (i > 1)
                {
                    column_data += ',';
                }

                column_data += "{'title':'" + $.trim($(field).text()) + "', 'id':'" + $.trim($(field).val()) + "'}";
            }
        });

        if(column_id == undefined)
        {
            column_id = COLUMN_ID;
        }

        $('#column_id').val(column_id);
        AWS.Dropdown.set_dropdown_list('.aw-publish-title .dropdown.column', eval('[' + column_data + ']'), column_id);

        $('.aw-publish-title .dropdown.column li a').click(function() {
            $('#column_id').val($(this).attr('data-value'));
        });

        $.each($('.aw-publish-title .dropdown.column .aw-dropdown-list li a'),function(i, e)
        {
            if ($(e).attr('data-value') == $('#column_id').val())
            {
                var html = $(e).html().length > 6 ? $(e).html().substr(0, 6) + '...' : $(e).html();
                $('#aw-topic-tags-select-column').html(html);
            }
        });
    }
	//自动展开话题选择
	$('.aw-edit-topic').click();

    // 自动保存草稿
    if($('#wmd-input').length){
        if(G_DRAFT_ENABLED == 'Y'){
            if (G_ADVANCED_EDITOR_ENABLE == 'Y'){
                //富文本
                EDITOR_CALLBACK = function (evt){
                    if (evt.editor.getData().length){
                        $.post(G_BASE_URL + '/account/ajax/save_draft/item_id-1__type-' +　PUBLISH_TYPE, 'message=' + evt.editor.getData(), function (result) {
                            $('#question_detail_message').html(result.err + ' <a href="#" onclick="$(\'textarea#advanced_editor\').attr(\'value\', \'\'); AWS.User.delete_draft(1, \'' + PUBLISH_TYPE + '\'); $(this).parent().html(\' \'); return false;">' + _t('删除草稿') + '</a>');
                        }, 'json');
                    }
                }
                // 自动保存草稿
                editor.on( 'blur', EDITOR_CALLBACK);
            }else{
                //纯文本
                $('#wmd-input').bind('blur',function(){
                    var  article_textarea = $('#wmd-input').val();
                    if(article_textarea != ''){
                        $.post(G_BASE_URL + '/account/ajax/save_draft/item_id-1__type-' +　PUBLISH_TYPE, 'message=' + article_textarea, function (result) {
                            $('#question_detail_message').html(result.err + ' <a href="#" onclick="$(\'textarea#advanced_editor\').attr(\'value\', \'\'); AWS.User.delete_draft(1, \'' + PUBLISH_TYPE + '\'); $(this).parent().html(\' \'); return false;">' + _t('删除草稿') + '</a>');
                        }, 'json');
                    }
                });
            }
        }   
    }

    // 手动保存草稿
    $('#draft').click(function() {
        if (G_ADVANCED_EDITOR_ENABLE == 'Y' && G_DRAFT_ENABLED == 'N'){
            var  article_textarea = CKEDITOR.instances['wmd-input'].getData();
        }else{
            var  article_textarea = $('#wmd-input').val();
        }
        if(article_textarea != ''){
            $.post(G_BASE_URL + '/account/ajax/save_draft/item_id-1__type-' +　PUBLISH_TYPE, 'message=' + article_textarea, function (result) {
                $('#question_detail_message').html(result.err + ' <a href="#" onclick="$(\'textarea#advanced_editor\').attr(\'value\', \'\'); AWS.User.delete_draft(1, \'' + PUBLISH_TYPE + '\'); $(this).parent().html(\' \'); return false;">' + _t('删除草稿') + '</a>');
            }, 'json');
        }
    });

$(function(){setTimeout(uplaodImage,400);});
//使用FormData形式，将base64图片转换成formData再提交(图片不限制大小)
function uplaodImage(){
   editor.on('change',function(e){//content为textarea的id
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
                                            var a =editor.document.$.getElementsByTagName("img")[i]; //content为textarea的id
                                            a.setAttribute('data-cke-saved-src',imgurl);
                                                  }
                                });
                       }
              }
    });
}
});
