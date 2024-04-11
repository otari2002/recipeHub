<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{

    public function createComment(Request $request)
    {
        $user = Auth::user();

        $formFields = $request->only(['idRecipe', 'commentText']);
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, [
            'idRecipe' => ['required', 'numeric'],
            'commentText' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }
        $comment = new Comment();
        $comment->idUser = $user->idUser;
        $comment->idRecipe = $formFields['idRecipe'];
        $comment->commentText = $formFields['commentText'];
        $comment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment created successfully'
        ]);
    }

    public function deleteComment(Request $request)
    {
        $user = Auth::user();

        $formFields = $request->only('idComment');
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, ['idComment' => ['required', 'numeric']]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }
        $idComment = $formFields['idComment'];

        // Check if comment exists
        $comment = Comment::where('idComment', $idComment)->where('idUser', $user->idUser)->first();
        if (!$comment) return response()->json([
            'status' => 'error',
            'message' => 'Comment does not exist'
        ]);

        // Check if comment belongs to user
        if ($comment) {
            $comment->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Comment deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not allowed to delete this comment'
            ]);
        }
    }
}
