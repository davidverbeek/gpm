(function(){var t,i;t=this.jQuery||window.jQuery,i=t(window),t.fn.stick_in_parent=function(o){var s,e,n,r,c,l,a,p,u,d,f,h;for(null==o&&(o={}),h=o.sticky_class,l=o.inner_scrolling,f=o.recalc_every,d=o.parent,u=o.offset_top,p=o.spacer,n=o.bottoming,null==u&&(u=0),null==d&&(d=void 0),null==l&&(l=!0),null==h&&(h="is_stuck"),s=t(document),null==n&&(n=!0),r=function(o,e,r,c,a,g,k,m){var v,y,_,b,w,x,C,I,z,A,j,M;if(!o.data("sticky_kit")){if(o.data("sticky_kit",!0),w=s.height(),C=o.parent(),null!=d&&(C=C.closest(d)),!C.length)throw"failed to find stick parent";if(_=!1,v=!1,j=null!=p?p&&o.closest(p):t("<div />"),j&&j.css("position",o.css("position")),I=function(){var t,i,n;if(!m)return w=s.height(),t=parseInt(C.css("border-top-width"),10),i=parseInt(C.css("padding-top"),10),e=parseInt(C.css("padding-bottom"),10),r=C.offset().top+t+i,c=C.height(),_&&(_=!1,v=!1,null==p&&(o.insertAfter(j),j.detach()),o.css({position:"",top:"",width:"",bottom:""}).removeClass(h),n=!0),a=o.offset().top-(parseInt(o.css("margin-top"),10)||0)-u,g=o.outerHeight(!0),k=o.css("float"),j&&j.css({width:o.outerWidth(!0),height:g,display:o.css("display"),"vertical-align":o.css("vertical-align"),"float":k}),n?M():void 0},I(),g!==c)return b=void 0,x=u,A=f,M=function(){var t,d,y,z,M,Q;if(!m)return y=!1,null!=A&&(A-=1,0>=A&&(A=f,I(),y=!0)),y||s.height()===w||(I(),y=!0),z=i.scrollTop(),null!=b&&(d=z-b),b=z,_?(n&&(M=z+g+x>c+r,v&&!M&&(v=!1,o.css({position:"fixed",bottom:"",top:x}).trigger("sticky_kit:unbottom"))),a>z&&(_=!1,x=u,null==p&&(("left"===k||"right"===k)&&o.insertAfter(j),j.detach()),t={position:"",width:"",top:""},o.css(t).removeClass(h).trigger("sticky_kit:unstick")),l&&(Q=i.height(),g+u>Q&&(v||(x-=d,x=Math.max(Q-g,x),x=Math.min(u,x),_&&o.css({top:x+"px"}))))):z>a&&(_=!0,t={position:"fixed",top:x},t.width="border-box"===o.css("box-sizing")?o.outerWidth()+"px":o.width()+"px",o.css(t).addClass(h),null==p&&(o.after(j),("left"===k||"right"===k)&&j.append(o)),o.trigger("sticky_kit:stick")),_&&n&&(null==M&&(M=z+g+x>c+r),!v&&M)?(v=!0,"static"===C.css("position")&&C.css({position:"relative"}),o.css({position:"absolute",bottom:e,top:"auto"}).trigger("sticky_kit:bottom")):void 0},z=function(){return I(),M()},y=function(){return m=!0,i.off("touchmove",M),i.off("scroll",M),i.off("resize",z),t(document.body).off("sticky_kit:recalc",z),o.off("sticky_kit:detach",y),o.removeData("sticky_kit"),o.css({position:"",bottom:"",top:"",width:""}),C.position("position",""),_?(null==p&&(("left"===k||"right"===k)&&o.insertAfter(j),j.remove()),o.removeClass(h)):void 0},i.on("touchmove",M),i.on("scroll",M),i.on("resize",z),t(document.body).on("sticky_kit:recalc",z),o.on("sticky_kit:detach",y),setTimeout(M,0)}},c=0,a=this.length;a>c;c++)e=this[c],r(t(e));return this}}).call(this);