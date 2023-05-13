@extends('layouts.app')

@section('content')
    <main class="py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Reset Password</div>

                        <div class="card-body">
{{--                            @include('includes.notification')--}}
                            <form method="POST" action="{{ config('app.url') }}/password">
{{--                                {{ csrf_field() }}--}}

                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="form-group row">
                                    <label for="email" class="col-md-4 col-form-label text-md-right">E-Mail Address</label>

                                    <div class="col-md-6">
                                        <input
                                            id="email"
                                            type="email"
                                            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                            name="email"
                                            required
                                            autocomplete="email"
                                            minlength="6"
                                            value="{{ old('email', '') }}">

                                        @if($errors->has('email'))
                                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('email') }}</strong>
                                </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>

                                    <div class="col-md-6">
                                        <input
                                            id="password"
                                            type="password"
                                            class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                            name="password"
                                            required
                                            autocomplete="password"
                                            minlength="6"
                                            value="{{ old('password', '') }}">

                                        @if($errors->has('password'))
                                            <span class="invalid-feedback" role="alert">
                                <strong>{{  $errors->first('password') }}</strong>
                                </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="password_confirmation" class="col-md-4 col-form-label text-md-right">Confirm Password</label>

                                    <div class="col-md-6">
                                        <input
                                            id="password_confirmation"
                                            type="password"
                                            class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                                            name="password_confirmation"
                                            required
                                            autocomplete="password_confirmation"
                                            value="{{ old('password_confirmation', '') }}">
                                    </div>
                                    @if($errors->has('password_confirmation'))
                                        <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('password_confirmation') }} }}</strong>
                                </span>
                                    @endif
                                </div>

                                <div class="form-group row mb-0">
                                    <div class="col-md-8 offset-md-4">
                                        <button type="submit" class="btn btn-primary">
                                            Reset password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
