function ueStackingCard(){
  
    var g_stackingCard, g_stackingCardItems; 
    var g_classConnected;
    var g_isInEditor;

    /*
    * clone sections and append to their parent slides
    */
    function findSections(g_stackingCardItem, sectionToClone){  
      
      //clone section - use detach method on front to make elementor widgets work
      var clonedSectionItem = sectionToClone.detach();
      
      //paste section
      var saItemContentWrapper = g_stackingCardItem.find(".ue_content_wrapper.ue_section_id");

      saItemContentWrapper.html(clonedSectionItem);
      
      //add connected class to stacking card child element
      var g_stackingCardSection = g_stackingCardItem.children();
      
      g_stackingCardSection.addClass(g_classConnected);
      
    }
    
    /*
    * show what sections are connected to stacking card in editor
    */
    function findSectionsInEditor(g_stackingCardItem){     
      
      //cloned section's id
      var g_stackingCardItemId = g_stackingCardItem.data("id");

      var stackingCardItemSource = g_stackingCardItem.data("source");

      var saItemContentWrapper = g_stackingCardItem.find(".ue_content_wrapper.ue_section_id");

      if(stackingCardItemSource !== "section_id")
      return(false);
      
      g_stackingCardItem.addClass('uc-item-message');

      //stacking card item message in editor
      var g_stackingCardItemHTML = "<div class='uc-message'>Section with id <b>'" + g_stackingCardItemId + "'</b> connected to stacking card</div>";
      
      //paste section
      saItemContentWrapper.html(g_stackingCardItemHTML);
      
    }
    
    /*
    * handle section find error 
    */
    function showHideErrors(g_stackingCardItem, stackingCardItemId, sectionToClone) {
      
      var showErrors = g_stackingCard.data("errors");
      var stackingCardItemSource = g_stackingCardItem.data("source");
      var saItemContentWrapper = g_stackingCardItem.find(".ue_content_wrapper.ue_section_id");
      
      if (!g_isInEditor) {
        if (showErrors === false) {
            return(false);
        }
      }
      
      if(stackingCardItemSource !== "section_id")
      return(false);

      //check if section with id from stacking card item exist
      if(sectionToClone.length > 0)
      return(false);
      
      //if section with id from stacking card item does not exist, then add error clas to stacking card item
      g_stackingCardItem.addClass('uc-item-error');
      
      //add error html element
      saItemContentWrapper.html("<div class='uc-section-error'><div class='uc-error'>Couldn't find a section with id: '" + stackingCardItemId + "'</div></div>");
      
    }
    
    /*
    * show | hide section in editor
    */ 
    function hideSectionInEditor(sectionToClone){
      
      if(g_isInEditor == false)
      return(false);
    
      //affect only section that are not in connected to stacking card
      if(sectionToClone.hasClass(g_classConnected) == true)
      return(false);
      
      var dataShowInEditor = g_stackingCard.data('show-section');
      
      if(dataShowInEditor == 'no')
      sectionToClone.css('display', '');
      
      if(dataShowInEditor == 'hide')
      sectionToClone.hide();     
    }
    
    /*
    * define item type "section" behaviour on live page
    */
    function handleItemTypeSection(){
      
      g_stackingCardItems.each(function(){
        
        var g_stackingCardItem = jQuery(this); 

        var stackingCardItemId = g_stackingCardItem.data("id");

        if(!stackingCardItemId)
        return;
        
        //find sections to clone on the page
        var sectionToClone = jQuery("#" + stackingCardItemId);
        
        //clone and paste section from elementor layout
        findSections(g_stackingCardItem, sectionToClone);  

        var stackingCardItemSource = g_stackingCardItem.data("source");
        if(stackingCardItemSource !== "section_id")
        return;
        
        //see if stacking card item id has its element on the page
        showHideErrors(g_stackingCardItem, stackingCardItemId, sectionToClone);
        
      }); 
      
    }
    
    
    /*
    * define item type "section" behaviour in editor
    */
    function handleItemTypeSectionInEditor(){
      
      g_stackingCardItems.each(function(){
        
        var g_stackingCardItem = jQuery(this); 

        var stackingCardItemId = g_stackingCardItem.data("id");
        
        //find sections to clone on the page
        var sectionToClone = jQuery("#" + stackingCardItemId);
        
        //tell to user which section is connected
        findSectionsInEditor(g_stackingCardItem);
        
        //see if stacking card item id has its element on the page
        showHideErrors(g_stackingCardItem, stackingCardItemId, sectionToClone);
        
        //show | hide sections if needed
        hideSectionInEditor(sectionToClone);
        
      }); 
      
    }
    
    
    /*
    * init stacking card
    */
    this.init = function(objStackingCard){
      
      //init globals
      g_stackingCard = jQuery(objStackingCard);
      g_stackingCardItems = g_stackingCard.find('.ue_stacking_card_item');
      
      g_classConnected = 'uc-connected';
      g_activeSliderClass = '.uc-active-item';
      
      g_isInEditor = g_stackingCard.data("editor");
      
      //do not run stacking card in editor
      if(g_isInEditor == "no")
      handleItemTypeSection();    
      
      //in editor run a different function that do not manipulate DOM
      if(g_isInEditor == "yes")
      handleItemTypeSectionInEditor();
    }
  }