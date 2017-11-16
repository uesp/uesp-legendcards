var LegendsCardPopup = null;
var LegendsCardPopup_Image = null;
var LegendsCardPopup_LastElement = null;
var LegendsCardPopup_Visible = false;
//var EsoItemLinkPopup_CacheId = "";
//var EsoItemLinkPopup_Cache = { };


function CreateLegendsCardPopup()
{
	LegendsCardPopup = $('<div />').addClass('eslegCardPopup').hide();
	LegendsCardPopup_Image = $('<img />').on("load", OnLegendsCardImageLoad).on("error", OnLegendsCardImageError);
	LegendsCardPopup.append(LegendsCardPopup_Image);
	
	$('body').append(LegendsCardPopup);
}


function OnLegendsCardImageLoad(e)
{
	LegendsCardPopup.show();
	AdjustLegendsCardPopupPosition(LegendsCardPopup, LegendsCardPopup_LastElement);
}


function OnLegendsCardImageError(e)
{
	LegendsCardPopup.hide();
}


function ShowLegendsCardPopup(parent, cardName)
{
	if (LegendsCardPopup == null) CreateLegendsCardPopup();
	
	LegendsCardPopup_LastElement = parent;
	
	//var imageSrc = "//legends.uesp.net/cardimage/" + encodeURIComponent(cardName) + ".png";
	var imageSrc = "//content3.uesp.net/w/extensions/UespLegendsCards/cardimages/" + cardName + ".png";
	
	LegendsCardPopup_Image.attr("src", imageSrc);
}


function AdjustLegendsCardPopupPosition(tooltip, parent)
{
     var windowWidth = $(window).width();
     var windowHeight = $(window).height();
     var toolTipWidth = tooltip.width();
     var toolTipHeight = tooltip.height();
     var elementHeight = parent.height();
     var elementWidth = parent.width();
     
     var top = parent.offset().top - 150;
     var left = parent.offset().left + parent.outerWidth() + 3;
     
     tooltip.offset({ top: top, left: left });
     
     var viewportTooltip = tooltip[0].getBoundingClientRect();
     
     if (viewportTooltip.bottom > windowHeight) 
     {
    	 var deltaHeight = viewportTooltip.bottom - windowHeight + 10;
         top = top - deltaHeight
     }
     else if (viewportTooltip.top < 0)
     {
    	 var deltaHeight = viewportTooltip.top - 10;
         top = top - deltaHeight
     }
         
     if (viewportTooltip.right > windowWidth) 
     {
         left = left - toolTipWidth - parent.width() - 28;
     }
     
     tooltip.offset({ top: top, left: left });
     viewportTooltip = tooltip[0].getBoundingClientRect();
     
     if (viewportTooltip.left < 0 )
     {
    	 var el = $('<i/>').css('display','inline').insertBefore(parent[0]);
         var realOffset = el.offset();
         el.remove();
         
         left = realOffset.left - toolTipWidth - 3;
         tooltip.offset({ top: top, left: left });
     }
     
}


function HideLegendsCardPopup()
{
	LegendsCardPopup_Visible = false;
	if (LegendsCardPopup == null) return;
	LegendsCardPopup.hide();
}


function OnLegendsCardLinkEnter()
{
	var $this = $(this);
	LegendsCardPopup_LastElement = $this;
	
	ShowLegendsCardPopup(LegendsCardPopup_LastElement, $this.attr('card'));
}


function OnLegendsCardLinkLeave()
{
	LegendsCardPopup_LastElement = null;
	HideLegendsCardPopup();
}


$( document ).ready(function() {
	$('.legendsCardLink').hover(OnLegendsCardLinkEnter, OnLegendsCardLinkLeave);
});