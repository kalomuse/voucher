/**
 * Created by ThingLin on 2016/12/15.
 */

/**
 * web移动端滑动，实现下拉刷新与上拉加载更多
 * @type {{transitionTime: string, scroll: Function, xTrans: {translateX: Function, translateY: Function, removeTransition: Function, addTransition: Function}, onTip: Function}}
 */
window.xScroll = {

    "transitionTime": ".3",
    /**
     *
     * @param dom 执行侧滑的dom元素对象，额外添加属性 xScrollCoord = {"moveX": 0, "moveY": 0}; 记录当前滑动到的位置
     *              dom == jQuery对象时转为dom元素对象
     *              通过 dom.xInitParameters对象保存所有初始参数
     *              通过 dom.xScrollCoord 对象保存移动坐标参数
     * @param maxTranslate  滑动.++ 上限
     * @param minTranslate  滑动下限
     * @param isVertical  true上下滑动，false || undefined 左右滑动
     *            左右滑动例  ：  xScroll.scroll(dom,0,-300);
     *@param touchendCallBackFun call(this,e,orientation) -->> this触发事件dom元素对象，e触发事件对象，
     *          orientation滑动方向(如垂直（isVertical = true）则可通过该参数得到是下拉刷新还是上拉加载更多)
     *          isVertical ? orientation ？下拉至顶 ：上拉至底 : orientation ? 左滑到底 : 右滑到底
     *          orientation == undefined -->> 未滑至上限或者下限
     *
     *
     * moveY  moveX x或y轴移动距离
     * this.xInitParameters.xisMove TRUE为移动  FALSE为点击
     * this.xInitParameters.xminTranslate 滑动的下限 最大滑动距离
     *
     *
     *如果代码中设定了一个 setTimeout，那么浏览器便会在合适的时间，将代码插入任务队列，
     *如果这个时间设为 0，就代表立即插入队列，但不是立即执行，仍然要等待前面代码执行完毕。
     *所以 setTimeout 并不能保证执行的时间，是否及时执行取决于 JavaScript 线程是拥挤还是空闲。
     * 
     */
    scroll: function (dom, maxTranslate, minTranslate, isVertical,touchendCallBackFun) {

        if(!maxTranslate)
            maxTranslate = 0;

        if(!minTranslate)
            throw "minTranslate : "+minTranslate;

        if("function" == typeof isVertical){
            touchendCallBackFun = isVertical;
            isVertical = false;
        }

        if (dom.jquery) {
            dom = dom[0];
        }

        dom.xInitParameters = {"xstart":0,"xdifference":0,"xisMove":false,"xmaxTranslate":maxTranslate,"xminTranslate":minTranslate
            ,"xisVertical" : isVertical,"xtouchendCallBackFun":touchendCallBackFun};
        dom.xScrollCoord = {"moveX": 0, "moveY": 0};

       
        dom.addEventListener("touchstart", function (e) {
            if (this.xInitParameters.xisVertical)
                this.xInitParameters.xstart = e.touches[0].clientY;
            else
                this.xInitParameters.xstart = e.touches[0].clientX;
        });

        var oldLi = $('.category-left li:first-child');
        dom.addEventListener("touchend", function (e) {
            // 不是滑动的时候，并且点击的是左侧列表执行以下代码
           if(!this.xInitParameters.xisMove  && $(e.target).is('.category-left li')){
                oldLi.css('color','black');
                $(e.target).css('color','red');
                oldLi = $(e.target);
                
                //点击列表项滑动到顶部
                this.xScrollCoord.moveY = $(e.target).index() * $(e.target).outerHeight(true) * -1;
                // 滑动距离已经大于最大滑动距离 那么就把距离设置成最大滑动距离
                if(this.xScrollCoord.moveY < this.xInitParameters.xminTranslate){
                    this.xScrollCoord.moveY = this.xInitParameters.xminTranslate;
                }
                xScroll.xTrans.addTransition(this, parseFloat(xScroll.transitionTime) + "s");
                xScroll.xTrans.translateY(this, this.xScrollCoord.moveY);
                addDate(e.target.value);
            }


            if (!this.xInitParameters.xisMove) {
                return;
            }
            var orientation = undefined;

            if (this.xInitParameters.xisVertical) {
                if ((this.xScrollCoord.moveY + this.xInitParameters.xdifference) > this.xInitParameters.xmaxTranslate) {
                    this.xScrollCoord.moveY = this.xInitParameters.xmaxTranslate;
                    orientation = true;
                } else if ((this.xScrollCoord.moveY + this.xInitParameters.xdifference) < this.xInitParameters.xminTranslate) {
                    this.xScrollCoord.moveY = this.xInitParameters.xminTranslate;
                    orientation = false;
                } else {
                    this.xScrollCoord.moveY += this.xInitParameters.xdifference;
                }
                xScroll.xTrans.translateY(this, this.xScrollCoord.moveY);
            } else {
                if ((this.xScrollCoord.moveX + this.xInitParameters.xdifference) > this.xInitParameters.xmaxTranslate) {
                    this.xScrollCoord.moveX = this.xInitParameters.xmaxTranslate;
                    orientation = false;
                } else if ((this.xScrollCoord.moveX + this.xInitParameters.xdifference) < this.xInitParameters.xminTranslate) {
                    this.xScrollCoord.moveX = this.xInitParameters.xminTranslate;
                    orientation = true;
                } else {
                    this.xScrollCoord.moveX += this.xInitParameters.xdifference;
                }
                xScroll.xTrans.translateX(this, this.xScrollCoord.moveX);
            }
            xScroll.xTrans.addTransition(this, parseFloat(xScroll.transitionTime) + "s");
            this.xInitParameters.xisMove = false;

            if(this.xInitParameters.xtouchendCallBackFun && "function" == typeof this.xInitParameters.xtouchendCallBackFun)
                this.xInitParameters.xtouchendCallBackFun.call(this,e,orientation);
        });

        dom.addEventListener("touchmove", function (e) {
            this.xInitParameters.xisMove = true;
            xScroll.xTrans.removeTransition(this);
            if (this.xInitParameters.xisVertical) {
                var clientY = e.touches[0].clientY;
                this.xInitParameters.xdifference = clientY - this.xInitParameters.xstart;
                xScroll.xTrans.translateY(this, this.xScrollCoord.moveY + this.xInitParameters.xdifference);
            } else {
                var clientX = e.touches[0].clientX;
                this.xInitParameters.xdifference = clientX - this.xInitParameters.xstart;
                xScroll.xTrans.translateX(this, this.xScrollCoord.moveX + this.xInitParameters.xdifference);
            }
        });
    },
    xTrans: {
        translateX: function (dom, x) {
            dom.style["transform"] = "translateX(" + x + "px)";
            dom.style["webkitTransform"] = "translateX(" + x + "px)";
        },

        translateY: function (dom, y) {
            dom.style["transform"] = "translateY(" + y + "px)";
            dom.style["webkitTransform"] = "translateY(" + y + "px)";
        },

        removeTransition: function (dom) {
            dom.style.transition = "none";
            dom.style.webkitTransition = "none";
        },

        addTransition: function (dom, timer) {
            dom.style.transition = timer;
            dom.style.webkitTransition = timer;
        },
    },

    /**
     * 快速点击
     * @param dom
     * @param clallBackFun
     */
    onTip: function (dom, clallBackFun) {
        if (dom && typeof dom == "object") {
            dom.xonTipParameters = {"isMove":false,"timer":0,"clallBackFun":clallBackFun};
            dom.addEventListener("touchstart", function (e) {
                this.xonTipParameters.timer = Date.now();
                this.xonTipParameters.isMove = false;
            });

            dom.addEventListener("touchmove", function (e) {
                this.xonTipParameters.isMove = true;
            });

            dom.addEventListener("touchend", function (e) {
                if (!this.xonTipParameters.isMove && Date.now() - this.xonTipParameters.timer < 80 && this.xonTipParameters.clallBackFun && "function" == typeof this.xonTipParameters.clallBackFun) {
                    this.xonTipParameters.clallBackFun.call(this, e);
                }
            });
        }
    }

}

