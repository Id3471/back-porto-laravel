<?php

namespace App\Http\Controllers;

use App\Mail\PortfolioContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'not_regex:/[\r\n]/'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'subject' => ['nullable', 'string', 'max:160', 'not_regex:/[\r\n]/'],
            'message' => ['required', 'string', 'min:10', 'max:8000'],
            'website' => ['prohibited'],
        ]);

        $recipient = config('mail.contact_to');
        if (! is_string($recipient) || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'La réception des messages n’est pas encore configurée. Veuillez réessayer plus tard.',
            ], 503);
        }

        $subject = trim($validated['subject'] ?? '');
        if ($subject === '') {
            $subject = 'Message portfolio — '.$validated['name'];
        }

        Mail::send(new PortfolioContactMessage(
            recipientEmail: $recipient,
            senderName: trim($validated['name']),
            senderEmail: $validated['email'],
            subjectLine: $subject,
            messageBody: trim($validated['message']),
        ));

        return response()->json([
            'message' => 'Votre message a bien été envoyé.',
        ]);
    }
}
