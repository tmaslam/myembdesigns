(function() {
    'use strict';
    
    function fixLogoSlider() {
        var lists = document.querySelectorAll('.amazingcarousel-list');
        
        lists.forEach(function(list) {
            // Reset negative margin
            list.style.marginLeft = '0';
            list.style.display = 'flex';
            list.style.flexWrap = 'nowrap';
            list.style.width = 'max-content';
            
            var items = Array.from(list.querySelectorAll('.amazingcarousel-item'));
            if (items.length === 0) return;
            
            // Clone items for seamless infinite loop
            items.forEach(function(item) {
                var clone = item.cloneNode(true);
                clone.classList.add('amazingcarousel-clone');
                list.appendChild(clone);
            });
            
            // Add animation class
            list.classList.add('amazingcarousel-scroll');
        });
        
        // Make wrappers visible
        document.querySelectorAll('.amazingcarousel-list-wrapper').forEach(function(w) {
            w.style.overflow = 'hidden';
            w.style.width = '100%';
        });
        
        document.querySelectorAll('.amazingcarousel-list-container').forEach(function(c) {
            c.style.overflow = 'hidden';
            c.style.width = '100%';
        });
        
        // Inject infinite scroll CSS
        if (!document.getElementById('amazingcarousel-scroll-css')) {
            var style = document.createElement('style');
            style.id = 'amazingcarousel-scroll-css';
            style.textContent = `
                .amazingcarousel-scroll {
                    animation: amazingcarousel-scroll 45s linear infinite;
                }
                .amazingcarousel-scroll:hover {
                    animation-play-state: paused;
                }
                @keyframes amazingcarousel-scroll {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-50%); }
                }
                .amazingcarousel-list-container {
                    display: flex;
                    justify-content: center;
                }
                .amazingcarousel-item {
                    float: none !important;
                    flex: 0 0 auto;
                    margin: 4px 12px !important;
                    min-width: 110px;
                    text-align: center;
                }
                .amazingcarousel-item-container {
                    text-align: center;
                }
                .amazingcarousel-images {
                    text-align: center;
                }
                .amazingcarousel-images a {
                    display: inline-block;
                    text-align: center;
                }
                .amazingcarousel-images img {
                    width: 70px !important;
                    height: 70px !important;
                    object-fit: contain;
                    display: inline-block;
                    margin: 0 auto;
                }
                .amazingcarousel-title {
                    text-align: center;
                    font-size: 11px;
                    margin-top: 6px;
                    line-height: 1.3;
                    word-wrap: break-word;
                }
                .amazingcarousel-title a {
                    color: #333;
                    text-decoration: none;
                    display: block;
                    text-align: center;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    function run() {
        fixLogoSlider();
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
    
    window.addEventListener('load', run);
})();
