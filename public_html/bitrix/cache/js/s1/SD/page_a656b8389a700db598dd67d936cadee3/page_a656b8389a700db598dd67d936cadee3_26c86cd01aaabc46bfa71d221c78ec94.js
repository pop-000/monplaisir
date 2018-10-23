
; /* Start:/bitrix/components/bitrix/catalog.brandblock/templates/.default/script.js*/
;(function(window) {
	if (window.JCIblockBrands)
		return;

	JCIblockBrands = function (params)
	{
		this.brandBlockObj = null;
		this.brandBlockOverObj = null;
		this.timeoutID = null;

		this.DELAY_BEFROE_HIDE_POPUP = 350; //ms
	};

	JCIblockBrands.prototype.itemOver = function(obj)
	{
		this.brandBlockOverObj = obj;

		if(this.brandBlockObj == obj && this.timeoutID)
		{
			clearTimeout(this.timeoutID);
			this.timeoutID = null;
		}
		else if(this.brandBlockObj != obj && this.timeoutID)
			return;

		if(!BX.hasClass(obj, "hover"))
		{
			this.brandBlockObj = obj;
			BX.addClass(obj, "hover");
			this.correctPopup(obj);
		}
	};

	JCIblockBrands.prototype.itemOut = function(obj)
	{
		this.brandBlockOverObj = null;

		if(this.brandBlockObj && this.brandBlockObj != obj)
			return;

		if(!BX.hasClass(obj, "hover"))
			return;

		var _this = this;

		this.timeoutID = setTimeout(function() {

			BX.removeClass(obj, "hover");
			_this.timeoutID = null;
			_this.brandBlockObj = null;

			if (_this.brandBlockOverObj)
				_this.itemOver(_this.brandBlockOverObj);

			}, this.DELAY_BEFROE_HIDE_POPUP);
	};

	JCIblockBrands.prototype.getItemPopup = function(obj)
	{
		return BX.findChild(obj, {'className': 'bx_popup'}, true);
	};

	JCIblockBrands.prototype.correctPopup = function(obj)
	{
		var popupObj = this.getItemPopup(obj);

		if(popupObj)
		{
			var popupParams = BX.pos(popupObj);

			if(popupParams.height > 40)
				popupObj.style.top = "-1px";
			else
			{
				popupObj.style.top = "50%";
				popupObj.style.marginTop = "-"+popupParams.height/2+"px";
			}
		}
	};

})(window);
/* End */
;
; /* Start:/bitrix/components/bitrix/catalog.comments/templates/.default/script.js*/
;(function(window) {
	if (window.JCCatalogSocnetsComments)
		return;

	JCCatalogSocnetsComments = {

		lastWidth: null,
		ajaxUrl: null,

		setFBWidth: function(width)
		{
			if(JCCatalogSocnetsComments.lastWidth == width)
				return;

			JCCatalogSocnetsComments.lastWidth = width;

			var fbDiv = BX("bx-cat-soc-comments-fb");

			if(fbDiv)
			{
				if(fbDiv.childNodes[0])
					fbDiv = fbDiv.childNodes[0];

				if(fbDiv && fbDiv.childNodes[0] && fbDiv.childNodes[0].childNodes[0])
				{
					var fbIframe = fbDiv.childNodes[0].childNodes[0];

					if(fbIframe)
					{
						var src = fbIframe.getAttribute("src");
						var newSrc = src.replace(/width=(\d+)/ig, "width="+width);

						fbDiv.setAttribute("data-width", width+"px");
						fbDiv.childNodes[0].style.width = width+"px";
						fbIframe.style.width = width+"px";

						fbIframe.setAttribute("src", newSrc);
					}
				}
			}
		},

		onFBResize: function(event)
		{
			var width = JCCatalogSocnetsComments.getWidth();

			if(width > 20)
				JCCatalogSocnetsComments.setFBWidth(width-20);
		},

		getWidth: function()
		{
			var result = 0,
				obj = BX("soc_comments_div");

			if(obj && obj.parentNode && obj.parentNode.parentNode)
			{
				var pos = BX.pos(obj.parentNode.parentNode);
				result = pos.width;
			}

			return result;
		},

		getBlogAjaxHtml: function()
		{
			var postData = {
					sessid: BX.bitrix_sessid()
				};

			BX.ajax({
				timeout:   30,
				method:   'POST',
				dataType: 'html',
				url:       JCCatalogSocnetsComments.ajaxUrl,
				data:      postData,
				onsuccess: function(result)
				{
					if(result)
					{
						JCCatalogSocnetsComments.insertBlogHtml(result);
						JCCatalogSocnetsComments.hacksForCommentsWindow(false);
					}
				}
			});
		},

		insertBlogHtml: function(html)
		{
			JCCatalogSocnetsComments.blogContainerObj = BX("bx-cat-soc-comments-blg");

			if(JCCatalogSocnetsComments.blogContainerObj)
				JCCatalogSocnetsComments.blogContainerObj.innerHTML = html;
		},

        hacksForCommentsWindow: function(addTitleShow)
        {
            window["iblockCatalogCommentsHIntervalId"] = setInterval( function(){
                if(typeof  showComment == 'function')
                {
                    if(!addTitleShow)
                        showComment("0");

                    var addCommentButtons = BX.findChildren(document,
                        { class: "blog-add-comment" }
                        , true
                    );

                    if(addCommentButtons[0])
                        for (var i = addCommentButtons.length-1; i >= 0 ; i--)
                            addCommentButtons[i].style.display = addTitleShow ? "" : "none";

                    clearInterval(window["iblockCatalogCommentsHIntervalId"]);
                }

            },
            200
            );
        }
    };
})(window);

/* End */
;
; /* Start:/bitrix/components/bitrix/catalog.tabs/templates/.default/script.js*/
JCCatalogTabs = function (params)
{
	this.activeTabId = params.activeTabId;
	this.tabsContId = params.tabsContId;
};

JCCatalogTabs.prototype.onTabClick = function(tabObj)
{
	if(!tabObj || !tabObj.id || this.activeTabId == tabObj.id)
		return;

	this.setTabActive(tabObj);
};

JCCatalogTabs.prototype.setTabActive = function(tabObj)
{
	if(!tabObj || !tabObj.id)
		return;

	var newActiveContent = BX(tabObj.id+"_cont");

	if(newActiveContent)
	{
		BX.addClass(tabObj, "active");
		BX.removeClass(newActiveContent, "tab-off");
		BX.removeClass(newActiveContent, "hidden");

		if(this.activeTabId != tabObj.id)
		{
			var oldActiveTab = BX(this.activeTabId);
			var oldActiveContent = BX(this.activeTabId+"_cont");

			if(oldActiveTab && oldActiveContent)
			{
				BX.removeClass(oldActiveTab, "active");
				BX.addClass(oldActiveContent, "tab-off");
				setTimeout(function() { BX.addClass(oldActiveContent, "hidden"); }, 700);

				this.activeTabId = tabObj.id;
				var tabId = tabObj.id.replace(this.tabsContId, "");
				BX.onCustomEvent('onAfterBXCatTabsSetActive_'+this.tabsContId,[{activeTab: tabId}]);
			}
		}
	}
};
/* End */
;; /* /bitrix/components/bitrix/catalog.brandblock/templates/.default/script.js*/
; /* /bitrix/components/bitrix/catalog.comments/templates/.default/script.js*/
; /* /bitrix/components/bitrix/catalog.tabs/templates/.default/script.js*/
