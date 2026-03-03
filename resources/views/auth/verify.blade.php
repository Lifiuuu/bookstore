@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">{{ __('Enter OTP to Verify') }}</div>

                <div class="card-body">
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p>Please enter the 6-digit OTP sent to your email to verify your account.</p>

                    <form method="POST" action="{{ route('otp.verify') }}">
                        @csrf

                        <div class="form-group">
                            <label for="otp">OTP Code</label>
                            <input id="otp" type="text" class="form-control" name="otp" value="{{ old('otp') }}" required maxlength="6" pattern="\d{6}" autofocus>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Verify OTP</button>
                            <a href="{{ route('login') }}" class="btn btn-link">Back to login</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
