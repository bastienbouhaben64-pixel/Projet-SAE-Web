<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\DocumentStage;
use App\Services\ActivityLogger;
use App\Services\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentStageController extends Controller
{
    public function store(Stage $stage, Request $request)
    {
        $this->authorizeUpload($stage, $request);

        $data = $request->validate([
            'type' => ['required', Rule::in(DocumentStage::TYPES)],
            'titre' => ['required', 'string', 'max:200'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,odt,zip,png,jpg,jpeg'],
        ]);

        $path = $request->file('file')->store("stages/{$stage->id}", 'public');

        $doc = DocumentStage::create([
            'stage_id' => $stage->id,
            'type' => $data['type'],
            'titre' => $data['titre'],
            'file_path' => $path,
            'mime' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        ActivityLogger::log('stage.document.uploaded', ['stage_id' => $stage->id, 'document_id' => $doc->id, 'type' => $data['type']]);

        // notify others
        $recipients = array_filter([$stage->student_id, $stage->company_id, $stage->tutor_id]);
        foreach ($recipients as $uid) {
            if ($uid === $request->user()->id) continue;
            Notify::send($uid, 'document.uploaded',
                "Nouveau document ({$data['type']}) — {$stage->offer->titre}",
                $data['titre'], route('stages.afficher', $stage));
        }

        return back()->with('status', 'Document déposé.');
    }

    public function download(DocumentStage $document, Request $request)
    {
        $this->authorizeView($document->stage, $request);
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);
        ActivityLogger::log('stage.document.downloaded', ['document_id' => $document->id]);
        return Storage::disk('public')->download($document->file_path, $document->titre);
    }

    public function destroy(DocumentStage $document, Request $request)
    {
        $u = $request->user();
        abort_unless($u->isAdmin() || $document->uploaded_by === $u->id, 403);
        Storage::disk('public')->delete($document->file_path);
        $sid = $document->stage_id;
        $document->delete();
        ActivityLogger::log('stage.document.deleted', ['document_id' => $document->id, 'stage_id' => $sid]);
        return back()->with('status', 'Document supprimé.');
    }

    private function authorizeUpload(Stage $stage, Request $request): void
    {
        $u = $request->user();
        $ok = $u->isAdmin() || in_array($u->id, array_filter([$stage->student_id, $stage->tutor_id, $stage->company_id]), true);
        abort_unless($ok, 403);
    }

    private function authorizeView(Stage $stage, Request $request): void
    {
        $u = $request->user();
        $ok = $u->isAdmin() || $u->isJury()
            || in_array($u->id, array_filter([$stage->student_id, $stage->company_id, $stage->tutor_id]), true);
        abort_unless($ok, 403);
    }
}
