$(function(){
	var id=getQueryString('id');
	//判断是否是外部页面跳转过来
	if(id!=null){

	$('.h-right .listslide').eq(id).addClass('actived').siblings().removeClass('actived');
	$('main .con').eq(id).fadeIn().siblings().hide();
		switch (id) {
			case '0':
				addyhqdata();
				break;
			case '1':
			
				addcjqdata();
				break;
			case '2':
			
				adddddata();
				break;
			default:
				break;
		}

	}else {
		//点击底部栏打开时
	$('main .con').eq(0).fadeIn().siblings().hide();
	$('.h-right .listslide').eq(0).addClass('actived').siblings().removeClass('actived');
		//优惠券数据加载
	$.ajax({
		url:'?type=get',
		type:'post',
		dataType:'json',
		success:function(data){
            var brach=$('.yhq');
            success(brach, data);
			
		},
		error:function(data){
			console.log(data);
		}
	})
	}

	
	//加载优惠券数据
	function addyhqdata(){
		$.ajax({
				url:'?type=get',
				type:'post',
				dataType:'json',
				success:function(data){

					
					var brach=$('.yhq');
					success(brach, data);
					
				},
				error:function(data){
					console.log(data);
				}
			})
	}
	//加载抽奖券数据
	function addcjqdata() {
		$.ajax({
		url:'?type=choujiang',
		type:'post',
		dataType:'json',
		success:function(data){

			success($('.cjq'), data);
		
			
		},
		error:function(data){
			console.log(data);
		}
	})
	}
	//加载订单数据
	function adddddata(){
		$.ajax({
		url:'?type=buy',
		type:'post',
		dataType:'json',
		success:function(data){
			var brach=$('.dd');
			success(brach, data);
			
		},
		error:function(data){
			console.log(data);
		}
	})
	}

    $('.h-right .listslide').on('touchend click',function(e){
        e.preventDefault();
        $(this).addClass('actived').siblings().removeClass('actived');

        switch (e.target.innerHTML) {
            case '抽奖券':
                addcjqdata();
                break;
            case '优惠券':
                addyhqdata();
                break;
            case '订单':
                adddddata();
                break;
            default:
                // statements_def
                break;
        }
        $('main .con').eq($(this).index()).fadeIn().siblings().hide();

    })

	function success(brach, data) {
        brach.html("");

        var data=data.content[0].list;
        if(data.length==0){
            var list=$('<div class="isnull"></div>');
            var a=$('<a href="index.html"></a>');
            var span1=$('<span>抽奖券还是空的</span>');
            var span2=$('<span>去逛逛</span>');
            a.append(span1);
            a.append(span2);
            list.append(a);
            brach.append(list);

        }else{


            var list=$('<div class="cjqlist"></div>');
            for(var i = 0;i < data.length;i++)
            {

                var shoplist=$($('template').html());

                var newlist=shoplist.find('.a').clone();


                newlist.attr("href",data[i].url);
                newlist.find('.shopn').text(data[i].text);
                newlist.find('.shop-pic img').attr('src',data[i].imgSrc);
                newlist.find('.shop-style span:nth-child(2)').text(data[i].state);
                newlist.find('.shop-userdate span:nth-child(2)').text(data[i].date);
                newlist.find('.shop-userstyle span:nth-child(2)').text(data[i].usestate);
                console.log(newlist[0].innerHTML);
                if(data[i].state_code != 1)
                    newlist.find('.shop-userdate').remove();
                if(data[i].state_code != 2)
                    newlist.find('.shop-userstyle').remove();
                var div=$('<div class="shop-main"></div>');
                div.append(newlist);
                list.append(div);

            }




            brach.append(list);
        }


    }

})