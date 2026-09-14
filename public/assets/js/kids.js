/* generated from kids-braces-page-layout.html */
// Modal Handlers
        (function() {
            var modal = document.getElementById('booking-modal');
            var closeBtn = document.getElementById('modal-close-btn');
            var initialView = document.getElementById('modal-initial-view');
            var successView = document.getElementById('modal-success-view');
            var locName = document.getElementById('selected-loc-name');
            
            document.querySelectorAll('[data-popup="true"]').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (initialView && successView) {
                        initialView.style.display = 'block';
                        successView.style.display = 'none';
                    }
                    modal.classList.add('active');
                });
            });

            

            closeBtn.addEventListener('click', function() {
                modal.classList.remove('active');
            });

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        })();

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
