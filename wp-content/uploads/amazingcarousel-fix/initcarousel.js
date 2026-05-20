(function() {
    'use strict';

    function initEventsCarousel() {
        var lists = document.querySelectorAll('.amazingcarousel-list');
        
        lists.forEach(function(list) {
            // Skip if already initialized
            if (list.classList.contains('carousel-initialized')) {
                return;
            }
            list.classList.add('carousel-initialized');
            
            var items = Array.from(list.querySelectorAll('.amazingcarousel-item'));
            if (items.length === 0) return;
            
            // Reset any inline styles that might interfere
            list.style.marginLeft = '';
            list.style.width = '';
            
            // Clone items for seamless infinite loop (need 2x for -50% translate)
            items.forEach(function(item) {
                var clone = item.cloneNode(true);
                clone.classList.add('amazingcarousel-clone');
                list.appendChild(clone);
            });
            
            // Add scroll animation class
            list.classList.add('amazingcarousel-scroll');
        });
    }

    function run() {
        initEventsCarousel();
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
    
    // Re-run after all images load to ensure correct widths
    window.addEventListener('load', run);
})();
