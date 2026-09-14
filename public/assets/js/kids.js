/* generated from kids-braces-page-layout.html */


        // Accordion FAQ Toggle
        function toggleFaq(btn) {
            var item = btn.parentElement;
            var isActive = item.classList.contains('active');
            
            document.querySelectorAll('.faq-item').forEach(function(el) {
                el.classList.remove('active');
            });

            if (!isActive) {
                item.classList.add('active');
            }
        }
