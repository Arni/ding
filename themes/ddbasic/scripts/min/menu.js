!function(e,$){"use strict";var n=0;$(window).on("scroll.header",function(){if(ddbasic.breakpoint.is("mobile")){var e=$(window).scrollTop();if(Math.abs(n-e)<=100)return;e>n&&e>148?$("header.site-header").addClass("topbar-up"):e+$(window).height()<$(document).height()&&$("header.site-header").removeClass("topbar-up"),n=e}}),$(window).on("dingpopup-close",function(){$("body").removeClass("pane-login-is-open overlay-is-active")}),Drupal.behaviors.menu={attach:function(e,n){var i=$("a.topbar-link-user",e),a=$(".close-user-login",e),o=$("a.topbar-link-menu",e),s=$("a.topbar-link-search",e),l=$("a.search-extended-button",e),t=$(".main-menu-wrapper > .main-menu > .expanded > a",e),r=$(".main-menu-wrapper > .main-menu > .expanded > .main-menu > .expanded > a",e),u=$("body");o.on("click",function(e){e.preventDefault(),u.toggleClass("mobile-menu-is-open"),u.removeClass("mobile-search-is-open pane-login-is-open mobile-usermenu-is-open"),u.toggleClass("overlay-is-active"),u.hasClass("mobile-menu-is-open")?u.addClass("overlay-is-active"):u.removeClass("overlay-is-active")}),s.on("click",function(e){e.preventDefault(),u.toggleClass("mobile-search-is-open"),u.removeClass("mobile-menu-is-open pane-login-is-open mobile-usermenu-is-open"),u.hasClass("mobile-search-is-open")?u.addClass("overlay-is-active"):u.removeClass("overlay-is-active")}),i.on("click",function(e){e.preventDefault(),ddbasic.openLogin()}),a.on("click",function(e){e.preventDefault(),u.removeClass("pane-login-is-open"),u.removeClass("overlay-is-active")}),t.on("click",function(e){$(".is-tablet").is(":visible")&&(e.preventDefault(),t.not($(this)).parent().children(".main-menu").slideUp(200),$(this).parent().children(".main-menu").slideToggle(200))}),r.on("click",function(e){$(".is-tablet").is(":visible")&&(e.preventDefault(),r.not($(this)).removeClass("open"),r.not($(this)).parent().children(".main-menu").slideUp(200),$(this).toggleClass("open"),$(this).parent().children(".main-menu").slideToggle(200))}),l.on("click",function(e){e.preventDefault(),u.toggleClass("extended-search-is-open")}),$(".header-wrapper .navigation-inner > ul.main-menu-third-level").click(function(e){if($(".is-tablet").is(":visible")){var n=$(".header-wrapper .navigation-inner > ul.main-menu-third-level").offset(),i=$(".header-wrapper .navigation-inner > ul.main-menu-third-level > li"),a=0;i.each(function(e){a+=$(this).outerHeight()}),e.offsetY>n.top+a&&(window.location.href="/user/logout")}})}},Drupal.behaviors.second_level_menu={attach:function(e,n){$("ul.main-menu-second-level").flexMenu({linkText:Drupal.t("More")+"...",popupAbsolute:!1,cutoff:1})}}}(0,jQuery);