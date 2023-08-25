<?php

namespace App\Http\Controllers\Admin;

use App\Models\Dukan;
use App\Models\Category;
use Illuminate\Http\Request;
use Validator;
use Datatables;

class DukanController extends AdminBaseController
{
    //*** JSON Request
    public function datatables()
    {
         $datas = Dukan::latest('id')->get();
         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
                            ->addColumn('status', function(Dukan $data) {
                                $class = $data->status == 1 ? 'drop-success' : 'drop-danger';
                                $s = $data->status == 1 ? 'selected' : '';
                                $ns = $data->status == 0 ? 'selected' : '';
                                return '<div class="action-list"><select class="process select droplinks '.$class.'"><option data-val="1" value="'. route('admin-dukan-status',['id1' => $data->id, 'id2' => 1]).'" '.$s.'>'.__("Activated").'</option><option data-val="0" value="'. route('admin-dukan-status',['id1' => $data->id, 'id2' => 0]).'" '.$ns.'>'.__("Deactivated").'</option>/select></div>';
                            })
                            ->addColumn('action', function(Dukan $data) {
                                return '<div class="action-list"><a data-href="' . route('admin-dukan-edit',$data->id) . '" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>'.__('Edit').'</a><a href="javascript:;" data-href="' . route('admin-dukan-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
                            })
                            ->addColumn('icon', function(Dukan $data) {
                                if(!empty($data->icon)){
                                    return '<img height="65px" src="/assets/images/dukan/'.$data->icon.'">';
                                }
                                else{
                                    return '<img height="65px">';
                                }
                            })
                            ->rawColumns(['status','icon','action'])
                            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function index(){
        return view('admin.dukans.index');
    }

    public function create(){
        $cats = Category::all();
        return view('admin.dukans.create',compact('cats'));
    }
    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = [
                    'icon' => 'mimes:jpeg,jpg,png,svg',
                    'gst_number' => 'unique:dukans|regex:/^[a-zA-Z0-9\s-]+$/',
                    'shop_name' => 'required',
                    'pan_number' => 'required',
                    'contact_number' => 'required',
                    'address' => 'required',
                    'category_id' => 'required',
                    'owner_name' => 'required'
                ];
        $customs = [
                    'icon.mimes' => __('Icon Type is Invalid.'),
                    'gst_number.unique' => __('This GST Number has already been taken.'),
                    'gst_number.regex' => __('GST Number Must Not Have Any Special Characters.'),
                    'shop_name.required' => __('Dukan name is required.'),
                    'pan_number.required' => __('PAN number is required.'),
                    'contact_number.required' => __('Contact number is required.'),
                    'address.required' => __('Address is required.'),
                    'category_id.required' => __('category is required.')
                   ];
        $validator = Validator::make($request->all(), $rules, $customs);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new Dukan();
        $input = $request->all();
        if ($file = $request->file('icon'))
         {
            $name = \PriceHelper::ImageCreateName($file);
            $file->move('assets/images/dukan',$name);
            $input['icon'] = $name;
        }
        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = __('New Data Added Successfully.');
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request
    public function edit($id)
    {
        $cats = Category::all();
        $data = Dukan::findOrFail($id);
        return view('admin.dukans.edit',compact('data', 'cats'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        $rules = [
                    'icon' => 'mimes:jpeg,jpg,png,svg',
                    'gst_number' => 'unique:dukans,slug,'.$id.'|regex:/^[a-zA-Z0-9\s-]+$/',
                    'shop_name' => 'required',
                    'pan_number' => 'required',
                    'contact_number' => 'required',
                    'address' => 'required',
                    'category_id' => 'required',
                    'owner_name' => 'required'
                ];
        $customs = [
                    'icon.mimes' => __('Icon Type is Invalid.'),
                    'gst_number.unique' => __('This GST Number has already been taken.'),
                    'gst_number.regex' => __('GST Number Must Not Have Any Special Characters.'),
                    'shop_name.required' => __('Dukan name is required.'),
                    'pan_number.required' => __('PAN number is required.'),
                    'contact_number.required' => __('Contact number is required.'),
                    'address.required' => __('Address is required.'),
                    'category_id.required' => __('category is required.')
                   ];
        $validator = Validator::make($request->all(), $rules, $customs);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = Dukan::findOrFail($id);
        $input = $request->all();
        if ($file = $request->file('icon'))
        {
            $name = \PriceHelper::ImageCreateName($file);
            $file->move('assets/images/dukan',$name);
            if($data->icon != null)
            {
                if (file_exists(public_path().'/assets/images/dukan/'.$data->icon)) {
                    unlink(public_path().'/assets/images/dukan/'.$data->icon);
                }
            }
            $input['icon'] = $name;
        }            
        $data->update($input);
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = __('Data Updated Successfully.');
        return response()->json($msg);
        //--- Redirect Section Ends
    }

      //*** GET Request Status
      public function status($id1,$id2)
      {
          $data = Dukan::findOrFail($id1);
          $data->status = $id2;
          $data->update();
          //--- Redirect Section
          $msg = __('Status Updated Successfully.');
          return response()->json($msg);
          //--- Redirect Section Ends
      }


    //*** GET Request Delete
    public function destroy($id)
    {
        $data = Dukan::findOrFail($id);

        if (file_exists(public_path().'/assets/images/dukan/'.$data->icon)) {
            unlink(public_path().'/assets/images/dukan/'.$data->icon);
        }
        $data->delete();
        //--- Redirect Section
        $msg = __('Data Deleted Successfully.');
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}