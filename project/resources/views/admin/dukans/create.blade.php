@extends('layouts.load')
@section('content')
    <div class="content-area">
      <div class="add-product-content1">
        <div class="row">
          <div class="col-lg-12">
            <div class="product-description">
              <div class="body-area">
                @include('alerts.admin.form-error')  
                <form id="geniusformdata" action="{{route('admin-dukan-create')}}" method="POST" enctype="multipart/form-data">
                  {{csrf_field()}}
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="left-area">
                        <h4 class="heading">{{ __('Select Language') }}*</h4>
                      </div>
                    </div>
                    <div class="col-lg-7">
                      <select name="language_id" required="">
                          @foreach(DB::table('languages')->get() as $ldata)
                            <option value="{{ $ldata->id }}">{{ $ldata->language }}</option>
                          @endforeach
                        </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="left-area">
                          <h4 class="heading">{{ __('Name') }} *</h4>
                      </div>
                    </div>
                    <div class="col-lg-7">
                      <input type="text" class="input-field" name="shop_name" placeholder="{{ __('Enter Name') }}" required="" value="">
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="left-area">
                          <h4 class="heading">{{ __('Owner Name') }} *</h4>
                      </div>
                    </div>
                    <div class="col-lg-7">
                      <input type="text" class="input-field" name="owner_name" placeholder="{{ __('Enter Owner Name') }}" required="" value="">
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="left-area">
                          <h4 class="heading">{{ __('PAN Number') }} *</h4>
                      </div>
                    </div>
                    <div class="col-lg-7">
                      <input type="text" class="input-field" name="pan_number" placeholder="{{ __('Enter PAN Number') }}" required="" value="">
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="left-area">
                          <h4 class="heading">{{ __('Contact Number') }} *</h4>
                      </div>
                    </div>
                    <div class="col-lg-7">
                      <input type="text" class="input-field" name="contact_number" placeholder="{{ __('Enter Contact Number') }}" required="" value="">
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="left-area">
                          <h4 class="heading">{{ __('Address') }} *</h4>
                      </div>
                    </div>
                    <div class="col-lg-7">
                      <textarea class="form-control" name="address" placeholder="{{ __('Enter Address') }}" required=""></textarea>
                      <input type="text" style="display: none;" class="input-field" name="latitude" value="00000">
                      <input type="text" style="display: none;" class="input-field" name="longitude" value="00000">
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="left-area">
                          <h4 class="heading">{{ __('GST Number') }} *</h4>
                      </div>
                    </div>
                    <div class="col-lg-7">
                      <input type="text" class="input-field" name="gst_number" placeholder="{{ __('Enter GST Number') }}" required="" value="">
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="left-area">
                          <h4 class="heading">{{ __("Category") }}*</h4>
                      </div>
                    </div>
                    <div class="col-lg-7">
                        <select  name="category_id" required="">
                          <option value="">{{ __("Select Category") }}</option>
                          @foreach($cats as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                          @endforeach
                        </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="left-area">
                          <h4 class="heading">{{ __('Set Icon') }} *</h4>
                      </div>
                    </div>
                    <div class="col-lg-7">
                      <div class="img-upload">
                          <div id="image-preview" class="img-preview" style="background: url({{ asset('assets/admin/images/upload.png') }});">
                              <label for="image-upload" class="img-label" id="image-label"><i class="icofont-upload-alt"></i>{{ __('Upload Icon') }}</label>
                              <input type="file" name="icon" class="img-upload" id="image-upload">
                            </div>
                      </div>
                    </div>
                  </div>
                  <br>
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="left-area">
                      </div>
                    </div>
                    <div class="col-lg-7">
                      <button class="addProductSubmit-btn" type="submit">{{ __('Create Category') }}</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
@endsection