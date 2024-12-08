<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(PostStoreRequest $request)
    {
        try {
           if($request->hasFile('image')){
                $file = $request->file('image');
                $fileName = md5(rand().time()).'.'.$file->extension();
                $pathWithFile = 'storage/'.$file->storePubliclyAs('post', $fileName, 'public');
            }

            Post::query()->create([
                'user_id'   => auth()->user()->id,
                'barta'     => $request->barta,
                'image'     => $pathWithFile ?? null,
                'created_at'=> Carbon::now()
            ]);

            return redirect()->back()->with('success', 'Post created successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return view('post.single', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        if(auth()->user()->id != $post->user_id){
            return redirect()->back()->with('error', 'Invalid user!');
        }
        return view('post.edit', ['post' => $post]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostUpdateRequest $request, Post $post)
    {
        if(auth()->user()->id != $post->user_id){
            return redirect()->back()->with('error', 'Invalid user!');
        }

        if(!$post){
            return redirect()->route('dashboard')->with('success', 'Post not found!');
        }

        if($request->hasFile('image')){
            Storage::disk('public')->delete($post->image);

            $file = $request->file('image');
            $fileName = md5(rand().time()).'.'.$file->extension();
            $pathWithFile = 'storage/'.$file->storePubliclyAs('post', $fileName, 'public');
            
        }else {
            $pathWithFile = $post->image;
        }

        
        $post->barta = $request->barta;
        $post->image = $pathWithFile ?? null;
        $post->save();

        return redirect()->route('dashboard')->with('success', 'Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if(auth()->user()->id != $post->user_id){
            return redirect()->back()->with('error', 'Invalid user!');
        }

        if(!$post){
            return redirect()->route('dashboard')->with('success', 'Post not found!');
        }
        $post->delete();

        return redirect()->route('dashboard')->with('success', 'Post deleted successfully');
    }
}
