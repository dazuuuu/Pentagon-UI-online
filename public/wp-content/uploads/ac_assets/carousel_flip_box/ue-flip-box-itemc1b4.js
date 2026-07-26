var UEFlipboxItem = function(objFlip){
  
  //classes
  var g_showClass, g_hideClass, g_ueFlipboxPanelFront, g_ueFlipboxPanelBack;
  
  //objects
  var g_objFlipBox, g_objTriggerFront, g_objTriggerBack, g_objBack, g_objFront;
  
  //helpers
  var g_isWasHover = false, g_isHoverMode, g_backEventType;  
  
  /**
  * turn actually the box
  */
  function turnOverActually(isBack){
    
    if(!isBack)
      var isBack = g_objFlipBox.hasClass(g_showClass);
    
    flipbox = g_objFlipBox[0];
    
    //turn to front
    if( isBack == true) {
      flipbox.classList.remove(g_showClass);
      flipbox.classList.add(g_hideClass);
      
      g_objFront.removeAttr('inert');      
      g_objBack.attr('inert', '');
      
      return(true);
    } 
    
    g_objFront.attr('inert', '');
    g_objBack.removeAttr('inert'); 
    
    //turn to back
    flipbox.classList.remove(g_hideClass);
    flipbox.classList.add(g_showClass);
    
  }
  
  /**
  * show the back panel
  */
  function turnOverFlipBox(event){
    
    var objClicked = jQuery(event.target);
    
    var isHoverEvent = event.type != "click";
    
    //for mobile mode
    if(isHoverEvent == true)
      g_isWasHover = true;
    
    var isFrontClicked = isHoverEvent == false && objClicked.hasClass(g_ueFlipboxPanelFront);
    
    //don't allow to click on front when trigger button exists
    if(isFrontClicked == true && g_objTriggerFront.length == true)
      return(true);
    
    var flipbox = g_objFlipBox[0];
    
    var isBack = flipbox.classList.contains(g_showClass);
    
    //don't allow clicks on desktop
    if(g_isHoverMode == true){
      
      if(isHoverEvent == false && g_isWasHover == true)
        return(true);
      
    }else{
      //click only mode - don't allow hover
      
      if(isHoverEvent == true){
        
        //if no trigger button on back - turn by mouse out
        if(isBack == true && g_backEventType == "hover" && event.type == "mouseleave"){
          turnOverActually(isBack);
        }
        
        return(true);		  
      }else{		
        
        //don't allow hover on click on trigger mode, on desktop only. on mobile allowed click
        
        if(isBack == true && g_backEventType == "hover" && g_isWasHover == false)
          return(true);
      }
      
    }
    
    
    //don't turn back on mouseleave
    if(isBack == false && event.type == "mouseleave")
      return(true);
    
    turnOverActually(isBack);
    
  }

  /**
   * handle keyboard events
   */
  function onKeyDown(event){
    var key = event.key || event.keyCode;
    
    if(key == 'Enter' || key == 13 || key == ' ' || key == 32){
      event.preventDefault();

      var isBack = g_objFlipBox[0].classList.contains(g_showClass); 
      turnOverActually(isBack);
    }
  }
  
  /**
  * init the events
  */
  function initFlipBox(objFlipboxWrapper){
    //classes
    g_showClass = "uc-show";
    g_hideClass = "uc-hide";
    g_ueFlipboxPanelFront = "ue-flip-box__panel--front";
    g_ueFlipboxPanelBack = "ue-flip-box__panel--back";
    
    if(objFlipboxWrapper.length == 0){
      console.log("flipbox not found: ");
      return(false);
    }
    
    //objects
    g_objFlipBox = objFlipboxWrapper.find(".ue-flip-box__container");    
    g_isHoverMode = g_objFlipBox.hasClass("ue-flip-box__container--hover");
    g_objFront = g_objFlipBox.find("."+g_ueFlipboxPanelFront);
    g_objBack = g_objFlipBox.find("."+g_ueFlipboxPanelBack);
    
    g_backEventType = "hover";
    
    if(g_isHoverMode == false)
      g_backEventType = "click";
    
    g_objTriggerFront = g_objFlipBox.find(".ue-flip-box__front-trigger");
    g_objTriggerBack = g_objFlipBox.find(".ue-flip-box__back-trigger");
    
    if(g_objTriggerBack.length == false)
      g_backEventType = "hover";
    
    g_objFlipBox.on("keydown", onKeyDown);
    
    if(g_objTriggerFront.length && g_objTriggerBack.length){
      g_objTriggerBack.on("click", turnOverFlipBox);
      g_objTriggerFront.on("click", turnOverFlipBox);
    }else{
      g_objFlipBox.on("click mouseenter mouseleave", turnOverFlipBox);
    }    
  }
  
  initFlipBox(objFlip);
  
  // turn back visibility of back panel
  setTimeout( function() { jQuery('.'+g_ueFlipboxPanelBack).css('visibility', 'visible'); },1000);
}
