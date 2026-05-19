(function() {
    'use strict';
    
    function fixEvents() {
        var blogCarousel = document.getElementById('blogCarousel');
        if (!blogCarousel) return;
        
        // Walk up and show all parent containers
        var parent = blogCarousel.parentElement;
        while (parent) {
            if (parent.style && parent.style.display === 'none') {
                parent.style.display = 'block';
            }
            parent = parent.parentElement;
        }
        
        // Show the carousel itself
        blogCarousel.style.display = 'block';
        blogCarousel.style.visibility = 'visible';
        blogCarousel.style.opacity = '1';
        
        // Remove Bootstrap carousel classes that might conflict
        blogCarousel.classList.remove('carousel', 'slide');
        blogCarousel.removeAttribute('data-ride');
        
        var inner = blogCarousel.querySelector('.carousel-inner');
        if (!inner) return;
        
        inner.className = 'row';
        inner.style.display = 'flex';
        inner.style.flexWrap = 'wrap';
        inner.style.margin = '0 -10px';
        
        var tabs = inner.querySelectorAll('.signletab');
        tabs.forEach(function(tab) {
            tab.className = 'col-6 col-md-4 col-lg-3 mb-3';
            tab.style.padding = '10px';
            
            var link = tab.querySelector('a');
            if (link) {
                link.style.display = 'block';
                link.style.padding = '25px 15px';
                link.style.background = '#f8f8f8';
                link.style.borderRadius = '8px';
                link.style.textDecoration = 'none';
                link.style.color = '#ce1d76';
                link.style.fontWeight = '600';
                link.style.textAlign = 'center';
                link.style.border = '2px solid #ce1d76';
                link.style.transition = 'all 0.3s ease';
                link.style.fontSize = '14px';
            }
        });
    }
    
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
        fixEvents();
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
