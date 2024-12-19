<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MailTemplate;
use App\Http\Resources\MailTemplateResource;
use Illuminate\Validation\ValidationException;

class MailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm = $request->input('search', '');

        $query = MailTemplate::query()->where('is_delete', 'No');

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%$searchTerm%")
                    ->orWhere('type', 'like', "%$searchTerm%")
                    ->orWhere('subject', 'like', "%$searchTerm%")
                    ->orWhere('mail_for', 'like', "%$searchTerm%");
            });
        }

        if ($page === 'all') {
            $mailTemplates = $query->get();
            return response()->json([
                'data' => $mailTemplates,
            ]);
        } else {
            $mailTemplates = $query->paginate($perPage);
            return response()->json([
                'meta' => [
                    'current_page' => $mailTemplates->currentPage(),
                    'last_page' => $mailTemplates->lastPage(),
                    'per_page' => $mailTemplates->perPage(),
                    'total' => $mailTemplates->total(),
                ],
                'data' => $mailTemplates->items(),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'subject' => 'required|string|max:255',
                'mail_for' => 'required|string|max:255',
                'body' => 'required|string',
                'status' => 'in:Active,Inactive',
            ]);

            $mailTemplate = MailTemplate::create($request->all());

            return response()->json([
                'message' => ' Mail Template created successfully!',
                'data' => new MailTemplateResource($mailTemplate)
            ], 201);
        } catch (ValidationException $e) {
            // Custom error response
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function show(MailTemplate $mailTemplate)
    {
        return new MailTemplateResource($mailTemplate);
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'subject' => 'required|string|max:255',
                'mail_for' => 'required|string|max:255',
                'body' => 'required|string',
                'status' => 'in:Active,Inactive',
            ]);

            // Find the mail template by ID
            $mailTemplate = MailTemplate::find($id);

            // If the template doesn't exist, return a 404 error
            if (!$mailTemplate) {
                return response()->json([
                    'message' => 'Mail template not found.',
                ], 404);
            }
            
            $mailTemplate->update($request->all());

            return response()->json([
                'message' => 'Mail template updated successfully!',
                'data' => new MailTemplateResource($mailTemplate)
            ], 201);
        } catch (ValidationException $e) {
            // Custom error response
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy($id)
    {
        // Find the mail template by ID
    $mailTemplate = MailTemplate::find($id);

    // If the template doesn't exist, return a 404 error
    if (!$mailTemplate) {
        return response()->json([
            'message' => 'Mail template not found.',
        ], 404);
    }

    if ($mailTemplate->is_allow !== 'Yes') {
        return response()->json([
            'message' => 'Delete operation not allowed for this template.',
        ], 403);
    }

    // Soft delete: Set 'is_delete' to 'Yes'
    $mailTemplate->update(['is_delete' => 'Yes']);
        return response()->json(['message' => 'Mail template deleted successfully.']);
    }
}
