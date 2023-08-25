@extends('layouts.admin')

@section('content')

          <div class="content-area">

            <div class="mr-breadcrumb">

              <div class="row">

                <div class="col-lg-12">

                    <h4 class="heading">{{ __('App Version') }}</h4>

                    <ul class="links">

                      <li>

                        <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>

                      </li>

                      <li>

                        <a href="javascript:;">{{ __('General Settings') }}</a>

                      </li>

                      <li>

                        <a href="{{ route('admin-gs-app-version') }}">{{ __('App Version') }}</a>

                      </li>

                    </ul>

                </div>

              </div>

            </div>

            <div class="add-logo-area">

              <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>

              <div class="row">

                <div class="col-lg-12">



                        @include('alerts.admin.form-both')  



                  <form class="uplogo-form" id="geniusform"  action="{{ route('admin-gs-update') }}" method="POST" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <div class="set-logo">
                      <div class="row">
                        <div class="col-lg-6">
                          <h4 class="title">
                            {{ __('App Version') }} :
                          </h4>                          
                        </div>
                        <div class="col-lg-6">
                          <input class="form-control" type="text" name="app_version" maxlength="5" value="{{ $gs->app_version }}">
                        </div>
                      </div>
                    </div>
                    <div class="submit-area">
                      <button type="submit" class="submit-btn">{{ __('Save') }}</button>
                    </div>
                  </form>
                </div>

              </div>

            </div>

          </div>



@endsection