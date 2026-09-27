<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use App\Models\FacultyRankingScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Uploaded files live on the private disk; they are only streamed to the
 * owner or to HRDO / committee staff (the legacy app served them publicly).
 */
class DocumentController extends Controller
{
    public function application(Request $request, ApplicationDocument $document): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $document->application->user_id === $user->id, 403);

        return Storage::disk('local')->response($document->path, $document->original_name);
    }

    public function evidence(Request $request, FacultyRankingScore $score): StreamedResponse
    {
        $user = $request->user();
        abort_unless($score->evidence_path, 404);
        abort_unless($user->role->isStaff() || $score->ranking->user_id === $user->id, 403);

        return Storage::disk('local')->response($score->evidence_path, $score->evidence_name);
    }
}
