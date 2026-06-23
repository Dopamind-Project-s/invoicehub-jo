(function(){

// Extracted from resources/views/company/settings/edit.blade.php
document.addEventListener('DOMContentLoaded',function(){var form=document.querySelector('[data-settings-form]');if(!form)return;var tabs=form.querySelectorAll('[data-tab]');var panels=form.querySelectorAll('[data-panel]');tabs.forEach(function(tab){tab.addEventListener('click',function(){tabs.forEach(function(t){t.classList.remove('active')});panels.forEach(function(p){p.classList.remove('active')});tab.classList.add('active');form.querySelector('[data-panel="'+tab.dataset.tab+'"]').classList.add('active');});});form.querySelectorAll('[data-image-input]').forEach(function(input){input.addEventListener('change',function(){var file=input.files[0];var summary=form.querySelector('[data-validation-summary]');if(!file)return;if(!file.type.startsWith('image/')||file.size>2*1024*1024){summary.style.display='block';summary.innerHTML='<div>الصورة يجب أن تكون ملف صورة وحجمها لا يتجاوز 2MB.</div>';input.value='';return;}var img=document.getElementById(input.dataset.preview);if(img)img.src=URL.createObjectURL(file);summary.style.display='none';});});form.addEventListener('submit',function(event){var errors=[];var currency=form.querySelector('[name="settings[default_currency]"]');if(currency&& !/^[A-Za-z]{3}$/.test(currency.value.trim()))errors.push('العملة الافتراضية يجب أن تكون رمزاً من 3 أحرف مثل JOD.');if(errors.length){event.preventDefault();var summary=form.querySelector('[data-validation-summary]');summary.style.display='block';summary.innerHTML=errors.map(function(e){return '<div>'+e+'</div>';}).join('');window.scrollTo({top:form.offsetTop-20,behavior:'smooth'});}});});


// Extracted from resources/views/company/master-data/categories/_form.blade.php
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-category-form]').forEach(function (form) {
        var summary = form.querySelector('[data-validation-summary]');
        var currentIcon = form.querySelector('[data-current-icon]');
        function showErrors(errors) { summary.style.display = errors.length ? 'block' : 'none'; summary.innerHTML = errors.map(function (e) { return '<div>'+e+'</div>'; }).join(''); }
        form.querySelectorAll('[data-icon-choice]').forEach(function (choice) { choice.addEventListener('change', function () { if (currentIcon) currentIcon.textContent = choice.value; }); });
        form.addEventListener('submit', function (event) {
            var errors = [];
            var nameAr = form.querySelector('[name="name_ar"]');
            var code = form.querySelector('[name="code"]');
            if (!nameAr.value.trim()) errors.push('الاسم العربي مطلوب ولا يمكن تركه فارغاً.');
            if (!code.value.trim()) errors.push('كود الفئة مطلوب ولا يمكن تركه فارغاً.');
            if (errors.length) { event.preventDefault(); showErrors(errors); window.scrollTo({ top: form.offsetTop - 20, behavior: 'smooth' }); }
        });
    });
});


// Extracted from resources/views/company/master-data/units/_form.blade.php
document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-unit-form]').forEach(function(form) {
            var summary = form.querySelector('[data-validation-summary]');

            function showErrors(errors) {
                summary.style.display = errors.length ? 'block' : 'none';
                summary.innerHTML = errors.map(function(e) {
                    return '<div>' + e + '</div>';
                }).join('');
            }
            form.addEventListener('submit', function(event) {
                var errors = [];
                var nameAr = form.querySelector('[name="name_ar"]');
                var code = form.querySelector('[name="code"]');
                if (!nameAr.value.trim()) errors.push('الاسم العربي مطلوب ولا يمكن تركه فارغاً.');
                if (!code.value.trim()) errors.push('كود الوحدة مطلوب ولا يمكن تركه فارغاً.');
                if (errors.length) {
                    event.preventDefault();
                    showErrors(errors);
                    window.scrollTo({
                        top: form.offsetTop - 20,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });


// Extracted from resources/views/company/master-data/tax-profiles/_form.blade.php
document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-tax-form]').forEach(function(form) {
            var summary = form.querySelector('[data-validation-summary]');

            function showErrors(errors) {
                summary.style.display = errors.length ? 'block' : 'none';
                summary.innerHTML = errors.map(function(e) {
                    return '<div>' + e + '</div>';
                }).join('');
            }
            form.addEventListener('submit', function(event) {
                var errors = [];
                var name = form.querySelector('[name="name"]');
                var type = form.querySelector('[name="tax_type"]');
                var percent = form.querySelector('[name="tax_percent"]');
                if (!name.value.trim()) errors.push('اسم الضريبة مطلوب ولا يمكن تركه فارغاً.');
                if (!type.value.trim()) errors.push('نوع الضريبة مطلوب.');
                if (percent.value === '' || Number(percent.value) < 0 || Number(percent.value) > 100) errors.push('نسبة الضريبة مطلوبة ويجب أن تكون بين 0 و100.');
                if (errors.length) {
                    event.preventDefault();
                    showErrors(errors);
                    window.scrollTo({
                        top: form.offsetTop - 20,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });


// Extracted from resources/views/company/master-data/contacts/_form.blade.php
document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-contact-form]').forEach(function(form) {
            var summary = form.querySelector('[data-validation-summary]');

            function showErrors(errors) {
                summary.style.display = errors.length ? 'block' : 'none';
                summary.innerHTML = errors.map(function(e) {
                    return '<div>' + e + '</div>';
                }).join('');
            }
            form.addEventListener('submit', function(event) {
                var errors = [];
                var nameAr = form.querySelector('[name="name_ar"]');
                var type = form.querySelector('[name="type"]');
                var email = form.querySelector('[name="email"]');
                var phone = form.querySelector('[name="phone"]');
                if (!nameAr.value.trim()) errors.push('الاسم العربي مطلوب ولا يمكن تركه فارغاً.');
                if (!type.value) errors.push('نوع جهة الاتصال مطلوب.');
                if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) errors.push('صيغة البريد الإلكتروني غير صحيحة.');
                if (phone.value && !/^[0-9+()\-\s]*$/.test(phone.value)) errors.push('الهاتف يجب أن يحتوي على أرقام ورموز اتصال فقط.');
                if (errors.length) {
                    event.preventDefault();
                    showErrors(errors);
                    window.scrollTo({
                        top: form.offsetTop - 20,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });


// Extracted from resources/views/company/master-data/products/_form.blade.php
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-product-form]').forEach(function (form) {
        var imageInput = form.querySelector('[data-image-input]');
        var imagePreview = form.querySelector('[data-image-preview]');
        var summary = form.querySelector('[data-validation-summary]');
        function showErrors(errors) { summary.style.display = errors.length ? 'block' : 'none'; summary.innerHTML = errors.map(function (e) { return '<div>'+e+'</div>'; }).join(''); }
        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', function () {
                var file = imageInput.files[0];
                if (!file) return;
                if (!['image/jpeg','image/png','image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) { showErrors(['صيغة الصورة أو حجمها غير صحيح.']); imageInput.value = ''; return; }
                imagePreview.src = URL.createObjectURL(file);
                showErrors([]);
            });
        }
        form.addEventListener('submit', function (event) {
            var errors = [];
            var nameAr = form.querySelector('[name="name_ar"]');
            var type = form.querySelector('[name="type"]');
            var price = form.querySelector('[name="price"]');
            var cost = form.querySelector('[name="cost"]');
            if (!nameAr.value.trim()) errors.push('الاسم العربي مطلوب.');
            if (!type.value) errors.push('نوع المنتج مطلوب.');
            if (price.value === '' || Number(price.value) < 0) errors.push('السعر مطلوب ويجب أن يكون 0 أو أكثر.');
            if (cost.value !== '' && Number(cost.value) < 0) errors.push('التكلفة يجب أن تكون 0 أو أكثر.');
            if (imageInput && imageInput.files[0]) {
                var file = imageInput.files[0];
                if (!['image/jpeg','image/png','image/webp'].includes(file.type)) errors.push('صيغة الصورة يجب أن تكون JPG أو PNG أو WEBP.');
                if (file.size > 2 * 1024 * 1024) errors.push('حجم الصورة يجب ألا يتجاوز 2MB.');
            }
            if (errors.length) { event.preventDefault(); showErrors(errors); window.scrollTo({ top: form.offsetTop - 20, behavior: 'smooth' }); }
        });
    });
});


// Extracted from resources/views/invoices/form.blade.php
document.addEventListener('DOMContentLoaded', function () {
  const table = document.getElementById('items-table');
  const addRow = document.getElementById('add-row');
  const grandTotal = document.getElementById('grand-total');
  if (!table || !addRow || !grandTotal) return;
  const recalc = function () {
    let total = 0;
    table.querySelectorAll('tbody tr').forEach(function (row) {
      const nums = row.querySelectorAll('.calc');
      const q = parseFloat(nums[0]?.value) || 0;
      const p = parseFloat(nums[1]?.value) || 0;
      const d = parseFloat(nums[2]?.value) || 0;
      const t = parseFloat(nums[3]?.value) || 0;
      const line = Math.max(q * p - d, 0) * (1 + t / 100);
      const lineTotal = row.querySelector('.line-total');
      if (lineTotal) lineTotal.textContent = line.toFixed(3);
      total += line;
    });
    grandTotal.textContent = total.toFixed(3);
  };
  document.addEventListener('input', function (event) { if (event.target.classList.contains('calc')) recalc(); });
  addRow.addEventListener('click', function () {
    const tbody = table.querySelector('tbody');
    const i = tbody.children.length;
    tbody.insertAdjacentHTML('beforeend', `<tr><td><input name="items[${i}][description]" class="form-control" required></td><td><input name="items[${i}][quantity]" type="number" step="0.001" min="0.001" class="form-control calc" value="1" required></td><td><input name="items[${i}][unit_price]" type="number" step="0.001" min="0" class="form-control calc" value="0" required></td><td><input name="items[${i}][discount]" type="number" step="0.001" min="0" class="form-control calc" value="0"></td><td><input name="items[${i}][tax_percent]" type="number" step="0.001" min="0" class="form-control calc" value="0"></td><td class="line-total ltr">0.000</td><td><button type="button" class="btn btn-sm btn-danger remove-row">حذف</button></td></tr>`);
    recalc();
  });
  document.addEventListener('click', function (event) { if (event.target.classList.contains('remove-row')) { event.target.closest('tr').remove(); recalc(); } });
  recalc();
});

})();
