/**
 * MANON Luxury Storefront JS
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sticky Navbar Effect
    const navbar = document.querySelector('.navbar-manon');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 30) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // 2. Add to Cart via AJAX
    document.querySelectorAll('.btn-add-cart-ajax').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.id;
            const sizeSelect = document.getElementById('productSizeSelect');
            const qtyInput = document.getElementById('productQtyInput');
            
            const colorInput = document.getElementById('productColorInput');
            const size = sizeSelect ? sizeSelect.value : (this.dataset.size || '54');
            const color = colorInput ? colorInput.value : (this.dataset.color || 'أسود');
            const quantity = qtyInput ? qtyInput.value : (this.dataset.qty || 1);

            const originalHtml = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> جاري الإضافة...';
            this.disabled = true;

            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            formData.append('size', size);
            formData.append('color', color);

            fetch('api/cart.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.innerHTML = originalHtml;
                this.disabled = false;

                if (data.success) {
                    showToast(data.message, 'success');
                    updateCartBadge(data.cartCount);
                } else {
                    showToast(data.message || 'حدث خطأ أثناء الإضافة', 'error');
                }
            })
            .catch(err => {
                this.innerHTML = originalHtml;
                this.disabled = false;
                showToast('تعذر الاتصال بالخادم', 'error');
            });
        });
    });

    // Special Order Form AJAX Submission
    const specialOrderForm = document.getElementById('specialOrderForm');
    if (specialOrderForm) {
        specialOrderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            const origHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> جاري تجهيز الطلب...';
            submitBtn.disabled = true;

            const fd = new FormData(this);
            fetch('api/special-order.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.innerHTML = origHtml;
                submitBtn.disabled = false;
                if (data.success) {
                    showToast('تم تسجيل طلبكِ الخاص! جاري فتح واتساب...', 'success');
                    specialOrderForm.reset();
                    setTimeout(() => {
                        window.open(data.wa_url, '_blank');
                    }, 800);
                } else {
                    showToast(data.message || 'تعذر حفظ الطلب', 'error');
                }
            })
            .catch(() => {
                submitBtn.innerHTML = origHtml;
                submitBtn.disabled = false;
                showToast('حدث خطأ في الاتصال', 'error');
            });
        });
    }

    // Customer Review Form AJAX Submission
    const reviewForm = document.getElementById('customerReviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            const origHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> جاري الإرسال...';
            submitBtn.disabled = true;

            const fd = new FormData(this);
            fetch('api/reviews.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.innerHTML = origHtml;
                submitBtn.disabled = false;
                if (data.success) {
                    showToast(data.message, 'success');
                    reviewForm.reset();
                    const modalEl = document.getElementById('addReviewModal');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const m = bootstrap.Modal.getInstance(modalEl);
                        if (m) m.hide();
                    }
                } else {
                    showToast(data.message || 'تعذر إرسال التقييم', 'error');
                }
            })
            .catch(() => {
                submitBtn.innerHTML = origHtml;
                submitBtn.disabled = false;
                showToast('حدث خطأ في الاتصال', 'error');
            });
        });
    }

    // Welcome Wheel of Fortune Auto-Popup and Canvas Logic
    const wheelModalEl = document.getElementById('welcomeWheelModal');
    const wheelCanvas = document.getElementById('wheelCanvas');
    const btnSpinWheel = document.getElementById('btnSpinWheel');
    const btnCopyWheelCode = document.getElementById('btnCopyWheelCode');

    if (wheelCanvas) {
        const ctx = wheelCanvas.getContext('2d');
        const sectors = [
            { label: 'خصم 10%', percent: 10, bg: '#1B1714', text: '#C5A880' },
            { label: 'خصم 5%',  percent: 5,  bg: '#FAF7F2', text: '#1B1714' },
            { label: 'خصم 1%',  percent: 1,  bg: '#C5A880', text: '#FFFFFF' },
            { label: 'خصم 10%', percent: 10, bg: '#1B1714', text: '#C5A880' },
            { label: 'خصم 5%',  percent: 5,  bg: '#FAF7F2', text: '#1B1714' },
            { label: 'خصم 1%',  percent: 1,  bg: '#C5A880', text: '#FFFFFF' }
        ];
        const numSectors = sectors.length;
        const arc = (2 * Math.PI) / numSectors;
        const radius = wheelCanvas.width / 2;

        function drawWheel() {
            ctx.clearRect(0, 0, wheelCanvas.width, wheelCanvas.height);
            for (let i = 0; i < numSectors; i++) {
                const angle = i * arc;
                ctx.beginPath();
                ctx.fillStyle = sectors[i].bg;
                ctx.moveTo(radius, radius);
                ctx.arc(radius, radius, radius, angle, angle + arc);
                ctx.lineTo(radius, radius);
                ctx.fill();
                ctx.strokeStyle = '#D1C2A5';
                ctx.lineWidth = 1.5;
                ctx.stroke();

                // Draw Text
                ctx.save();
                ctx.translate(radius, radius);
                ctx.rotate(angle + arc / 2);
                ctx.textAlign = 'right';
                ctx.fillStyle = sectors[i].text;
                ctx.font = 'bold 15px Alexandria, sans-serif';
                ctx.fillText(sectors[i].label, radius - 20, 6);
                ctx.restore();
            }
        }
        drawWheel();

        let currentRotation = 0;
        if (btnSpinWheel) {
            btnSpinWheel.addEventListener('click', function() {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm ms-2"></span> جاري تدوير العجلة...';

                const fd = new FormData();
                fd.append('action', 'spin');

                fetch('api/wheel.php', {
                    method: 'POST',
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Find matching sectors for this percent
                        const matchingIndices = [];
                        sectors.forEach((sec, idx) => {
                            if (sec.percent === data.percent) matchingIndices.push(idx);
                        });
                        const targetIndex = matchingIndices[Math.floor(Math.random() * matchingIndices.length)];

                        // Calculate degree: Pointer is at top (270 deg / -90 deg)
                        // Sector center angle = targetIndex * arc + arc/2
                        const sectorDegree = (targetIndex * (360 / numSectors)) + (360 / numSectors / 2);
                        // To align this sector with top pointer (270 deg)
                        const targetRotation = 360 * 5 + (270 - sectorDegree);
                        currentRotation += targetRotation;

                        wheelCanvas.style.transform = `rotate(${currentRotation}deg)`;

                        setTimeout(() => {
                            localStorage.setItem('manon_wheel_spun', 'true');
                            const actionArea = document.getElementById('wheelActionArea');
                            const resultArea = document.getElementById('wheelResultArea');
                            const winMsg = document.getElementById('wheelWinMessage');
                            const resCode = document.getElementById('wheelResultCode');

                            if (winMsg) winMsg.textContent = `🎉 مبروك! حصلتِ على خصم ${data.percent}%`;
                            if (resCode) resCode.textContent = data.code;
                            if (actionArea) actionArea.classList.add('d-none');
                            if (resultArea) resultArea.classList.remove('d-none');

                            showToast(`مبروك! فزتِ بكود خصم ${data.percent}%`, 'success');
                        }, 4200);
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-arrow-repeat ms-1"></i> تدوير العجلة الآن';
                        showToast(data.message || 'تعذر تدوير العجلة', 'error');
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat ms-1"></i> تدوير العجلة الآن';
                    showToast('حدث خطأ أثناء الاتصال', 'error');
                });
            });
        }
    }

    // Copy wheel code to clipboard
    if (btnCopyWheelCode) {
        btnCopyWheelCode.addEventListener('click', function() {
            const codeEl = document.getElementById('wheelResultCode');
            if (codeEl) {
                navigator.clipboard.writeText(codeEl.textContent.trim()).then(() => {
                    showToast('تم نسخ كود الخصم بنجاح!', 'success');
                    btnCopyWheelCode.innerHTML = '<i class="bi bi-check2 ms-1"></i> تم النسخ';
                    setTimeout(() => {
                        btnCopyWheelCode.innerHTML = '<i class="bi bi-clipboard ms-1"></i> نسخ الكود';
                    }, 2500);
                });
            }
        });
    }

    if (wheelModalEl && typeof bootstrap !== 'undefined') {
        const hasSpun = localStorage.getItem('manon_wheel_spun');
        if (!hasSpun) {
            setTimeout(() => {
                const wheelModal = new bootstrap.Modal(wheelModalEl);
                wheelModal.show();
            }, 2500);
        }
    }

    // 3. Product Gallery Thumbnail Switcher
    const mainGalleryImg = document.getElementById('mainGalleryImg');
    if (mainGalleryImg) {
        document.querySelectorAll('.gallery-thumb').forEach(thumb => {
            thumb.addEventListener('click', function() {
                document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                mainGalleryImg.src = this.dataset.full;
            });
        });
    }

    // 4. Quantity Increment/Decrement
    document.querySelectorAll('.qty-btn-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.closest('.qty-control-wrap').querySelector('.qty-input');
            if (input) {
                input.value = parseInt(input.value || 1) + 1;
                input.dispatchEvent(new Event('change'));
            }
        });
    });

    document.querySelectorAll('.qty-btn-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.closest('.qty-control-wrap').querySelector('.qty-input');
            if (input && parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                input.dispatchEvent(new Event('change'));
            }
        });
    });
});

/**
 * Toast Notification System
 */
function showToast(message, type = 'success') {
    let toast = document.getElementById('manonToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'manonToast';
        toast.className = 'manon-toast';
        document.body.appendChild(toast);
    }

    const icon = type === 'success' 
        ? '<i class="bi bi-check-circle-fill text-success fs-5"></i>' 
        : '<i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>';

    toast.innerHTML = `
        ${icon}
        <div>
            <div class="fw-bold">${type === 'success' ? 'تمت العملية' : 'تنبيه'}</div>
            <div class="small opacity-75">${message}</div>
        </div>
    `;

    toast.classList.add('show');
    clearTimeout(window.toastTimer);
    window.toastTimer = setTimeout(() => {
        toast.classList.remove('show');
    }, 3500);
}

/**
 * Update Cart Badge in Navbar & Mobile Nav
 */
function updateCartBadge(count) {
    document.querySelectorAll('.cart-count-val').forEach(el => {
        el.textContent = count;
        el.style.transform = 'scale(1.2)';
        setTimeout(() => el.style.transform = 'scale(1)', 200);
    });
}
