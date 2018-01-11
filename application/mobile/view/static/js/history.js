$(function(){
	//历史优惠数据加载
	$.ajax({
		url:'?type=buy',
		type:'post',
		dataType:'json',
		success:function(data){
			var brach=$('.yh');
			brach.html("");
		
			var data=data.content[0].list;
			console.log(data);
			if(data.length==0){
				var list=$('<div class="isnull"></div>');
				
				var span1=$('<span>历史优惠还是空的</span>');
				list.append(span1);
				brach.append(list);

			}else{
				
				
				var list=$('<div class="yhlist"></div>');
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
					var div=$('<div class="shop-main"></div>');
					div.append(newlist); 
					list.append(div);
				
				}
				
				
				
				
				brach.append(list);
			}

			
		
			
		},
		error:function(data){
			console.log(data);
		}
	})

	function addyhdata() {
		$.ajax({
		url:'?type=buy',
		type:'post',
		dataType:'json',
		success:function(data){

			var brach=$('.yh');
			brach.html("");
		
			var data=data.content[0].list;
			console.log(data);
			if(data.length==0){
				var list=$('<div class="isnull"></div>');		
				var span1=$('<span>历史优惠还是空的</span>');
				list.append(span1);
				brach.append(list);

			}else{
				var list=$('<div class="yhlist"></div>');
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
					var div=$('<div class="shop-main"></div>');
					div.append(newlist);
					list.append(div);

				}
				brach.append(list);
			}

			
		
			
		},
		error:function(data){
			console.log(data);
		}
	})
	}
	function addspdata(){
		$.ajax({
		url:'?type=choujiang',
		type:'post',
		dataType:'json',
		success:function(data){

			
			var brach=$('.sp');
			brach.html("");
		
			var data=data.content[0].list;
			console.log(data);
			if(data.length==0){
				var list=$('<div class="isnull"></div>');
				
				var span1=$('<span>历史商品还是空的</span>');
				list.append(span1);
				brach.append(list);

			}else{
				
				
				var list=$('<div class="splist"></div>');
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
					var div=$('<div class="shop-main"></div>');
					div.append(newlist); 
					list.append(div);
				
				}
				
				
				
				
				brach.append(list);
			}

			
		
			
		},
		error:function(data){
			console.log(data);
		}
	})
	}

    //tab栏切换
    $('.h-right .listslide').on('touchend click',function(e){
        e.preventDefault();
        $(this).addClass('actived').siblings().removeClass('actived');
        console.log(e.target);
        switch (e.target.innerHTML) {
            case '优惠':
                addspdata();
                break;
            case '商品':
                addyhdata();

                break;

            default:
                // statements_def
                break;
        }
        $('main .con').eq($(this).index()).fadeIn().siblings().hide();

    })

})