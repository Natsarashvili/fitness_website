
document.addEventListener('DOMContentLoaded', function() {
    
    // ===== მობილურის მენიუ =====
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            // ვაცვლით კლასს - თუ არის active წავშალოთ, თუ არა დავამატოთ
            navMenu.classList.toggle('active');
        });
    }
    
    // ===== შეტყობინებების ავტომატური დახურვა =====
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(function(alert) {
        // 5 წამის შემდეგ თანდათან გაქრება
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            
            // გაქრობის შემდეგ სრულიად წაშლა
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // ===== ფორმის ვალიდაცია =====
    const forms = document.querySelectorAll('form');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                // თუ ცარიელია სავალდებულო ველი
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger-color)';
                    
                    // შეცდომის შეტყობინება
                    showFieldError(field, 'ეს ველი სავალდებულოა');
                } else {
                    field.style.borderColor = 'var(--border-color)';
                    removeFieldError(field);
                }
            });
            
            // თუ ვალიდაცია ვერ გაიარა
            if (!isValid) {
                e.preventDefault();
                showAlert('გთხოვთ შეავსოთ ყველა სავალდებულო ველი', 'error');
            }
        });
        
        // როცა ვწერთ ველში - შეცდომა უნდა გაქრეს
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(function(input) {
            input.addEventListener('input', function() {
                this.style.borderColor = 'var(--border-color)';
                removeFieldError(this);
            });
        });
    });
    
    // ===== წაშლის დადასტურება =====
    const deleteButtons = document.querySelectorAll('.btn-delete, [data-confirm]');
    
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'დარწმუნებული ხართ რომ გსურთ წაშლა?';
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // ===== სურათის preview (ატვირთვამდე) =====
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    
    imageInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // შევამოწმოთ არის თუ არა სურათი
                if (!file.type.startsWith('image/')) {
                    showAlert('გთხოვთ აირჩიოთ სურათი', 'error');
                    this.value = '';
                    return;
                }
                
                // შევამოწმოთ ზომა (5MB მაქსიმუმ)
                if (file.size > 5 * 1024 * 1024) {
                    showAlert('სურათი ძალიან დიდია (მაქს 5MB)', 'error');
                    this.value = '';
                    return;
                }
                
                // სურათის preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // ===== ძებნის live search =====
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            // 500მს ველოდებით რომ მომხმარებელმა დაასრულოს წერა
            clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(function() {
                const query = searchInput.value.trim();
                
                if (query.length >= 2) {
                    // აქ შეიძლება AJAX request გავაკეთოთ
                    console.log('ძებნა:', query);
                }
            }, 500);
        });
    }
    
    // ===== რეიტინგის ვარსკვლავები =====
    const ratingStars = document.querySelectorAll('.rating-star');
    
    ratingStars.forEach(function(star, index) {
        star.addEventListener('click', function() {
            const rating = index + 1;
            const ratingInput = document.getElementById('ratingInput');
            
            if (ratingInput) {
                ratingInput.value = rating;
            }
            
            // ვარსკვლავების განახლება
            ratingStars.forEach(function(s, i) {
                if (i < rating) {
                    s.textContent = '⭐';
                } else {
                    s.textContent = '☆';
                }
            });
        });
    });
    
    // ===== Smooth scroll =====
    const smoothScrollLinks = document.querySelectorAll('a[href^="#"]');
    
    smoothScrollLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            if (targetId !== '#' && targetId !== '#!') {
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
});

// ===== დამხმარე ფუნქციები =====

/**
 * შეტყობინების ჩვენება
 */
function showAlert(message, type = 'success') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass}`;
    alert.textContent = message;
    
    const container = document.querySelector('.container');
    container.insertBefore(alert, container.firstChild);
    
    // 5 წამის შემდეგ გაქრება
    setTimeout(function() {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        
        setTimeout(function() {
            alert.remove();
        }, 500);
    }, 5000);
}

/**
 * ველის შეცდომის ჩვენება
 */
function showFieldError(field, message) {
    // შევამოწმოთ უკვე არ არის თუ არა შეცდომა
    const existingError = field.parentElement.querySelector('.field-error');
    if (existingError) return;
    
    const error = document.createElement('span');
    error.className = 'field-error';
    error.style.color = 'var(--danger-color)';
    error.style.fontSize = '0.85rem';
    error.style.marginTop = '0.25rem';
    error.style.display = 'block';
    error.textContent = message;
    
    field.parentElement.appendChild(error);
}

/**
 * ველის შეცდომის წაშლა
 */
function removeFieldError(field) {
    const error = field.parentElement.querySelector('.field-error');
    if (error) {
        error.remove();
    }
}

/**
 * Loading spinner-ის ჩვენება
 */
function showLoading(button) {
    button.disabled = true;
    button.innerHTML = '<span class="spinner">⏳</span> იტვირთება...';
}

/**
 * Loading-ის მოხსნა
 */
function hideLoading(button, originalText) {
    button.disabled = false;
    button.innerHTML = originalText;
}

/**
 * ფორმატირება: რიცხვი → ქართული
 */
function formatNumber(num) {
    return new Intl.NumberFormat('ka-GE').format(num);
}

/**
 * თარიღის ფორმატირება
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('ka-GE', options);
}