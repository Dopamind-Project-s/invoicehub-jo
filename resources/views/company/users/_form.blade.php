@csrf
<div class="row g-3" dir="rtl">
    <div class="col-md-6"><label class="form-label">الاسم</label><input name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
    <div class="col-md-6"><label class="form-label">البريد الإلكتروني</label><input name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
    <div class="col-md-4"><label class="form-label">الهاتف</label><input name="phone" class="form-control" value="{{ old('phone', $user->phone) }}"></div>
    <div class="col-md-4"><label class="form-label">الحالة</label><select name="status" class="form-select"><option value="active" @selected(old('status', $user->status ?: 'active') === 'active')>نشط</option><option value="inactive" @selected(old('status', $user->status) === 'inactive')>غير نشط</option><option value="suspended" @selected(old('status', $user->status) === 'suspended')>معلق</option></select></div>
    <div class="col-md-4"><label class="form-label">كلمة المرور</label><input name="password" type="password" class="form-control" @if(! $user->exists) required @endif></div>
</div>
<hr>
<h2 class="h5">الأدوار</h2>
<div class="row g-2">
    @foreach($roles as $role)
        <div class="col-md-3"><label class="form-check border rounded p-3"><input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, old('roles', $assignedRoleIds ?? [])))> <span>{{ $role->name }}</span></label></div>
    @endforeach
</div>
<button class="btn btn-primary mt-4">حفظ</button>
