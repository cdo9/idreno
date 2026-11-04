<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index()
    {
        // This method is not used for now
    }

    /**
     * Store a new comment
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'commentaire' => 'nullable|string|max:1000',
            'type' => 'nullable|integer',
        ]);

        // Create a new comment
        if (! is_null($validated['type'])) {
            $typeChangeResponse = ClientController::updateType($request, $validated['client_id']);
            if (is_null($validated['commentaire'])) {
                return $typeChangeResponse;
            }
        }

        if (! is_null($validated['commentaire'])) {
            $comment = new Comment;
            $comment->client_id = $validated['client_id'];
            $comment->user_id = Auth::id();
            $comment->date = Carbon::now();
            $comment->commentaire = $validated['commentaire'];
            $comment->save();

            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'commentaire' => $comment->commentaire,
                    'date' => $comment->date->format('d/m/Y'),
                    'user' => Auth::user() ? Auth::user()->prenom.' '.Auth::user()->nom : 'Inconnu',
                ],
            ]);
        }
    }
}
