<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['post'] = Post::all();

        return response()->json([
            'status' => true,
            'message' => "All Post Data",
            'data' => $data,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'description' => 'required',
                'image' => 'required|mimes:png,jpg,jpeg,gif'
            ]
        );

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => "Validation Error",
                'errors' => $validateUser->errors()->all()
            ], 401);
        }

        $img = $request->image;
        $ext = $img->getClientOriginalExtension();
        $image_name = time(). '.' .$ext;
        $img->move(public_path() . '/uploads/' . $image_name);

        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $image_name,
        ]);

        if($post){
            return response()->json([
                'status' => true,
                'message' => "Post Created Successfully",
                'post' => $post
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data['post'] = Post::select('id', 'title', 'description', 'image')->where(['id' => $id])->get();

        return response()->json([
            'status' => true,
            'message' => "Your Single Post",
            'data' => $data
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'description' => 'required',
                'image' => 'required|mimes:png,jpg,jpeg,gif'
            ]
        );

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => "Validation Error",
                'errors' => $validateUser->errors()->all()
            ], 401);
        }

        $post = Post::select('id', 'image')->where(['id' => $id])->first();

        // Checking image is uploaded or not
        if($request->image != ''){
            $path = public_path() . '/uploads/'; // Fetching the image folder path

            if($post->image != '' && $post->image != null){ // If image is present in the DB
                $old_file = $path . $post->image; // Storing path + image name

                if(file_exists($old_file)){ // If file exists
                    unlink($old_file); // Deleting the existing file
                }
            }

            // Now storing new image
            $img = $request->image;
            $ext = $img->getClientOriginalExtension();
            $image_name = time(). '.' .$ext;
            $img->move(public_path().'/uploads/'.$image_name);
        } else{
            // If image is not uploaded then we are storing the old image name 
            $image_name = $post->image;
        }

        $post = Post::where(['id' => $id])->update([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $image_name,
        ]);

        if($post){
            return response()->json([
                'status' => true,
                'message' => "Post Updated Successfully",
                'post' => $post
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::select('image')->where(['id' => $id])->first();

        // Checking image is uploaded or not
        if($post->image != '' && $post->image != null){ // If image is present in the DB
            $path = public_path() . '/uploads/'; // Fetching the image folder path
            $old_file = $path . $post->image; // Storing path + image name

            if(file_exists($old_file)){ // If file exists
                unlink($old_file); // Deleting the existing file
            }
        }

        $post = Post::where(['id' => $id])->delete();


        return response()->json([
            'status' => true,
            'message' => "Your Post has been removed",
            'post' => $post
        ], 200);
    }
}
