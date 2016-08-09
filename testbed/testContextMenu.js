/* 
 * The makeMenuItems function will use the getMenuType
 * function to determine which menu items it will
 * include in the currently displayed menu.
 */
function makeMenuItems(e) {
  return (
  {
    rcw: {
      name: 'Rotate CW',
    //callback: doit('cw')
    },
    rccw: {
      name: 'Rotate CCW',
    //callback: doit('ccw')
    }
  });
}

/* 
 * The makeMenus function registers a dynamic context menu which
 * will be rebuilt every time the menu is to be shown. It will
 * use the makeMenuItems function to include the correct menu 
 * items in the menu to be displayed for a particular event.
 */
function makeMenus() {
  $.contextMenu({
    selector: '#content', 
    build: function($trigger, e) {
      // this callback is executed 
      // its results are destroyed every time the menu is hidden
      // e is the original contextmenu event, 
      // containing e.pageX and e.pageY (amongst other data)
      var opts = {
        trigger: "right",
        determinePosition: function($menu) {
          // .position() is provided as a jQuery UI utility
          // (...and it won't work on hidden elements)
          $menu.css('display', 'block').position({
            my: "right top",
            at: "left bottom",
            of: this,
            offset: "0 5",
            collision: "fit"
          }).css('display', 'none');
        },
        callback: function(key, options) {
          var m = "clicked on " + key + " on element ";
          m =  m + options.$trigger.attr("id");
          alert(m); 
        },
        zindex: 10,
        reposition: false
      };
      opts.items = makeMenuItems(e);
      return opts;
    }
  });
}



