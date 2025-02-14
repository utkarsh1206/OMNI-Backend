<?php

use Illuminate\Support\Facades\DB;

if (! function_exists('generateMailFromTemplate')) {
    function generateMailFromTemplate($templateId, $replacements)
    {
        $template = DB::table('mail_templates')->find($templateId);

        if (!$template) {
            return null; // Or throw an exception if you prefer
        }

        $html = $template->template_content;

        $updatedHtml = preg_replace_callback(
            '/\{\{([^{}]+)\}\}/',
            function ($matches) use ($replacements) {
                $variableName = trim($matches[1]);
                return $replacements[$variableName] ?? $matches[0];
            },
            $html
        );

        return $updatedHtml;
    }
}


// In your controller or service:
// $updatedHtml = generateMailFromTemplate($id, [      // $id is the id of the template that is supposed to be used
//     'agent_name' => $agent_name,
//     'ticket_number' => $support_ticket_number,
//     // ... other replacements
// ]);

// Use $updatedHtml to send your email.