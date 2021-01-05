/**
 * Project: Blog Management System With Sevida-Like UI
 * Developed By: Ahmad Tukur Jikamshi
 *
 * @facebook: amaedyteeskid
 * @twitter: amaedyteeskid
 * @instagram: amaedyteeskid
 * @whatsapp: +2348145737179
 */
(function(window){
	var openPopup = function(button){
		var mNavUL = document.getElementById(button.dataset.target);
		mNavUL.classList.add("open");
		button.classList.add("active");
		button.ariaExpanded = "true";
	};
	var closePopup = function(button){
		var mNavUL = document.getElementById(button.dataset.target);
		mNavUL.classList.remove("open");
		button.classList.remove("active");
		button.ariaExpanded = "false";
	};
	window.MobPopup = function(){
		this.onclick = function(event){
			event.preventDefault();
			var isOpen = !(this.classList.contains("active"));
			document.querySelectorAll("a.nav-btn[data-target]").forEach(closePopup);
			if(isOpen)
				openPopup(this);
		};
	};
	window.DropDown = function (){
		 this.querySelector("span.fas").onclick = function(event){
			event.preventDefault();
			var isOpen = this.parentNode.classList.contains("open");
			if(isOpen) {
				this.parentNode.classList.remove("open");
				this.parentNode.ariaExpanded = false;
			} else {
				this.parentNode.classList.add("open");
				this.parentNode.ariaExpanded = true;
			}
			delete isOpen;
		};
	};
	window.FlexiMenu = function(){
		var element = this,
			isFiring = false,
			_windowX = 0;
		var onWidthChange = function(event) {
			if(isFiring === true && _windowX != window.innerWidth && (isFiring = true))
				return false;
			_windowX = window.innerWidth;
			var moreUl = document.getElementById("moreUl"),
				numChild = moreUl.childElementCount - 1;
			moreUl.parentElement.style.display = "block";
			element.style.pointerEvents = "none";
			for(var index=numChild; index>0; index--){
				var node = moreUl.childNodes[index];
				element.childNodes[node.dataset.index].replaceWith(node);
			}
			numChild = element.childElementCount - 1;
			for(var index=numChild; index>=0; index--){
				if(element.scrollHeight <= 50)
					break;
				var child = element.childNodes[index];
				if(!child || child.classList.contains("popup"))
					continue;
				child.dataset.index = index;
				child.replaceWith(document.createElement("li"));
				moreUl.appendChild(child);
			}
			element.style.pointerEvents = "all";
			if(moreUl.childElementCount == 0)
				moreUl.parentElement.style.display = "none";
			isFiring = false;
			delete moreUl, node, child, index;
		};
		(window.onresize = onWidthChange)(this);
	};
	window.FeedButton = function(){
		this.onclick = function(){
			if(this.classList.contains("active"))
				return false;
			var feedList = document.getElementById("mainFeed");
			if(!feedList)
				return false;
			if(this.dataset.view == "list"){
				feedList.classList.add("feed-list");
				feedList.classList.remove("feed-grid");
			}else{
				feedList.classList.add("feed-grid");
				feedList.classList.remove("feed-list");
			}
			this.classList.add("active");
			(this.previousElementSibling||this.nextElementSibling).classList.remove("active");
			delete feedList;
		};
	};
	window.TabWidget = function(){
		this.onclick = function(event){
			event.preventDefault();
			if(this.classList.contains("active"))
				return false;
			event.target.closest("ul").querySelector("a.active").classList.remove("active");
			this.classList.add("active");
		};
	};
})(window);