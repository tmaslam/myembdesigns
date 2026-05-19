(function() {
    'use strict';
    
    function fixLogoSlider() {
        var lists = document.querySelectorAll('.amazingcarousel-list');
        lists.forEach(function(list) {
            list.style.marginLeft = '0';
            list.style.display = 'flex';
            list.style.flexWrap = 'wrap';
            list.style.justifyContent = 'center';
            list.style.width = '100%';
            
            var items = list.querySelectorAll('.amazingcarousel-item');
            items.forEach(function(item) {
                item.style.float = 'none';
                item.style.flex = '0 0 auto';
                item.style.margin = '10px';
            });
        });
        
        document.querySelectorAll('.amazingcarousel-list-wrapper').forEach(function(w) {
            w.style.overflow = 'visible';
            w.style.width = '100%';
        });
        
        document.querySelectorAll('.amazingcarousel-list-container').forEach(function(c) {
            c.style.overflow = 'visible';
            c.style.width = '100%';
        });
    }
    
    function run() {
        fixLogoSlider();
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
    
    // Also run after window load as fallback
    window.addEventListener('load', run);
})();
