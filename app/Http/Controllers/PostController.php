<?php


namespace App\Http\Controllers;

use App\Models\PostLike;
use App\Models\SavedPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function savePost(Request $request)
    {
        $user = Auth::user();

        $formFields = $request->only('idPost');
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, ['idPost' => ['required', 'numeric']]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => implode(" ", $validator->messages()->all())
            ]);
        }
        $idPost = $formFields['idPost'];

        $postSaved = SavedPost::where(['idPost' => $idPost, 'idUser' => $user->idUser])->first();

        // Check if post was already saved
        if ($postSaved) {
            return $this->unsavePost($request);
        }

        // Create a record for saved post
        $postToSave = new SavedPost();
        $postToSave->idPost = $idPost;
        $postToSave->idUser = $user->idUser;
        $postToSave->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Post saved successfully',
            'saved' => true
        ]);
    }

    public function unsavePost(Request $request)
    {
        $user = Auth::user();

        $formFields = $request->only('idPost');
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, ['idPost' => ['required', 'numeric']]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => implode(" ", $validator->messages()->all())
            ]);
        }
        $idPost = $formFields['idPost'];

        $savedPost = SavedPost::where(['idPost' => $idPost, 'idUser' => $user->idUser])->first();

        // Check if post was saved by the user and delete it the save record
        if ($savedPost) {
            $savedPost->delete();
            return response()->json([
                'status' => 'success',
                'message' => "Post removed from Saved Posts",
                'saved' => false
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => "User didn't save this post"
            ]);
        }
    }

    public function likePost(Request $request)
    {
        $user = Auth::user();

        $formFields = $request->only('idPost');
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, ['idPost' => ['required', 'numeric']]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => implode(" ", $validator->messages()->all())
            ]);
        }
        $idPost = $formFields['idPost'];
        $likeExists = PostLike::where(["idPost" => $idPost, "idUser" => $user->idUser])->get()->first();
        
        // Check if post was already liked by the user
        if ($likeExists) {
               if ($likeExists->delete()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Post unliked succefully',
                    'like' => false
                ]);
            }
        }

        // Create a record for the post like
        $postLike = new PostLike();
        $postLike->idUser = $user->idUser;
        $postLike->idPost = $idPost;
        $postLike->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Post liked successfully',
            'like' => true
        ]);
    }

}
