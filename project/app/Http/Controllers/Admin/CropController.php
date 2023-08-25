<?php

namespace App\Http\Controllers\Admin;

use App\Models\Crop;
use Illuminate\Http\Request;
use Validator;
use Datatables;

class CropController extends AdminBaseController
{
    //*** JSON Request
    public function datatables()
    {
         $datas = Crop::latest('id')->get();
         return Datatables::of($datas)
                            ->addColumn('status', function(Crop $data) {
                                $class = $data->status == 1 ? 'drop-success' : 'drop-danger';
                                $s = $data->status == 1 ? 'selected' : '';
                                $ns = $data->status == 0 ? 'selected' : '';
                                return '<div class="action-list"><select class="process select droplinks '.$class.'"><option data-val="1" value="'. route('admin-crop-status',['id1' => $data->id, 'id2' => 1]).'" '.$s.'>'.__("Activated").'</option><option data-val="0" value="'. route('admin-crop-status',['id1' => $data->id, 'id2' => 0]).'" '.$ns.'>'.__("Deactivated").'</option>/select></div>';
                            })
                            ->addColumn('action', function(Crop $data) {
                                return '<div class="action-list"><a data-href="' . route('admin-crop-edit',$data->id) . '" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>'.__('Edit').'</a><a href="javascript:;" data-href="' . route('admin-crop-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
                            })
                            ->addColumn('icon', function(Crop $data) {
                                if(!empty($data->icon)){
                                    return '<img height="65px" src="/assets/images/crops/'.$data->icon.'">';
                                }
                                else{
                                    return '<img height="65px">';
                                }
                            })
                            ->rawColumns(['status','icon','attributes','action'])
                            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function index(){
        return view('admin.crops.index');
    }

    public function create(){
        return view('admin.crops.create');
    }
    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = [
            'icon' => 'mimes:jpeg,jpg,png,svg',
            'slug' => 'unique:crops|regex:/^[a-zA-Z0-9\s-]+$/',
            'name' => 'required'
                 ];
        $customs = [
            'icon.mimes' => __('Icon Type is Invalid.'),
            'slug.unique' => __('This slug has already been taken.'),
            'name.required' => __('Crop name is required.'),
            'slug.regex' => __('Slug Must Not Have Any Special Characters.')
                   ];
        $validator = Validator::make($request->all(), $rules, $customs);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new Crop();
        $input = $request->all();
        if ($file = $request->file('icon'))
         {
            $name = \PriceHelper::ImageCreateName($file);
            $file->move('assets/images/crops',$name);
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
        $data = Crop::findOrFail($id);
        return view('admin.crops.edit',compact('data'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        $rules = [
        	'icon' => 'mimes:jpeg,jpg,png,svg',
            'slug' => 'unique:crops,slug,'.$id.'|regex:/^[a-zA-Z0-9\s-]+$/'
        		 ];
        $customs = [
            'icon.mimes' => __('Icon Type is Invalid.'),
            'slug.unique' => __('This slug has already been taken.'),
            'slug.regex' => __('Slug Must Not Have Any Special Characters.')
        		   ];
        $validator = Validator::make($request->all(), $rules, $customs);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = Crop::findOrFail($id);
        $input = $request->all();
            if ($file = $request->file('icon'))
            {
                $name = \PriceHelper::ImageCreateName($file);
                $file->move('assets/images/crops',$name);
                if($data->icon != null)
                {
                    if (file_exists(public_path().'/assets/images/crops/'.$data->icon)) {
                        unlink(public_path().'/assets/images/crops/'.$data->icon);
                    }
                }
            $input['icon'] = $name;
            }
            if ($file = $request->file('image'))
            {
                $name = \PriceHelper::ImageCreateName($file);
                $file->move('assets/images/crops',$name);
                $input['image'] = $name;
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
          $data = Crop::findOrFail($id1);
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
        $data = Crop::findOrFail($id);

        if (file_exists(public_path().'/assets/images/crops/'.$data->icon)) {
            unlink(public_path().'/assets/images/crops/'.$data->icon);
        }
        $data->delete();
        //--- Redirect Section
        $msg = __('Data Deleted Successfully.');
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}