/**
 * CipherUI - تحسينات واجهة المستخدم
 * Vanilla JavaScript فقط
 */

document.addEventListener('DOMContentLoaded', function() {

    // التحقق من نموذج التسجيل
    var registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            var password = document.getElementById('password');
            var confirm = document.getElementById('confirm_password');
            if (password && confirm && password.value !== confirm.value) {
                e.preventDefault();
                showError(registerForm, 'كلمتا المرور غير متطابقتين.');
            }
        });
    }

    // التحقق من نموذج تسجيل الدخول
    var loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            var username = document.getElementById('username');
            var password = document.getElementById('password');
            if (username && password && (!username.value.trim() || !password.value)) {
                event.preventDefault();
                return false;
            }
        });
    }

    // نموذج تشفير النص
    var textEncryptForm = document.getElementById('textEncryptForm');
    if (textEncryptForm) {
        textEncryptForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var textarea = document.getElementById('plainText');
            var plainText = textarea ? textarea.value.trim() : '';
            if (!plainText) {
                showMessage('messageArea', 'أدخل نصاً للتشفير.', 'error');
                return;
            }
            encryptText(plainText);
        });
    }

    // نموذج رفع الملف
    var uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var fileInput = document.getElementById('fileInput');
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                showMessage('messageArea', 'اختر ملفاً أولاً.', 'error');
                return;
            }
            uploadFile(fileInput.files[0]);
        });
    }

    // أزرار فك التشفير والحذف
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-decrypt')) {
            var id = e.target.getAttribute('data-id');
            if (id) decryptData(parseInt(id, 10));
        }
        if (e.target.classList.contains('btn-delete')) {
            var id = e.target.getAttribute('data-id');
            if (id) deleteData(parseInt(id, 10), e.target);
        }
    });

    // إغلاق النافذة المنبثقة
    var closeModalBtn = document.getElementById('closeModalBtn');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            var modal = document.getElementById('decryptModal');
            if (modal) modal.classList.remove('active');
        });
    }

    // إغلاق عند النقر خارج المحتوى
    var modal = document.getElementById('decryptModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) modal.classList.remove('active');
        });
    }
});

function showError(form, msg) {
    var existing = form.querySelector('.message.error');
    if (existing) existing.remove();
    var div = document.createElement('div');
    div.className = 'message error';
    div.textContent = msg;
    form.insertBefore(div, form.firstChild);
}

function showMessage(containerId, msg, type) {
    var container = document.getElementById(containerId);
    if (!container) return;
    var div = document.createElement('div');
    div.className = 'message ' + (type || 'success');
    div.textContent = msg;
    container.innerHTML = '';
    container.appendChild(div);
    setTimeout(function() { div.remove(); }, 4000);
}

function encryptText(plainText) {
    var msgArea = document.getElementById('messageArea');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'encrypt.php');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    showMessage('messageArea', res.message, 'success');
                    document.getElementById('plainText').value = '';
                    location.reload();
                } else {
                    showMessage('messageArea', res.message || 'فشل التشفير', 'error');
                }
            } catch (err) {
                showMessage('messageArea', 'حدث خطأ غير متوقع', 'error');
            }
        }
    };
    xhr.send('plain_text=' + encodeURIComponent(plainText));
}

function uploadFile(file) {
    var msgArea = document.getElementById('messageArea');
    var formData = new FormData();
    formData.append('file', file);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'upload.php');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    showMessage('messageArea', res.message, 'success');
                    document.getElementById('fileInput').value = '';
                    location.reload();
                } else {
                    showMessage('messageArea', res.message || 'فشل الرفع', 'error');
                }
            } catch (err) {
                showMessage('messageArea', 'حدث خطأ غير متوقع', 'error');
            }
        }
    };
    xhr.send(formData);
}

function getMimeFromExtension(filename) {
    var ext = (filename || '').split('.').pop().toLowerCase();
    var map = {
        'jpg': 'image/jpeg', 'jpeg': 'image/jpeg', 'png': 'image/png',
        'gif': 'image/gif', 'webp': 'image/webp', 'bmp': 'image/bmp',
        'svg': 'image/svg+xml', 'ico': 'image/x-icon'
    };
    return map[ext] || 'application/octet-stream';
}

function isImageMime(mime) {
    return mime && mime.indexOf('image/') === 0;
}

function decryptData(id) {
    var modal = document.getElementById('decryptModal');
    var contentEl = document.getElementById('decryptedContent');
    if (!modal || !contentEl) return;
    contentEl.innerHTML = '';
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'decrypt.php');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    var data = res.data;
                    if (data.indexOf('FILE:') === 0) {
                        var parts = data.split(':');
                        var base64, filename, mime;
                        if (parts.length >= 4) {
                            mime = parts[1];
                            base64 = parts[2];
                            filename = parts.slice(3).join(':');
                        } else if (parts.length === 3) {
                            base64 = parts[1];
                            filename = parts[2];
                            mime = getMimeFromExtension(filename);
                        } else {
                            contentEl.textContent = 'صيغة الملف غير صحيحة';
                            modal.classList.add('active');
                            return;
                        }
                        var dataUrl = 'data:' + mime + ';base64,' + base64;
                        var html = '<p class="decrypted-file-name">' + escapeHtml(filename) + '</p>';
                        if (isImageMime(mime)) {
                            html += '<div class="decrypted-image-wrap"><img src="' + dataUrl + '" alt="' + escapeHtml(filename) + '" class="decrypted-image"></div>';
                        }
                        html += '<button type="button" class="btn btn-small btn-primary" id="downloadDecrypted">تحميل الملف</button>';
                        contentEl.innerHTML = html;
                        contentEl.dataset.fileContent = base64;
                        contentEl.dataset.fileName = filename;
                        contentEl.dataset.fileMime = mime;
                        modal.classList.add('active');
                        var btn = document.getElementById('downloadDecrypted');
                        if (btn) btn.onclick = function() {
                            var b64 = contentEl.dataset.fileContent;
                            var name = contentEl.dataset.fileName || 'download';
                            var type = contentEl.dataset.fileMime || 'application/octet-stream';
                            var arr = Uint8Array.from(atob(b64), function(c) { return c.charCodeAt(0); });
                            var blob = new Blob([arr], { type: type });
                            var a = document.createElement('a');
                            a.href = URL.createObjectURL(blob);
                            a.download = name;
                            a.click();
                            URL.revokeObjectURL(a.href);
                        };
                        return;
                    }
                    contentEl.textContent = data;
                    contentEl.dataset.fileContent = '';
                    contentEl.dataset.fileName = '';
                } else {
                    contentEl.textContent = res.message || 'فشل فك التشفير';
                }
                modal.classList.add('active');
            } catch (err) {
                contentEl.textContent = 'حدث خطأ غير متوقع';
                modal.classList.add('active');
            }
        }
    };
    xhr.send('id=' + id);
}

function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function deleteData(id, btnEl) {
    if (!confirm('هل أنت متأكد من حذف هذه البيانات؟ لا يمكن التراجع عن هذا الإجراء.')) return;
    btnEl.disabled = true;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'delete.php');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    var item = btnEl.closest('.data-item');
                    if (item) {
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(-20px)';
                        item.style.transition = 'all 0.3s ease';
                        setTimeout(function() {
                            item.remove();
                            var list = document.querySelector('.data-list');
                            if (list && list.children.length === 0) {
                                var empty = document.createElement('p');
                                empty.className = 'empty-state';
                                empty.textContent = 'لا توجد بيانات مشفرة حتى الآن.';
                                list.parentNode.appendChild(empty);
                                list.remove();
                            }
                        }, 300);
                    }
                    showMessage('messageArea', res.message, 'success');
                } else {
                    showMessage('messageArea', res.message || 'فشل الحذف', 'error');
                    btnEl.disabled = false;
                }
            } catch (err) {
                showMessage('messageArea', 'حدث خطأ غير متوقع', 'error');
                btnEl.disabled = false;
            }
        }
    };
    xhr.send('id=' + id);
}
