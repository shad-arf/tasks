@extends('layouts.app')

@section('title', 'پەڕەی مانیجەر')
@section('html_lang', 'ckb')
@section('dir', 'rtl')
@section('body_class', 'manager-bootstrap-page bg-body-tertiary py-4')

@push('head')
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
        crossorigin="anonymous"
    >
    <style>
        .manager-shell {
            max-width: 1200px;
        }

        .manager-hero {
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.2), transparent 28%),
                linear-gradient(135deg, #07111f 0%, #10233b 52%, #163e72 100%);
            color: #f8fafc;
            border: 1px solid rgba(148, 163, 184, 0.18);
        }

        .manager-hero .text-secondary-soft {
            color: rgba(226, 232, 240, 0.82);
        }

        .manager-stat-card {
            min-width: 210px;
            border: 1px solid #e2e8f0;
        }

        .manager-table-wrap {
            border: 1px solid #e2e8f0;
        }

        .manager-table th {
            white-space: nowrap;
            font-size: 0.82rem;
            color: #64748b;
        }

        .manager-table td {
            vertical-align: middle;
        }

        .manager-table .user-name {
            font-weight: 700;
            color: #0f172a;
        }

        .manager-table .user-subline {
            font-size: 0.84rem;
            color: #64748b;
        }

        .manager-modal .modal-content {
            border: 0;
            border-radius: 1.25rem;
            box-shadow: 0 32px 80px rgba(15, 23, 42, 0.22);
        }

        .manager-modal .modal-header,
        .manager-modal .modal-footer {
            border-color: #e2e8f0;
        }

        .manager-modal .form-label {
            font-weight: 600;
            color: #334155;
        }

        .manager-toolbar {
            gap: 0.75rem;
        }
    </style>
@endpush

@section('content')
    @php($createErrors = $errors->createUser)
    @php($updateErrors = $errors->updateUser)
    @php($failedUserId = (string) old('user_id'))
    @php($createBusinessName = old('business_name', $businesses->first()?->name))

    <div class="manager-shell container-fluid px-3 px-lg-4">
        <div class="manager-hero rounded-5 p-4 p-lg-5 shadow-lg">
            <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-4">
                <div class="col-lg-7 px-0">
                    <span class="badge rounded-pill text-bg-light text-dark px-3 py-2 fw-semibold">Manager Panel</span>
                    <h1 class="mt-3 mb-2 display-6 fw-bold">بەڕێوەبردنی بەکارهێنەران</h1>
                    <p class="mb-0 fs-6 text-secondary-soft">
                        پەڕەکە نوێکراوەتەوە بۆ خێراتر بینینی داتا. ئامارەکان لەسەرەوەن،
                        بەکارهێنەران لە خشتەدان و زیادکردن و نوێکردنەوەش بە modal ئەنجام دەدرێت.
                    </p>
                </div>

                <div class="d-flex flex-column align-items-stretch manager-toolbar">
                    <button
                        type="button"
                        class="btn btn-light btn-lg fw-semibold px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#createUserModal"
                    >
                        زیادکردنی بەکارهێنەری نوێ
                    </button>

                    <div class="d-flex align-items-center justify-content-between rounded-4 border border-light border-opacity-10 bg-white bg-opacity-10 px-3 py-3">
                        <div class="text-end">
                            <div class="fw-semibold">{{ $currentUser?->name }}</div>
                            <div class="small text-secondary-soft">{{ $currentUser?->username }}</div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm px-3">
                                چوونەدەرەوە
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 overflow-auto py-4">
            <div class="manager-stat-card card rounded-4 shadow-sm flex-shrink-0">
                <div class="card-body">
                    <div class="text-uppercase text-secondary fw-semibold small">All Users</div>
                    <div class="display-6 fw-bold mt-2 mb-0 text-dark">{{ $stats['total_users'] }}</div>
                </div>
            </div>

            <div class="manager-stat-card card rounded-4 shadow-sm flex-shrink-0">
                <div class="card-body">
                    <div class="text-uppercase text-secondary fw-semibold small">Managers</div>
                    <div class="display-6 fw-bold mt-2 mb-0 text-dark">{{ $stats['total_managers'] }}</div>
                </div>
            </div>

            <div class="manager-stat-card card rounded-4 shadow-sm flex-shrink-0">
                <div class="card-body">
                    <div class="text-uppercase text-secondary fw-semibold small">Users</div>
                    <div class="display-6 fw-bold mt-2 mb-0 text-dark">{{ $stats['total_regular_users'] }}</div>
                </div>
            </div>

            <div class="manager-stat-card card rounded-4 shadow-sm flex-shrink-0">
                <div class="card-body">
                    <div class="text-uppercase text-secondary fw-semibold small">Businesses</div>
                    <div class="display-6 fw-bold mt-2 mb-0 text-dark">{{ $stats['total_businesses'] }}</div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success rounded-4 border-0 shadow-sm" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger rounded-4 border-0 shadow-sm" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="card rounded-5 border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-4 px-lg-5 py-4 border-bottom">
                    <div>
                        <h2 class="h4 mb-1 fw-bold text-dark">لیستی بەکارهێنەران</h2>
                        <p class="mb-0 text-secondary">
                            خشتەدان بۆ بینینی خێرا و دەستکارییەکی ڕێکخراو.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn btn-primary px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#createUserModal"
                    >
                        زیادکردنی user
                    </button>
                </div>

                <div class="manager-table-wrap table-responsive rounded-bottom-5">
                    <table class="manager-table table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">بەکارهێنەر</th>
                                <th class="px-4 py-3">Username</th>
                                <th class="px-4 py-3">ئیمەیڵ</th>
                                <th class="px-4 py-3">مۆبایل</th>
                                <th class="px-4 py-3">Business</th>
                                <th class="px-4 py-3">ڕۆڵ</th>
                                <th class="px-4 py-3">دروستکراو</th>
                                <th class="px-4 py-3 text-center">کردارەکان</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $managedUser)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="user-name">{{ $managedUser->name }}</div>
                                        <div class="user-subline">ID: {{ $managedUser->id }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="fw-semibold text-dark">{{ $managedUser->username }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-secondary">
                                        {{ $managedUser->email ?: '---' }}
                                    </td>
                                    <td class="px-4 py-3 text-secondary">
                                        {{ $managedUser->phone ?: '---' }}
                                    </td>
                                    <td class="px-4 py-3 text-secondary">
                                        {{ $managedUser->business?->name ?: '---' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge rounded-pill {{ $managedUser->role === 'manager' ? 'text-bg-primary' : 'text-bg-secondary' }}">
                                            {{ $managedUser->role }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-secondary">
                                        {{ optional($managedUser->created_at)->format('Y-m-d') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUserModal-{{ $managedUser->id }}"
                                            >
                                                نوێکردنەوە
                                            </button>

                                            <form method="POST" action="{{ route('admin.users.destroy', $managedUser) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-outline-danger btn-sm"
                                                    @disabled($currentUser?->id === $managedUser->id)
                                                >
                                                    سڕینەوە
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-5 text-center text-secondary">
                                        هیچ بەکارهێنەرێک نەدۆزرایەوە.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <datalist id="business-options">
        @foreach ($businesses as $business)
            <option value="{{ $business->name }}"></option>
        @endforeach
    </datalist>

    <div class="modal fade manager-modal" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf

                    <div class="modal-header px-4 py-3">
                        <div>
                            <h2 class="modal-title fs-4 fw-bold text-dark" id="createUserModalLabel">زیادکردنی بەکارهێنەری نوێ</h2>
                            <p class="mb-0 text-secondary small">زانیارییە بنەڕەتییەکان بنووسە و save بکە.</p>
                        </div>
                        <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body px-4 py-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="create-name" class="form-label">ناو</label>
                                <input
                                    id="create-name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name') }}"
                                    class="form-control form-control-lg @if ($createErrors->has('name')) is-invalid @endif"
                                >
                                @if ($createErrors->has('name'))
                                    <div class="invalid-feedback">{{ $createErrors->first('name') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="create-username" class="form-label">Username</label>
                                <input
                                    id="create-username"
                                    name="username"
                                    type="text"
                                    value="{{ old('username') }}"
                                    class="form-control form-control-lg @if ($createErrors->has('username')) is-invalid @endif"
                                >
                                @if ($createErrors->has('username'))
                                    <div class="invalid-feedback">{{ $createErrors->first('username') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="create-email" class="form-label">ئیمەیڵ</label>
                                <input
                                    id="create-email"
                                    name="email"
                                    type="text"
                                    value="{{ old('email') }}"
                                    class="form-control form-control-lg @if ($createErrors->has('email')) is-invalid @endif"
                                >
                                @if ($createErrors->has('email'))
                                    <div class="invalid-feedback">{{ $createErrors->first('email') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="create-phone" class="form-label">ژمارەی مۆبایل</label>
                                <input
                                    id="create-phone"
                                    name="phone"
                                    type="text"
                                    value="{{ old('phone') }}"
                                    class="form-control form-control-lg @if ($createErrors->has('phone')) is-invalid @endif"
                                    placeholder="9647501234567"
                                >
                                <div class="form-text">بە country code ـەوە تۆماری بکە.</div>
                                @if ($createErrors->has('phone'))
                                    <div class="invalid-feedback">{{ $createErrors->first('phone') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="create-role" class="form-label">ڕۆڵ</label>
                                <select
                                    id="create-role"
                                    name="role"
                                    class="form-select form-select-lg @if ($createErrors->has('role')) is-invalid @endif"
                                >
                                    <option value="user" @selected(old('role', 'user') === 'user')>user</option>
                                    <option value="manager" @selected(old('role') === 'manager')>manager</option>
                                </select>
                                @if ($createErrors->has('role'))
                                    <div class="invalid-feedback">{{ $createErrors->first('role') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="create-business-name" class="form-label">Business</label>
                                <input
                                    id="create-business-name"
                                    name="business_name"
                                    type="text"
                                    list="business-options"
                                    value="{{ $createBusinessName }}"
                                    class="form-control form-control-lg @if ($createErrors->has('business_name')) is-invalid @endif"
                                    placeholder="Business A"
                                >
                                <div class="form-text">هەمان ناو بنووسە بۆ business ـی هەبوو، یان ناوی نوێ بنووسە بۆ دروستکردنی business ـێکی نوێ.</div>
                                @if ($createErrors->has('business_name'))
                                    <div class="invalid-feedback">{{ $createErrors->first('business_name') }}</div>
                                @endif
                            </div>

                            <div class="col-12">
                                <label for="create-password" class="form-label">وشەی نهێنی</label>
                                <input
                                    id="create-password"
                                    name="password"
                                    type="password"
                                    class="form-control form-control-lg @if ($createErrors->has('password')) is-invalid @endif"
                                >
                                <div class="form-text">هەر پاسووردێک وەک `123`یش قبوڵ دەکرێت.</div>
                                @if ($createErrors->has('password'))
                                    <div class="invalid-feedback">{{ $createErrors->first('password') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer px-4 py-3">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">داخستن</button>
                        <button type="submit" class="btn btn-primary px-4">زیادکردنی بەکارهێنەر</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($users as $managedUser)
        @php($isFailedRow = $failedUserId === (string) $managedUser->id)
        @php($rowName = $isFailedRow ? old('name', $managedUser->name) : $managedUser->name)
        @php($rowUsername = $isFailedRow ? old('username', $managedUser->username) : $managedUser->username)
        @php($rowEmail = $isFailedRow ? old('email', $managedUser->email) : $managedUser->email)
        @php($rowPhone = $isFailedRow ? old('phone', $managedUser->phone) : $managedUser->phone)
        @php($rowRole = $isFailedRow ? old('role', $managedUser->role) : $managedUser->role)
        @php($rowBusinessName = $isFailedRow ? old('business_name', $managedUser->business?->name) : $managedUser->business?->name)

        <div class="modal fade manager-modal" id="editUserModal-{{ $managedUser->id }}" tabindex="-1" aria-labelledby="editUserModalLabel-{{ $managedUser->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.users.update', $managedUser) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="user_id" value="{{ $managedUser->id }}">

                        <div class="modal-header px-4 py-3">
                            <div>
                                <h2 class="modal-title fs-4 fw-bold text-dark" id="editUserModalLabel-{{ $managedUser->id }}">
                                    نوێکردنەوەی {{ $managedUser->name }}
                                </h2>
                                <p class="mb-0 text-secondary small">زانیارییەکان بگۆڕە و save بکە.</p>
                            </div>
                            <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body px-4 py-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="edit-name-{{ $managedUser->id }}" class="form-label">ناو</label>
                                    <input
                                        id="edit-name-{{ $managedUser->id }}"
                                        name="name"
                                        type="text"
                                        value="{{ $rowName }}"
                                        class="form-control form-control-lg @if ($isFailedRow && $updateErrors->has('name')) is-invalid @endif"
                                    >
                                    @if ($isFailedRow && $updateErrors->has('name'))
                                        <div class="invalid-feedback">{{ $updateErrors->first('name') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="edit-username-{{ $managedUser->id }}" class="form-label">Username</label>
                                    <input
                                        id="edit-username-{{ $managedUser->id }}"
                                        name="username"
                                        type="text"
                                        value="{{ $rowUsername }}"
                                        class="form-control form-control-lg @if ($isFailedRow && $updateErrors->has('username')) is-invalid @endif"
                                    >
                                    @if ($isFailedRow && $updateErrors->has('username'))
                                        <div class="invalid-feedback">{{ $updateErrors->first('username') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="edit-email-{{ $managedUser->id }}" class="form-label">ئیمەیڵ</label>
                                    <input
                                        id="edit-email-{{ $managedUser->id }}"
                                        name="email"
                                        type="text"
                                        value="{{ $rowEmail }}"
                                        class="form-control form-control-lg @if ($isFailedRow && $updateErrors->has('email')) is-invalid @endif"
                                    >
                                    @if ($isFailedRow && $updateErrors->has('email'))
                                        <div class="invalid-feedback">{{ $updateErrors->first('email') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="edit-phone-{{ $managedUser->id }}" class="form-label">ژمارەی مۆبایل</label>
                                    <input
                                        id="edit-phone-{{ $managedUser->id }}"
                                        name="phone"
                                        type="text"
                                        value="{{ $rowPhone }}"
                                        class="form-control form-control-lg @if ($isFailedRow && $updateErrors->has('phone')) is-invalid @endif"
                                        placeholder="9647501234567"
                                    >
                                    <div class="form-text">بە country code ـەوە تۆماری بکە.</div>
                                    @if ($isFailedRow && $updateErrors->has('phone'))
                                        <div class="invalid-feedback">{{ $updateErrors->first('phone') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="edit-role-{{ $managedUser->id }}" class="form-label">ڕۆڵ</label>
                                    <select
                                        id="edit-role-{{ $managedUser->id }}"
                                        name="role"
                                        class="form-select form-select-lg @if ($isFailedRow && $updateErrors->has('role')) is-invalid @endif"
                                    >
                                        <option value="user" @selected($rowRole === 'user')>user</option>
                                        <option value="manager" @selected($rowRole === 'manager')>manager</option>
                                    </select>
                                    @if ($isFailedRow && $updateErrors->has('role'))
                                        <div class="invalid-feedback">{{ $updateErrors->first('role') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="edit-business-name-{{ $managedUser->id }}" class="form-label">Business</label>
                                    <input
                                        id="edit-business-name-{{ $managedUser->id }}"
                                        name="business_name"
                                        type="text"
                                        list="business-options"
                                        value="{{ $rowBusinessName }}"
                                        class="form-control form-control-lg @if ($isFailedRow && $updateErrors->has('business_name')) is-invalid @endif"
                                        placeholder="Business A"
                                    >
                                    <div class="form-text">دەتوانیت business ـێکی هەبوو هەڵبژێریت یان ناوی نوێ بنووسیت.</div>
                                    @if ($isFailedRow && $updateErrors->has('business_name'))
                                        <div class="invalid-feedback">{{ $updateErrors->first('business_name') }}</div>
                                    @endif
                                </div>

                                <div class="col-12">
                                    <label for="edit-password-{{ $managedUser->id }}" class="form-label">نوێکردنەوەی وشەی نهێنی</label>
                                    <input
                                        id="edit-password-{{ $managedUser->id }}"
                                        name="password"
                                        type="password"
                                        class="form-control form-control-lg @if ($isFailedRow && $updateErrors->has('password')) is-invalid @endif"
                                    >
                                    <div class="form-text">ئەگەر بەتاڵی بهێڵیت، وشەی نهێنی پێشووی دەمێنێتەوە.</div>
                                    @if ($isFailedRow && $updateErrors->has('password'))
                                        <div class="invalid-feedback">{{ $updateErrors->first('password') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer px-4 py-3">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">داخستن</button>
                            <button type="submit" class="btn btn-primary px-4">save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"
    ></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($createErrors->any())
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createUserModal')).show();
            @endif

            @if ($updateErrors->any() && $failedUserId !== '')
                bootstrap.Modal.getOrCreateInstance(
                    document.getElementById('editUserModal-{{ $failedUserId }}')
                ).show();
            @endif
        });
    </script>
@endpush
