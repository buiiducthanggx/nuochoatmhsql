(() => {
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', () => {
            mainNav.classList.toggle('is-open');
        });
    }

    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(12px)';
        setTimeout(() => {
            card.style.transition = 'opacity 350ms ease, transform 350ms ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 60 * index);
    });

    const searchInput = document.getElementById('searchInput');
    const suggestionsEl = document.getElementById('searchSuggestions');
    let suggestTimer = null;

    if (searchInput && suggestionsEl) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim();
            clearTimeout(suggestTimer);

            if (q.length < 2) {
                suggestionsEl.innerHTML = '';
                suggestionsEl.classList.remove('show');
                return;
            }

            suggestTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`search_suggest.php?q=${encodeURIComponent(q)}`);
                    const items = await res.json();

                    suggestionsEl.innerHTML = items.map((item) => (
                        `<li><a href="product.php?id=${item.id}">${item.name} - ${Number(item.price).toLocaleString('vi-VN')} đ</a></li>`
                    )).join('');

                    suggestionsEl.classList.toggle('show', items.length > 0);
                } catch (err) {
                    suggestionsEl.innerHTML = '';
                    suggestionsEl.classList.remove('show');
                }
            }, 180);
        });

        document.addEventListener('click', (event) => {
            if (!suggestionsEl.contains(event.target) && event.target !== searchInput) {
                suggestionsEl.classList.remove('show');
            }
        });
    }

    const chatToggle = document.getElementById('chatToggle');
    const chatPanel = document.getElementById('chatPanel');
    if (chatToggle && chatPanel) {
        chatToggle.addEventListener('click', () => {
            chatPanel.classList.toggle('show');
        });
    }

    const mainProductImage = document.getElementById('mainProductImage');
    const galleryThumbs = document.querySelectorAll('[data-gallery-thumb]');
    if (mainProductImage && galleryThumbs.length > 0) {
        galleryThumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                const nextImage = thumb.getAttribute('data-image');
                if (!nextImage) {
                    return;
                }

                mainProductImage.src = nextImage;
                const link = mainProductImage.closest('a');
                if (link) {
                    link.setAttribute('href', nextImage);
                }

                galleryThumbs.forEach((item) => item.classList.remove('is-active'));
                thumb.classList.add('is-active');
            });
        });
    }

    const qtyWraps = document.querySelectorAll('[data-qty-wrap]');
    qtyWraps.forEach((wrap) => {
        const input = wrap.querySelector('.qty-input');
        const minus = wrap.querySelector('.qty-minus');
        const plus = wrap.querySelector('.qty-plus');
        if (!input || !minus || !plus) {
            return;
        }

        minus.addEventListener('click', () => {
            const current = Number(input.value || 1);
            const min = Number(input.min || 1);
            input.value = String(Math.max(min, current - 1));
        });

        plus.addEventListener('click', () => {
            const current = Number(input.value || 1);
            input.value = String(current + 1);
        });
    });

    const revealItems = document.querySelectorAll('.reveal-on-scroll');
    if (revealItems.length > 0 && 'IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.2,
            rootMargin: '0px 0px -40px 0px',
        });

        revealItems.forEach((item) => revealObserver.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }
})();
