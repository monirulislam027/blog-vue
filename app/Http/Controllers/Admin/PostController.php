<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Image;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::with(['user', 'category'])->get();

        return  response()->json(['posts' => $posts], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' =>  'required | min:10 | max: 100 | unique:posts',
            'status' =>  'required | unique:posts',
            'category' =>  'required',
            'contentText' =>  'required | min: 200',
            'thumbnail' =>  'required',
        ]);

        $imageFile = $request->thumbnail;
        $fileExe = explode(';' , $imageFile);
        $fileExe = explode('/', $fileExe[0]);
        $fileExe = end($fileExe);

        $category = Category::where('slug' , $request->category)->get()->first();

        $slug = Str::slug($request->title);
        $fileName = $slug.'.'.$fileExe;


        $add = Post::create([
            'title' =>  $request->title,
            'slug' =>  $slug,
            'status' =>  $request->status,
            'category_id' =>  $category->id,
            'content' =>  $request->contentText,
            'thumbnail' =>  $fileName,
            'user_id' => 1,
        ]);

        if($add){
            Image::make($request->thumbnail)->resize(300, 200)->save(public_path('uploads/post/'.$fileName));
        }

        return  response()->json('success' , 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $post->delete();
        return  response()->json('Post Deleted Successfully!', 200);
    }


    //  custom function for extra features
    public function remove_selected_posts(Request $request)
    {

        $slugs = $request->slugs;

        foreach ($slugs as $slug) {
            $post = Post::where('slug', $slug)->firstOrFail();
            $delete = $post->delete();
            if (!$delete) {
                return response()->json('Error', 204);
            }
        }
        return response()->json("All Selected Post Deleted Successfully!", 200);
    }

    public function published_selected_posts(Request $request)
    {

        $slugs = $request->slugs;

        foreach ($slugs as $slug) {
            $post = Post::where('slug', $slug)->firstOrFail();
            $post->status = 'published';
            $active = $post->save();
            if (!$active) {
                return response()->json('Error', 204);
            }
        }
        return response()->json("All Selected Post Published!", 200);
    }
    public function draft_selected_posts(Request $request)
    {

        $slugs = $request->slugs;

        foreach ($slugs as $slug) {
            $post = Post::where('slug', $slug)->firstOrFail();
            $post->status = 'draft';
            $inactive = $post->save();
            if (!$inactive) {
                return response()->json('Error', 204);
            }
        }
        return response()->json("All Selected Post Draft!", 200);
    }
}
