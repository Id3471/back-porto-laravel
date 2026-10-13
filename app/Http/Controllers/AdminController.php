<?php

namespace App\Http\Controllers;

use App\Models\PortfolioContent;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function csrf(Request $request): JsonResponse
    {
        return response()->json(['csrfToken' => $request->session()->token()]);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:40'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('username', Str::lower($credentials['username']))
            ->where('is_admin', true)
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Identifiants incorrects.'],
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['user' => $this->userResponse($user)]);
    }

    public function session(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userResponse($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Session fermée.']);
    }

    public function content(): JsonResponse
    {
        return response()->json([
            'data' => PortfolioContent::query()->whereKey('portfolio')->first()?->data,
        ]);
    }

    public function updateContent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'data' => ['required', 'array'],
            'data.profile' => ['required', 'array'],
            'data.profile.firstName' => ['required', 'string', 'max:100'],
            'data.profile.lastName' => ['required', 'string', 'max:150'],
            'data.profile.fullName' => ['required', 'string', 'max:200'],
            'data.profile.role' => ['required', 'string', 'max:150'],
            'data.profile.roles' => ['required', 'array', 'max:20'],
            'data.profile.roles.*' => ['required', 'string', 'max:150'],
            'data.profile.location' => ['required', 'string', 'max:200'],
            'data.profile.email' => ['required', 'email', 'max:255'],
            'data.profile.phones' => ['required', 'array', 'max:10'],
            'data.profile.phones.*' => ['required', 'string', 'max:50'],
            'data.profile.summary' => ['required', 'string', 'max:5000'],
            'data.profile.socials' => ['present', 'array', 'max:20'],
            'data.profile.socials.*.label' => ['required', 'string', 'max:100'],
            'data.profile.socials.*.href' => ['required', 'string', 'max:500', 'regex:/^(https?:\/\/|mailto:|tel:)/i'],
            'data.profile.socials.*.icon' => ['nullable', 'string', 'max:60'],
            'data.profile.languages' => ['present', 'array', 'max:20'],
            'data.profile.languages.*.name' => ['required', 'string', 'max:100'],
            'data.profile.languages.*.level' => ['required', 'string', 'max:100'],
            'data.profile.languages.*.percent' => ['required', 'integer', 'between:0,100'],
            'data.profile.interests' => ['present', 'array', 'max:30'],
            'data.profile.interests.*.name' => ['required', 'string', 'max:100'],
            'data.profile.interests.*.emoji' => ['nullable', 'string', 'max:20'],
            'data.projects' => ['present', 'array', 'max:100'],
            'data.projects.*.id' => ['required', 'string', 'max:100'],
            'data.projects.*.title' => ['required', 'string', 'max:200'],
            'data.projects.*.description' => ['required', 'string', 'max:5000'],
            'data.projects.*.category' => ['required', 'string', 'max:80'],
            'data.projects.*.icon' => ['nullable', 'string', 'max:60'],
            'data.projects.*.tech' => ['present', 'array', 'max:30'],
            'data.projects.*.tech.*' => ['required', 'string', 'max:100'],
            'data.projects.*.highlights' => ['present', 'array', 'max:20'],
            'data.projects.*.highlights.*' => ['required', 'string', 'max:200'],
            'data.projects.*.links' => ['present', 'array'],
            'data.projects.*.links.demo' => ['nullable', 'url:http,https', 'max:500'],
            'data.projects.*.links.github' => ['nullable', 'url:http,https', 'max:500'],
            'data.projectCategories' => ['present', 'array', 'max:30'],
            'data.projectCategories.*' => ['required', 'string', 'max:80'],
            'data.skillGroups' => ['present', 'array', 'max:30'],
            'data.skillGroups.*.id' => ['required', 'string', 'max:100'],
            'data.skillGroups.*.title' => ['required', 'string', 'max:150'],
            'data.skillGroups.*.icon' => ['nullable', 'string', 'max:60'],
            'data.skillGroups.*.items' => ['present', 'array', 'max:100'],
            'data.skillGroups.*.items.*' => ['required', 'string', 'max:100'],
            'data.softSkills' => ['present', 'array', 'max:100'],
            'data.softSkills.*' => ['required', 'string', 'max:150'],
            'data.experiences' => ['present', 'array', 'max:100'],
            'data.experiences.*.id' => ['required', 'string', 'max:100'],
            'data.experiences.*.company' => ['required', 'string', 'max:200'],
            'data.experiences.*.role' => ['required', 'string', 'max:200'],
            'data.experiences.*.period' => ['required', 'string', 'max:150'],
            'data.experiences.*.location' => ['nullable', 'string', 'max:200'],
            'data.experiences.*.current' => ['required', 'boolean'],
            'data.experiences.*.description' => ['required', 'string', 'max:5000'],
            'data.experiences.*.tags' => ['present', 'array', 'max:30'],
            'data.experiences.*.tags.*' => ['required', 'string', 'max:100'],
            'data.education' => ['present', 'array', 'max:100'],
            'data.education.*.id' => ['required', 'string', 'max:100'],
            'data.education.*.title' => ['required', 'string', 'max:200'],
            'data.education.*.school' => ['required', 'string', 'max:200'],
            'data.education.*.period' => ['required', 'string', 'max:150'],
            'data.education.*.location' => ['nullable', 'string', 'max:200'],
            'data.education.*.description' => ['required', 'string', 'max:5000'],
            'data.education.*.tags' => ['present', 'array', 'max:30'],
            'data.education.*.tags.*' => ['required', 'string', 'max:100'],
            'data.certifications' => ['present', 'array', 'max:100'],
            'data.certifications.*.name' => ['required', 'string', 'max:200'],
            'data.certifications.*.issuer' => ['required', 'string', 'max:200'],
            'data.profile.cvUrl' => ['nullable', 'string', 'max:500', 'regex:/^(https?:\/\/|\/(?!\/))/i'],
        ]);

        $content = PortfolioContent::query()->updateOrCreate(
            ['key' => 'portfolio'],
            ['data' => $validated['data']],
        );

        return response()->json(['data' => $content->data]);
    }

    public function updateAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:40',
                'alpha_dash:ascii',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'current_password' => ['required', 'current_password'],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(12)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $user->username = Str::lower($validated['username']);
        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }
        $user->save();

        $request->session()->regenerate();

        return response()->json(['user' => $this->userResponse($user)]);
    }

    public function replaceCv(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cv' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $file = $validated['cv'];
        $path = $file->storeAs('cv', Str::uuid().'.pdf', 'public');
        if (! $path) {
            return response()->json(['message' => 'Le nouveau CV n’a pas pu être enregistré.'], 500);
        }

        $content = PortfolioContent::query()->firstOrCreate(
            ['key' => 'portfolio'],
            ['data' => []],
        );
        $data = $content->data ?? [];
        $previousUrl = $data['profile']['cvUrl'] ?? null;
        $data['profile']['cvUrl'] = Storage::disk('public')->url($path);
        $content->data = $data;
        $content->save();

        $previousPath = is_string($previousUrl) ? parse_url($previousUrl, PHP_URL_PATH) : null;
        if (is_string($previousPath) && str_starts_with($previousPath, '/storage/cv/')) {
            Storage::disk('public')->delete(substr($previousPath, strlen('/storage/')));
        }

        return response()->json(['cvUrl' => $data['profile']['cvUrl']]);
    }

    private function userResponse(User $user): array
    {
        return [
            'username' => $user->username,
            'name' => $user->name,
        ];
    }
}
