@extends('layouts.app')

@section('title', $business->name)
@section('body_class', 'min-vh-100 bg-light')

@push('head')
    <style>
        .bazrgan-page {
            min-height: 100vh;
            background: #f5f7fb;
            color: #182033;
        }

        .bazrgan-shell {
            width: min(1080px, calc(100% - 32px));
            margin: 0 auto;
            padding: 48px 0;
        }

        .bazrgan-header {
            display: grid;
            gap: 12px;
            margin-bottom: 28px;
        }

        .bazrgan-title {
            margin: 0;
            font-size: clamp(2rem, 5vw, 4.25rem);
            line-height: 1.02;
            font-weight: 800;
            letter-spacing: 0;
        }

        .bazrgan-subtitle {
            max-width: 680px;
            margin: 0;
            color: #5d6678;
            font-size: 1rem;
            line-height: 1.7;
        }

        .bazrgan-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            padding: 24px;
            background: #ffffff;
            border: 1px solid #e2e7f0;
            border-radius: 8px;
            box-shadow: 0 20px 55px rgba(24, 32, 51, 0.08);
        }

        .field {
            display: grid;
            gap: 8px;
        }

        .field-full {
            grid-column: 1 / -1;
        }

        .field label {
            font-weight: 700;
            font-size: 0.92rem;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid #ccd4e1;
            border-radius: 6px;
            padding: 12px 14px;
            font: inherit;
            color: #182033;
            background: #ffffff;
        }

        .field textarea {
            min-height: 140px;
            resize: vertical;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: 3px solid rgba(35, 116, 225, 0.18);
            border-color: #2374e1;
        }

        .error {
            color: #b42318;
            font-size: 0.85rem;
        }

        .alert {
            padding: 14px 16px;
            margin-bottom: 18px;
            border-radius: 8px;
            background: #ecfdf3;
            border: 1px solid #abefc6;
            color: #067647;
            font-weight: 700;
        }

        .submit-row {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
        }

        .submit-button {
            border: 0;
            border-radius: 6px;
            background: #2374e1;
            color: #ffffff;
            padding: 13px 22px;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }

        .submit-button:hover {
            background: #185cb8;
        }

        @media (max-width: 720px) {
            .bazrgan-shell {
                width: min(100% - 24px, 1080px);
                padding: 28px 0;
            }

            .bazrgan-form {
                grid-template-columns: 1fr;
                padding: 18px;
            }

            .submit-row {
                justify-content: stretch;
            }

            .submit-button {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <main class="bazrgan-page">
        <div class="bazrgan-shell">
            <header class="bazrgan-header">
                <h1 class="bazrgan-title">{{ $business->name }}</h1>
                <p class="bazrgan-subtitle">Submit a public request with an image. The selected team member will receive it as a task.</p>
            </header>

            @if (session('success'))
                <div class="alert" role="alert">{{ session('success') }}</div>
            @endif

            <form class="bazrgan-form" method="POST" action="{{ route('public.business.store', ['businessName' => $businessSlug]) }}" enctype="multipart/form-data">
                @csrf

                <div class="field field-full">
                    <label for="assigned_to">Assign this task to</label>
                    <select id="assigned_to" name="assigned_to" required>
                        <option value="">Select a user</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected((string) old('assigned_to') === (string) $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="customer_name">Your name</label>
                    <input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
                    @error('customer_name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="date">Date</label>
                    <input id="date" type="date" name="date" value="{{ old('date') }}" required>
                    @error('date')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="time">Time</label>
                    <input id="time" type="time" name="time" value="{{ old('time') }}" required>
                    @error('time')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field field-full">
                    <label for="image">Image</label>
                    <input id="image" type="file" name="image" accept="image/*" required>
                    @error('image')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field field-full">
                    <label for="note">Note</label>
                    <textarea id="note" name="note">{{ old('note') }}</textarea>
                    @error('note')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="submit-row">
                    <button class="submit-button" type="submit">Submit request</button>
                </div>
            </form>
        </div>
    </main>
@endsection
