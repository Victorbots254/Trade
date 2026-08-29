<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function accept(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->update([
                'accepted_terms_at' => now(),
                'accepted_terms_ip' => $request->ip(),
            ]);
        }

        return response()->json(['message' => 'Terms of service accepted successfully.']);
    }
}
