<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'website_id' => 'required|exists:websites,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        $post = Post::create($validated);

        return new PostResource($post);
    }
}
