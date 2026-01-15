
document.addEventListener('DOMContentLoaded', function() {
    
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
    
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
       const forms = document.querySelectorAll('form');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger-color)';
                    
                    showFieldError(field, 'ეს ველი სავალდებულოა');
                } else {
                    field.style.borderColor = 'var(--border-color)';
                    removeFieldError(field);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert('გთხოვთ შეავსოთ ყველა სავალდებულო ველი', 'error');
            }
        });
        
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(function(input) {
            input.addEventListener('input', function() {
                this.style.borderColor = 'var(--border-color)';
                removeFieldError(this);
            });
        });
    });
    
    const deleteButtons = document.querySelectorAll('.btn-delete, [data-confirm]');
    
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'დარწმუნებული ხართ რომ გსურთ წაშლა?';
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    
    imageInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                if (!file.type.startsWith('image/')) {
                    showAlert('გთხოვთ აირჩიოთ სურათი', 'error');
                    this.value = '';
                    return;
                }
                
                if (file.size > 5 * 1024 * 1024) {
                    showAlert('სურათი ძალიან დიდია (მაქს 5MB)', 'error');
                    this.value = '';
                    return;
                }
                
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
    

    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(function() {
                const query = searchInput.value.trim();
                
                if (query.length >= 2) {
                    console.log('ძებნა:', query);
                }
            }, 500);
        });
    }
    

    const ratingStars = document.querySelectorAll('.rating-star');
    
    ratingStars.forEach(function(star, index) {
        star.addEventListener('click', function() {
            const rating = index + 1;
            const ratingInput = document.getElementById('ratingInput');
            
            if (ratingInput) {
                ratingInput.value = rating;
            }
            

            ratingStars.forEach(function(s, i) {
                if (i < rating) {
                    s.textContent = '⭐';
                } else {
                    s.textContent = '☆';
                }
            });
        });
    });
    

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


function showAlert(message, type = 'success') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass}`;
    alert.textContent = message;
    
    const container = document.querySelector('.container');
    container.insertBefore(alert, container.firstChild);
    

    setTimeout(function() {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        
        setTimeout(function() {
            alert.remove();
        }, 500);
    }, 5000);
}


function showFieldError(field, message) {
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


function removeFieldError(field) {
    const error = field.parentElement.querySelector('.field-error');
    if (error) {
        error.remove();
    }
}


function showLoading(button) {
    button.disabled = true;
    button.innerHTML = '<span class="spinner">⏳</span> იტვირთება...';
}


function hideLoading(button, originalText) {
    button.disabled = false;
    button.innerHTML = originalText;
}


function formatNumber(num) {
    return new Intl.NumberFormat('ka-GE').format(num);
}


function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('ka-GE', options);
}