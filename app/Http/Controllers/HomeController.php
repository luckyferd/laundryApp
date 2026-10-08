<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Send each role to its landing page after login.
     */
    public function __invoke(Request $request)
    {
        return $request->user()->isAdmin()
            ? redirect()->route('dashboard')
            : redirect()->route('orders.index');
    }
}
